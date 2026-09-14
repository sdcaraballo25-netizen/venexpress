<?php

namespace Tests\Feature\Admin;

use App\Models\Driver;
use App\Models\Package;
use App\Models\Route;
use App\Models\User;
use App\Services\DeliveryAssignmentService;
use App\Services\PackageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Regresión: al asignar un paquete LISTO_RETIRO a una ruta de reparto,
 * el paquete se quedaba estancado en LISTO_RETIRO (solo se guardaba el
 * driver_id), aunque el mapa de transiciones de PackageService ya
 * contemplaba LISTO_RETIRO -> EN_TRANSITO_NACIONAL. Como
 * PackageService::completeDelivery() exige EN_TRANSITO_NACIONAL, el
 * repartidor nunca podía completar la entrega desde la app después de
 * que un admin le asignara el paquete por esta vía.
 */
class DeliveryAssignmentServiceTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestPackages;

    public function test_assigning_a_package_moves_it_back_to_en_transito_nacional(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN_PRINCIPAL,
            'status' => User::STATUS_ACTIVE,
        ]);

        $driverUser = User::factory()->create([
            'role' => User::ROLE_REPARTIDOR,
            'status' => User::STATUS_ACTIVE,
        ]);

        $driver = Driver::factory()->create([
            'user_id' => $driverUser->id,
            'status' => Driver::STATUS_ACTIVE,
            'driver_type' => Driver::TYPE_DELIVERY,
        ]);

        $route = Route::create([
            'city' => 'Valencia',
            'state' => 'Carabobo',
            'name' => 'Ruta de reparto de prueba',
            'driver_id' => $driver->id,
            'created_by' => $admin->id,
            'status' => Route::STATUS_IN_PROGRESS,
            'route_type' => Route::TYPE_DELIVERY,
        ]);

        $ally = $this->createAlly();

        $package = $this->createPackage($ally, [
            'requires_delivery' => true,
            'destination_city' => 'Valencia',
            'current_status' => Package::STATUS_LISTO_RETIRO,
            'delivery_status' => Package::DELIVERY_ACCEPTED,
        ]);

        $assigned = app(DeliveryAssignmentService::class)->assign($package, $route, $admin->id);

        $this->assertSame(Package::STATUS_EN_TRANSITO_NACIONAL, $assigned->current_status);
        $this->assertSame($driver->id, $assigned->driver_id);

        // Sin el fix, esto lanzaba "El paquete no está en estado de reparto."
        $delivered = app(PackageService::class)->completeDelivery(
            package: $assigned,
            driver: $driver,
            receiverName: 'María Gómez',
            receiverIdDoc: 'V-87654321',
            deliveryConfirmationMethod: 'cedula',
        );

        $this->assertSame(Package::STATUS_ENTREGADO, $delivered->current_status);
    }

    public function test_assign_rejects_a_package_that_is_not_listo_retiro(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN_PRINCIPAL,
            'status' => User::STATUS_ACTIVE,
        ]);

        $driverUser = User::factory()->create([
            'role' => User::ROLE_REPARTIDOR,
            'status' => User::STATUS_ACTIVE,
        ]);

        $driver = Driver::factory()->create([
            'user_id' => $driverUser->id,
            'status' => Driver::STATUS_ACTIVE,
            'driver_type' => Driver::TYPE_DELIVERY,
        ]);

        $route = Route::create([
            'city' => 'Valencia',
            'state' => 'Carabobo',
            'name' => 'Ruta de reparto de prueba',
            'driver_id' => $driver->id,
            'created_by' => $admin->id,
            'status' => Route::STATUS_IN_PROGRESS,
            'route_type' => Route::TYPE_DELIVERY,
        ]);

        $ally = $this->createAlly();

        $package = $this->createPackage($ally, [
            'requires_delivery' => true,
            'destination_city' => 'Valencia',
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
            'delivery_status' => Package::DELIVERY_ACCEPTED,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('El paquete debe estar LISTO_RETIRO.');

        app(DeliveryAssignmentService::class)->assign($package, $route, $admin->id);
    }
}
