<?php

namespace Tests\Feature\Admin;

use App\Models\Customer;
use App\Models\Driver;
use App\Models\Package;
use App\Models\PackageHistory;
use App\Models\Route;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseCoverage;
use App\Notifications\DeliveryPinIssued;
use App\Services\DeliveryAssignmentService;
use App\Services\PackageService;
use App\Services\RouteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use RuntimeException;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Asignación de un paquete a domicilio a la ruta de reparto en curso de
 * un repartidor (Admin, Almacén y la toma por escaneo usan este mismo
 * servicio): solo desde PENDIENTE_ENTREGA, en el almacén de la zona de
 * la ruta y con la ciudad de la ruta; el paquete sale a reparto
 * (EN_RUTA) y el destinatario recibe su PIN de entrega.
 */
class DeliveryAssignmentServiceTest extends TestCase
{
    use CreatesTestPackages;
    use RefreshDatabase;

    private User $admin;

    private Driver $driver;

    private Warehouse $hub;

    private Route $route;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN_PRINCIPAL,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->driver = Driver::factory()->create([
            'status' => Driver::STATUS_ACTIVE,
            'driver_type' => Driver::TYPE_DELIVERY,
        ]);

        $this->hub = Warehouse::factory()->create(['state' => 'Carabobo', 'city' => 'Valencia']);

        WarehouseCoverage::create([
            'warehouse_id' => $this->hub->id,
            'state' => 'Carabobo',
            'city' => 'Valencia',
            'is_active' => true,
        ]);

        $this->route = Route::create([
            'city' => 'Valencia',
            'state' => 'Carabobo',
            'name' => 'Ruta de reparto de prueba',
            'driver_id' => $this->driver->id,
            'created_by' => $this->admin->id,
            'status' => Route::STATUS_IN_PROGRESS,
            'route_type' => Route::TYPE_DELIVERY,
        ]);
    }

    private function pendingPackage(array $overrides = []): Package
    {
        return $this->createPackage($this->createAlly(), array_merge([
            'requires_delivery' => true,
            'destination_city' => 'Valencia',
            'destination_state' => 'Carabobo',
            'current_status' => Package::STATUS_PENDIENTE_ENTREGA,
            'current_warehouse_id' => $this->hub->id,
            'destination_warehouse_id' => $this->hub->id,
        ], $overrides));
    }

    private function assign(Package $package): Package
    {
        return app(DeliveryAssignmentService::class)->assign($package, $this->route, $this->admin->id);
    }

    public function test_assigning_sends_the_package_out_for_delivery(): void
    {
        $package = $this->pendingPackage();

        $this->assertSame(Package::DELIVERY_PENDING, $package->fresh()->delivery_status);

        $assigned = $this->assign($package);

        $this->assertSame(Package::STATUS_EN_RUTA, $assigned->current_status);
        $this->assertSame($this->driver->id, $assigned->driver_id);
        $this->assertSame(Package::DELIVERY_ACCEPTED, $assigned->delivery_status);

        $history = PackageHistory::where('package_id', $package->id)->latest('id')->first();
        $this->assertSame(Package::STATUS_EN_RUTA, $history->status);
        $this->assertSame(PackageHistory::EVENT_REPARTO, $history->event_type);
    }

    public function test_the_recipient_gets_a_delivery_pin_that_is_stored_only_hashed(): void
    {
        Notification::fake();

        Customer::create([
            'id_doc' => 'V-87654321',
            'name' => 'María Gómez',
            'phone' => '0424-7654321',
            'email' => 'maria@example.com',
        ]);

        $assigned = $this->assign($this->pendingPackage());

        $pin = null;

        Notification::assertSentOnDemand(
            DeliveryPinIssued::class,
            function (DeliveryPinIssued $notification, $channels, $notifiable) use (&$pin) {
                $mail = $notification->toMail($notifiable);
                $pin = collect($mail->introLines)
                    ->map(fn ($line) => preg_match('/(\d{6})/', (string) $line, $m) ? $m[1] : null)
                    ->filter()
                    ->first();

                return $notifiable->routes['mail'] === 'maria@example.com';
            }
        );

        $this->assertNotNull($pin);
        $this->assertTrue($assigned->acceptsDeliveryPin());
        $this->assertNotSame($pin, $assigned->delivery_pin_hash);
        $this->assertArrayNotHasKey('delivery_pin_hash', $assigned->toArray());

        // Con ese PIN, el repartidor confirma la entrega sin cédula ni foto.
        $delivered = app(PackageService::class)->completeDelivery(
            package: $assigned,
            driver: $this->driver,
            receiverName: 'María Gómez',
            deliveryPin: $pin,
        );

        $this->assertSame(Package::STATUS_ENTREGADO, $delivered->current_status);
        $this->assertSame(Package::DELIVERY_CONFIRMATION_PIN, $delivered->delivery_confirmation_method);
        $this->assertNull($delivered->delivery_pin_hash);
    }

    public function test_without_a_recipient_email_no_pin_is_generated(): void
    {
        Notification::fake();

        $assigned = $this->assign($this->pendingPackage());

        $this->assertNull($assigned->delivery_pin_hash);
        $this->assertFalse($assigned->acceptsDeliveryPin());
        Notification::assertNothingSent();
    }

    public function test_assign_rejects_a_package_that_is_not_pending_delivery(): void
    {
        foreach ([Package::STATUS_EN_TRANSITO_NACIONAL, Package::STATUS_LISTO_RETIRO, Package::STATUS_EN_HUB] as $status) {
            $package = $this->pendingPackage(['current_status' => $status]);

            try {
                $this->assign($package);
                $this->fail("Se asignó un paquete en {$status}.");
            } catch (RuntimeException $e) {
                $this->assertStringContainsString('pendiente de entrega', $e->getMessage());
            }

            $this->assertSame($status, $package->fresh()->current_status);
            $this->assertNull($package->fresh()->driver_id);
        }
    }

    public function test_assign_rejects_a_package_that_is_not_at_the_route_warehouse(): void
    {
        $otherHub = Warehouse::factory()->create(['state' => 'Carabobo', 'city' => 'Valencia']);

        $package = $this->pendingPackage(['current_warehouse_id' => $otherHub->id]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Este paquete no está en el almacén de la zona de la ruta.');

        $this->assign($package);
    }

    /**
     * Un almacén que cubre varias ciudades: el paquete está en el
     * almacén correcto, pero va a otra ciudad que la de la ruta.
     */
    public function test_assign_rejects_a_package_for_another_city_of_the_same_warehouse(): void
    {
        WarehouseCoverage::create([
            'warehouse_id' => $this->hub->id,
            'state' => 'Carabobo',
            'city' => 'Puerto Cabello',
            'is_active' => true,
        ]);

        $package = $this->pendingPackage(['destination_city' => 'Puerto Cabello']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('La ciudad de la ruta no coincide con la ciudad destino del paquete.');

        $this->assign($package);
    }

    public function test_unassigning_puts_the_package_back_as_pending_and_voids_the_pin(): void
    {
        Customer::create([
            'id_doc' => 'V-87654321',
            'name' => 'María Gómez',
            'phone' => '0424-7654321',
            'email' => 'maria@example.com',
        ]);

        $assigned = $this->assign($this->pendingPackage());
        $this->assertTrue($assigned->acceptsDeliveryPin());

        $unassigned = app(DeliveryAssignmentService::class)->unassign($assigned, $this->admin->id);

        $this->assertNull($unassigned->driver_id);
        $this->assertSame(Package::STATUS_PENDIENTE_ENTREGA, $unassigned->current_status);
        $this->assertSame(Package::DELIVERY_PENDING, $unassigned->delivery_status);
        $this->assertNull($unassigned->delivery_pin_hash);
        $this->assertTrue($unassigned->isAvailableForDeliveryClaim());
    }

    /**
     * assign() no pasa por ninguna RouteStop: aun así, cancelar la ruta
     * libera el paquete y lo deja de nuevo pendiente de entrega.
     */
    public function test_cancelling_a_route_releases_a_package_assigned_via_delivery_assignment_service(): void
    {
        $assigned = $this->assign($this->pendingPackage());
        $this->assertSame($this->driver->id, $assigned->driver_id);

        app(RouteService::class)->cancel($this->route, $this->admin->id);

        $package = $assigned->fresh();
        $this->assertNull($package->driver_id);
        $this->assertSame(Package::STATUS_PENDIENTE_ENTREGA, $package->current_status);
        $this->assertTrue($package->isAvailableForDeliveryClaim());
    }

    public function test_a_route_with_assigned_packages_cannot_be_completed_until_they_are_delivered(): void
    {
        $this->assign($this->pendingPackage());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('tienes 1 paquete pendiente de entregar');

        app(RouteService::class)->complete($this->route, $this->admin->id);
    }
}
