<?php

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\Package;
use App\Models\User;
use App\Services\PackageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * isClaimedForDelivery() solo es true cuando delivery_status ===
 * DELIVERY_ACCEPTED, pero un paquete puede tener driver_id asignado
 * por otras vías (registerCollection(), DeliveryAssignmentService,
 * o RouteService::releasePendingCustodyFor() liberando una ruta
 * cancelada) sin tocar delivery_status. Antes, claimForDelivery()
 * solo miraba isClaimedForDelivery() y podía dejar que un segundo
 * repartidor "robara" un paquete que otro ya tenía físicamente.
 */
class PackageServiceDeliveryClaimTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestPackages;

    protected PackageService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(PackageService::class);
    }

    private function createActiveDeliveryDriver(): Driver
    {
        $user = User::factory()->create([
            'role' => User::ROLE_REPARTIDOR,
            'status' => User::STATUS_ACTIVE,
        ]);

        return Driver::factory()->create([
            'user_id' => $user->id,
            'status' => Driver::STATUS_ACTIVE,
            'driver_type' => Driver::TYPE_DELIVERY,
        ]);
    }

    public function test_a_second_driver_cannot_claim_a_package_already_assigned_to_another_driver_even_without_delivery_status_accepted(): void
    {
        $ally = $this->createAlly();
        $driverA = $this->createActiveDeliveryDriver();
        $driverB = $this->createActiveDeliveryDriver();

        // Simula un paquete asignado a driverA por una vía que no pasa
        // por claimForDelivery() (ej. registerCollection() o una ruta
        // que se liberó): driver_id ya está fijado, pero
        // delivery_status se queda en su default ("pendiente"), así
        // que isClaimedForDelivery() por sí solo diría "no reclamado".
        $package = $this->createPackage($ally, [
            'requires_delivery' => true,
            'driver_id' => $driverA->id,
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
        ]);

        $this->assertFalse($package->isClaimedForDelivery());
        $this->assertFalse($package->fresh()->isAvailableForDeliveryClaim());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Este pedido ya fue reclamado por otro repartidor.');

        $this->service->claimForDelivery($package, $driverB, $driverB->user_id);
    }

    public function test_the_same_driver_can_reclaim_their_own_package_idempotently(): void
    {
        $ally = $this->createAlly();
        $driver = $this->createActiveDeliveryDriver();

        $package = $this->createPackage($ally, [
            'requires_delivery' => true,
            'driver_id' => $driver->id,
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
        ]);

        $result = $this->service->claimForDelivery($package, $driver, $driver->user_id);

        $this->assertSame($driver->id, $result->driver_id);
    }

    public function test_package_with_a_driver_already_assigned_does_not_show_as_available_for_claim(): void
    {
        $ally = $this->createAlly();
        $driver = $this->createActiveDeliveryDriver();

        $this->createPackage($ally, [
            'requires_delivery' => true,
            'driver_id' => $driver->id,
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
        ]);

        $this->assertSame(
            0,
            Package::query()->availableForDeliveryClaim()->count()
        );
    }
}
