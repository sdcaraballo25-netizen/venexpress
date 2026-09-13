<?php

namespace Tests\Feature\Driver;

use App\Livewire\Driver\Scanner;
use App\Models\Ally;
use App\Models\Driver;
use App\Models\Package;
use App\Models\PackageHistory;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\User;
use App\Services\HubReceptionService;
use App\Services\LogisticsScanService;
use App\Services\RouteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use RuntimeException;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Cubre la recepción en HUB (segunda mitad de "Aliado -> HUB", tras
 * scanCollection()): LogisticsScanService::scanHubReception().
 *
 * Reutiliza HubReceptionService::receive() tal cual para la
 * transición RECOLECTADO_VENEXPRESS -> EN_HUB (EVENT_RECEPCION); lo
 * nuevo aquí es la validación de que el paquete pertenece a la ruta
 * hub_transfer activa del driver, NUNCA decidida solo por
 * Package.driver_id.
 *
 * No prueba de nuevo el flujo de Admin\PackageReception (no se
 * modificó HubReceptionService), salvo un smoke test para confirmar
 * que sigue funcionando sin ninguna ruta involucrada.
 */
class DriverHubReceptionTest extends TestCase
{
    use CreatesTestPackages;
    use RefreshDatabase;

    private function createDriverUser(string $driverType = Driver::TYPE_HUB): array
    {
        $user = User::factory()->create([
            'role' => User::ROLE_REPARTIDOR,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $driver = Driver::factory()->create([
            'user_id' => $user->id,
            'status' => Driver::STATUS_ACTIVE,
            'driver_type' => $driverType,
        ]);

        return [$user, $driver];
    }

    private function createInProgressHubTransferRoute(Driver $driver, Ally $ally): array
    {
        $creator = User::factory()->create();

        $route = Route::create([
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
            'name' => 'Recolección Caracas',
            'driver_id' => $driver->id,
            'created_by' => $creator->id,
            'status' => Route::STATUS_IN_PROGRESS,
            'started_at' => now(),
            'route_type' => Route::TYPE_HUB_TRANSFER,
        ]);

        $stop = RouteStop::create([
            'route_id' => $route->id,
            'ally_id' => $ally->id,
            'sequence' => 1,
            'status' => RouteStop::STATUS_PENDING,
        ]);

        return [$route, $stop];
    }

    public function test_driver_can_receive_a_package_collected_on_their_active_route(): void
    {
        [$user, $driver] = $this->createDriverUser();
        $ally = $this->createAlly();
        [$route, $stop] = $this->createInProgressHubTransferRoute($driver, $ally);

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_RECIBIDO_AGENCIA,
        ]);

        $scanService = app(LogisticsScanService::class);
        $scanService->scanCollection($package, $driver, (int) $user->id);

        $received = $scanService->scanHubReception($package->fresh(), $driver, (int) $user->id);

        $this->assertSame(Package::STATUS_EN_HUB, $received->current_status);
        $this->assertNull($received->driver_id);

        $this->assertNotNull(
            $package->fresh()->histories()
                ->where('event_type', PackageHistory::EVENT_RECEPCION)
                ->first()
        );

        $this->assertSame(RouteStop::STATUS_VISITED, $stop->fresh()->status);
    }

    public function test_hub_reception_fails_for_a_package_not_collected_on_the_active_route(): void
    {
        [$user, $driver] = $this->createDriverUser();
        $ally = $this->createAlly();
        $this->createInProgressHubTransferRoute($driver, $ally);

        // Ya está RECOLECTADO_VENEXPRESS y con driver_id de ESTE
        // driver, pero SIN ningún PackageHistory de EVENT_SALIDA
        // ligado a las paradas de su ruta activa — no debe bastar con
        // que driver_id coincida.
        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_RECOLECTADO_VENEXPRESS,
            'driver_id' => $driver->id,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Este paquete no fue recolectado en tu ruta activa.');

        app(LogisticsScanService::class)->scanHubReception($package, $driver, (int) $user->id);
    }

    public function test_hub_reception_fails_without_an_active_hub_transfer_route(): void
    {
        [$user, $driver] = $this->createDriverUser();
        $ally = $this->createAlly();

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_RECOLECTADO_VENEXPRESS,
            'driver_id' => $driver->id,
        ]);

        $this->expectException(RuntimeException::class);

        app(LogisticsScanService::class)->scanHubReception($package, $driver, (int) $user->id);
    }

    public function test_hub_reception_fails_when_package_is_not_recolectado(): void
    {
        [$user, $driver] = $this->createDriverUser();
        $ally = $this->createAlly();
        $this->createInProgressHubTransferRoute($driver, $ally);

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_RECIBIDO_AGENCIA,
        ]);

        $this->expectException(RuntimeException::class);

        app(LogisticsScanService::class)->scanHubReception($package, $driver, (int) $user->id);
    }

    public function test_hub_reception_fails_for_delivery_type_driver(): void
    {
        [$user, $driver] = $this->createDriverUser(Driver::TYPE_DELIVERY);
        $ally = $this->createAlly();

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_RECOLECTADO_VENEXPRESS,
            'driver_id' => $driver->id,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Solo un repartidor de HUB puede registrar recepciones en HUB.');

        app(LogisticsScanService::class)->scanHubReception($package, $driver, (int) $user->id);
    }

    public function test_admin_hub_reception_flow_still_works_without_any_route(): void
    {
        $admin = User::factory()->create();
        $ally = $this->createAlly();

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_RECOLECTADO_VENEXPRESS,
        ]);

        $received = app(HubReceptionService::class)->receive(
            package: $package,
            userId: (int) $admin->id,
            hubLocation: 'HUB Caracas',
        );

        $this->assertSame(Package::STATUS_EN_HUB, $received->current_status);
    }

    public function test_scanner_receives_package_at_hub_after_collecting_it_on_the_same_route(): void
    {
        [$user, $driver] = $this->createDriverUser();
        $ally = $this->createAlly();
        $this->createInProgressHubTransferRoute($driver, $ally);

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_RECIBIDO_AGENCIA,
        ]);

        $component = Livewire::actingAs($user)->test(Scanner::class);

        $component
            ->set('trackingNumber', $package->tracking_number)
            ->call('searchPackage')
            ->assertSet('errorMessage', null);

        $this->assertSame(Package::STATUS_RECOLECTADO_VENEXPRESS, $package->fresh()->current_status);

        $component
            ->set('trackingNumber', $package->tracking_number)
            ->call('searchPackage')
            ->assertSet('errorMessage', null)
            ->assertSet(
                'successMessage',
                'Recepción en HUB registrada correctamente. El paquete quedó EN_HUB.'
            );

        $this->assertSame(Package::STATUS_EN_HUB, $package->fresh()->current_status);
    }

    public function test_scanner_rejects_hub_reception_for_a_package_collected_on_another_route(): void
    {
        [$user, $driver] = $this->createDriverUser();
        [$otherUser, $otherDriver] = $this->createDriverUser();

        $ally = $this->createAlly();
        $this->createInProgressHubTransferRoute($driver, $ally);
        [$otherRoute, $otherStop] = $this->createInProgressHubTransferRoute($otherDriver, $ally);

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_RECIBIDO_AGENCIA,
        ]);

        // Lo recolecta el OTRO driver, en SU propia ruta.
        app(RouteService::class)->registerCollection(
            $otherRoute,
            $otherStop,
            [$package->id],
            (int) $otherUser->id
        );

        $this->assertSame(Package::STATUS_RECOLECTADO_VENEXPRESS, $package->fresh()->current_status);

        Livewire::actingAs($user)
            ->test(Scanner::class)
            ->set('trackingNumber', $package->fresh()->tracking_number)
            ->call('searchPackage')
            ->assertSet('errorMessage', 'Este paquete no fue recolectado en tu ruta activa.');

        $this->assertSame(Package::STATUS_RECOLECTADO_VENEXPRESS, $package->fresh()->current_status);
    }
}
