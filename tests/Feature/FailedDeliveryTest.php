<?php

namespace Tests\Feature;

use App\Livewire\Admin\PackageReturns as AdminPackageReturns;
use App\Livewire\Driver\PackageDetail;
use App\Models\AuditLog;
use App\Models\Driver;
use App\Models\Package;
use App\Models\PackageHistory;
use App\Models\Route;
use App\Models\User;
use App\Services\PackageService;
use App\Services\RouteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use RuntimeException;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Entregas a domicilio fallidas: el repartidor registra el motivo
 * (EN_RUTA -> ENTREGA_FALLIDA) y se cuenta el intento; de vuelta en el
 * almacén, Almacén o Admin deciden un nuevo intento o la devolución.
 */
class FailedDeliveryTest extends TestCase
{
    use CreatesTestPackages;
    use RefreshDatabase;

    private function driverWithUser(): array
    {
        $user = User::factory()->create([
            'role' => User::ROLE_REPARTIDOR,
            'status' => User::STATUS_ACTIVE,
            'password' => bcrypt('password-seguro'),
        ]);

        $driver = Driver::factory()->create([
            'user_id' => $user->id,
            'status' => Driver::STATUS_ACTIVE,
            'driver_type' => Driver::TYPE_DELIVERY,
        ]);

        return [$user, $driver];
    }

    private function outForDelivery(Driver $driver, array $overrides = []): Package
    {
        return $this->createPackage($this->createAlly(), array_merge([
            'requires_delivery' => true,
            'driver_id' => $driver->id,
            'current_status' => Package::STATUS_EN_RUTA,
            'delivery_pin_hash' => bcrypt('123456'),
        ], $overrides));
    }

    public function test_a_failed_delivery_records_the_reason_and_counts_the_attempt(): void
    {
        [, $driver] = $this->driverWithUser();
        $package = $this->outForDelivery($driver);

        $failed = app(PackageService::class)->markDeliveryFailed($package, $driver, 'CLIENTE_AUSENTE', 'Nadie abrió');

        $this->assertSame(Package::STATUS_ENTREGA_FALLIDA, $failed->current_status);
        $this->assertSame(1, $failed->delivery_attempts);
        $this->assertSame('CLIENTE_AUSENTE', $failed->failed_delivery_reason);
        $this->assertSame('Nadie abrió', $failed->failed_delivery_notes);
        $this->assertNotNull($failed->failed_delivery_at);
        // Sigue con el repartidor hasta que lo devuelva al almacén, y el PIN ya no sirve.
        $this->assertSame($driver->id, $failed->driver_id);
        $this->assertNull($failed->delivery_pin_hash);

        $history = PackageHistory::where('package_id', $package->id)->latest('id')->first();
        $this->assertSame(PackageHistory::EVENT_ENTREGA_FALLIDA, $history->event_type);
        $this->assertStringContainsString('Intento de entrega #1 fallido: Destinatario ausente', $history->location_description);
    }

    public function test_attempts_add_up_across_retries(): void
    {
        [, $driver] = $this->driverWithUser();
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN_PRINCIPAL, 'status' => User::STATUS_ACTIVE]);
        $service = app(PackageService::class);

        $package = $service->markDeliveryFailed($this->outForDelivery($driver), $driver, 'DIRECCION_INCORRECTA');
        $package = $service->scheduleDeliveryRetry($package, $admin->id);

        $this->assertSame(Package::STATUS_PENDIENTE_ENTREGA, $package->current_status);
        $this->assertNull($package->driver_id);

        $package->forceFill(['current_status' => Package::STATUS_EN_RUTA, 'driver_id' => $driver->id])->save();
        $package = $service->markDeliveryFailed($package, $driver, 'CLIENTE_AUSENTE');

        $this->assertSame(2, $package->delivery_attempts);
    }

    public function test_other_requires_a_description_and_only_en_ruta_can_fail(): void
    {
        [, $driver] = $this->driverWithUser();
        $service = app(PackageService::class);

        try {
            $service->markDeliveryFailed($this->outForDelivery($driver), $driver, 'OTRO');
            $this->fail('Se aceptó "Otro" sin descripción.');
        } catch (RuntimeException $e) {
            $this->assertSame('Describe brevemente por qué no se pudo entregar.', $e->getMessage());
        }

        $pending = $this->outForDelivery($driver, ['current_status' => Package::STATUS_PENDIENTE_ENTREGA]);

        $this->expectExceptionMessage('Solo se puede marcar como fallida una entrega en ruta.');

        $service->markDeliveryFailed($pending, $driver, 'CLIENTE_AUSENTE');
    }

    public function test_the_route_cannot_be_completed_until_the_failed_package_is_back_at_the_warehouse(): void
    {
        [$user, $driver] = $this->driverWithUser();

        $route = Route::create([
            'name' => 'Reparto Valencia',
            'city' => 'Valencia',
            'state' => 'Carabobo',
            'route_type' => Route::TYPE_DELIVERY,
            'status' => Route::STATUS_IN_PROGRESS,
            'driver_id' => $driver->id,
            'created_by' => $user->id,
            'started_at' => now(),
        ]);

        $package = $this->outForDelivery($driver);

        AuditLog::create([
            'actor_user_id' => $user->id,
            'action' => 'package.delivery_assigned',
            'target_type' => Package::class,
            'target_id' => $package->id,
            'description' => 'Asignado a la ruta.',
            'metadata' => ['route_id' => $route->id, 'driver_id' => $driver->id],
        ]);

        app(PackageService::class)->markDeliveryFailed($package, $driver, 'CLIENTE_AUSENTE');

        $this->expectExceptionMessage('o de devolver al almacén si la entrega falló');

        app(RouteService::class)->complete($route, $user->id);
    }

    public function test_the_driver_marks_it_failed_from_the_web_panel(): void
    {
        [$user, $driver] = $this->driverWithUser();
        $package = $this->outForDelivery($driver);

        Livewire::actingAs($user)
            ->test(PackageDetail::class, ['packageId' => $package->id])
            ->assertSee('No se pudo entregar')
            ->set('showFailedForm', true)
            ->call('markDeliveryFailed')
            ->assertHasErrors(['failedReason' => 'required'])
            ->set('failedReason', 'ZONA_INACCESIBLE')
            ->call('markDeliveryFailed')
            ->assertHasNoErrors()
            ->assertSee('Entrega fallida (intento #1)')
            ->assertSee('Devuelve el paquete al almacén');

        $this->assertSame(Package::STATUS_ENTREGA_FALLIDA, $package->fresh()->current_status);
    }

    public function test_the_driver_marks_it_failed_from_the_app(): void
    {
        [$user, $driver] = $this->driverWithUser();
        $package = $this->outForDelivery($driver);

        $token = $this->postJson('/api/driver/login', [
            'email' => $user->email,
            'password' => 'password-seguro',
            'device_name' => 'telefono-de-prueba',
        ])->json('token');

        $headers = ['Authorization' => "Bearer {$token}"];

        $this->postJson("/api/driver/packages/{$package->id}/failed-delivery", ['reason' => 'INVENTADO'], $headers)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['reason']);

        $this->postJson("/api/driver/packages/{$package->id}/failed-delivery", [
            'reason' => 'RECHAZADO_POR_CLIENTE',
            'notes' => 'Dijo que no lo pidió',
        ], $headers)
            ->assertOk()
            ->assertJsonPath('package.current_status', Package::STATUS_ENTREGA_FALLIDA)
            ->assertJsonPath('package.delivery_attempts', 1)
            ->assertJsonPath('package.failed_delivery_reason_label', 'El destinatario lo rechazó');
    }

    public function test_admin_can_schedule_a_new_attempt(): void
    {
        [, $driver] = $this->driverWithUser();
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN_OPERATIVO, 'status' => User::STATUS_ACTIVE]);

        $package = app(PackageService::class)->markDeliveryFailed($this->outForDelivery($driver), $driver, 'CLIENTE_AUSENTE');

        $this->actingAs($admin)
            ->get(route('admin.package-returns'))
            ->assertOk()
            ->assertSee('Entregas fallidas')
            ->assertSee($package->tracking_number);

        Livewire::actingAs($admin)
            ->test(AdminPackageReturns::class, ['trackingNumber' => $package->tracking_number])
            ->assertSee('Programar nuevo intento')
            ->call('retryDelivery')
            ->assertSet('errorMessage', null);

        $package->refresh();
        $this->assertSame(Package::STATUS_PENDIENTE_ENTREGA, $package->current_status);
        $this->assertNull($package->driver_id);
        $this->assertTrue(AuditLog::where('action', 'package.delivery_retry_scheduled')->where('target_id', $package->id)->exists());
    }

    public function test_admin_can_return_a_failed_delivery_to_the_sender(): void
    {
        [, $driver] = $this->driverWithUser();
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN_PRINCIPAL, 'status' => User::STATUS_ACTIVE]);

        $package = app(PackageService::class)->markDeliveryFailed($this->outForDelivery($driver), $driver, 'CLIENTE_AUSENTE');

        Livewire::actingAs($admin)
            ->test(AdminPackageReturns::class, ['trackingNumber' => $package->tracking_number])
            ->set('returnReason', 'Dos intentos fallidos')
            ->call('startReturn')
            ->assertSet('errorMessage', null);

        $package->refresh();
        $this->assertSame(Package::STATUS_EN_DEVOLUCION, $package->current_status);
        $this->assertNull($package->driver_id);
    }

    public function test_public_tracking_explains_a_failed_delivery(): void
    {
        [, $driver] = $this->driverWithUser();
        $package = app(PackageService::class)->markDeliveryFailed($this->outForDelivery($driver), $driver, 'CLIENTE_AUSENTE');

        $this->get(route('tracking.show', ['guia' => $package->tracking_number]))
            ->assertOk()
            ->assertSee('Entrega fallida')
            ->assertSee('No pudimos entregar tu envío');
    }
}
