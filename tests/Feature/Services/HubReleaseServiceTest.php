<?php

namespace Tests\Feature\Services;

use App\Livewire\Admin\PackageReception as AdminPackageReception;
use App\Livewire\Ally\PackageReception as AllyPackageReception;
use App\Livewire\Client\Dashboard as ClientDashboard;
use App\Models\Ally;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Package;
use App\Models\PackageHistory;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseCoverage;
use App\Services\HubReceptionService;
use App\Services\HubReleaseService;
use App\Services\LogisticsResolutionResult;
use App\Services\PackageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use RuntimeException;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Fase 5B-2 — HubReleaseService::release().
 *
 * Qué ocurre con un paquete EN_HUB que ya llegó a su HUB destino
 * (current_warehouse_id === destination_warehouse_id, confirmado en
 * vivo vía LogisticsResolutionService::isAtDestinationWarehouse()):
 *
 * - Retiro en HUB: EN_HUB -> LISTO_RETIRO directo.
 * - Delivery: EN_HUB -> LISTO_RETIRO directo (mismo destino final que
 *   retiro en HUB — Package::CLAIMABLE_FOR_DELIVERY_STATUSES ya acepta
 *   LISTO_RETIRO como reclamable, y claimForDelivery() ya sabe llevarlo
 *   a EN_TRANSITO_NACIONAL cuando un repartidor lo reclama de verdad).
 * - Retiro en Aliado: EN_HUB -> EN_TRANSITO_NACIONAL vía
 *   PackageDispatchService::dispatch() (reutilizado, sin cambios) —
 *   todavía falta el traslado físico hasta el Aliado.
 *
 * No crea ningún estado nuevo, y no toca en absoluto Camino B
 * (DeliveryAssignmentService / Admin\DriverAssignment).
 */
class HubReleaseServiceTest extends TestCase
{
    use CreatesTestPackages;
    use RefreshDatabase;

    private function service(): HubReleaseService
    {
        return app(HubReleaseService::class);
    }

    /**
     * Deja un HUB propio ya resuelto como destino para un
     * estado/ciudad dados y devuelve ese Warehouse.
     */
    private function destinationHub(string $state = 'Carabobo', string $city = 'Valencia'): Warehouse
    {
        $hub = Warehouse::factory()->create(['state' => $state, 'city' => $city, 'is_active' => true]);

        WarehouseCoverage::create([
            'warehouse_id' => $hub->id,
            'state' => $state,
            'city' => $city,
            'is_active' => true,
        ]);

        return $hub;
    }

    private function verifiedPickupAlly(array $overrides = []): Ally
    {
        return $this->createAlly(array_merge([
            'is_verified_destination' => true,
            'status' => Ally::STATUS_ACTIVE,
        ], $overrides));
    }

    /*
    |--------------------------------------------------------------------------
    | 1) Retiro en HUB
    |--------------------------------------------------------------------------
    */

    public function test_hub_pickup_package_at_destination_hub_transitions_to_listo_retiro(): void
    {
        $ally = $this->createAlly();
        $hub = $this->destinationHub();
        $userId = $ally->user_id;

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_HUB,
            'destination_state' => 'Carabobo',
            'destination_city' => 'Valencia',
            'current_warehouse_id' => $hub->id,
            'destination_warehouse_id' => $hub->id,
            'destination_resolution_status' => LogisticsResolutionResult::STATUS_RESOLVED,
            'requires_delivery' => false,
            'pickup_mode' => Package::PICKUP_MODE_HUB,
            'pickup_ally_id' => null,
        ]);

        $released = $this->service()->release($package, $userId);

        $this->assertSame(Package::STATUS_LISTO_RETIRO, $released->current_status);
        $this->assertSame($hub->id, $released->current_warehouse_id);
        $this->assertSame($hub->id, $released->destination_warehouse_id);
        $this->assertNull($released->driver_id);

        $this->assertSame(1, PackageHistory::where('package_id', $package->id)->count());
        $history = PackageHistory::where('package_id', $package->id)->first();
        $this->assertSame(PackageHistory::EVENT_RECEPCION, $history->event_type);
        $this->assertSame(Package::STATUS_LISTO_RETIRO, $history->status);
    }

    public function test_hub_pickup_releases_driver_id_if_present(): void
    {
        $ally = $this->createAlly();
        $hub = $this->destinationHub();
        $driver = Driver::factory()->create();

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_HUB,
            'destination_state' => 'Carabobo',
            'destination_city' => 'Valencia',
            'current_warehouse_id' => $hub->id,
            'destination_warehouse_id' => $hub->id,
            'destination_resolution_status' => LogisticsResolutionResult::STATUS_RESOLVED,
            'requires_delivery' => false,
            'pickup_mode' => Package::PICKUP_MODE_HUB,
            'pickup_ally_id' => null,
            'driver_id' => $driver->id,
        ]);

        $released = $this->service()->release($package, $ally->user_id);

        $this->assertNull($released->driver_id);
    }

    /*
    |--------------------------------------------------------------------------
    | 2) Retiro en Aliado
    |--------------------------------------------------------------------------
    */

    public function test_ally_pickup_package_at_destination_hub_transitions_to_en_transito_nacional_toward_the_ally(): void
    {
        $ally = $this->createAlly();
        $hub = $this->destinationHub();
        $pickupAlly = $this->verifiedPickupAlly(['business_name' => 'Farmacia Aliada Valencia']);

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_HUB,
            'destination_state' => 'Carabobo',
            'destination_city' => 'Valencia',
            'current_warehouse_id' => $hub->id,
            'destination_warehouse_id' => $hub->id,
            'destination_resolution_status' => LogisticsResolutionResult::STATUS_RESOLVED,
            'requires_delivery' => false,
            'pickup_mode' => Package::PICKUP_MODE_ALLY,
            'pickup_ally_id' => $pickupAlly->id,
        ]);

        $released = $this->service()->release($package, $ally->user_id);

        $this->assertSame(Package::STATUS_EN_TRANSITO_NACIONAL, $released->current_status);
        $this->assertNotSame(Package::STATUS_LISTO_RETIRO, $released->current_status);

        $history = PackageHistory::where('package_id', $package->id)->latest('id')->first();
        $this->assertSame(PackageHistory::EVENT_SALIDA, $history->event_type);
        $this->assertSame('Farmacia Aliada Valencia', $history->destination_location);
        $this->assertSame(1, PackageHistory::where('package_id', $package->id)->count());
    }

    public function test_ally_pickup_rejects_when_pickup_ally_is_no_longer_active_or_verified(): void
    {
        $ally = $this->createAlly();
        $hub = $this->destinationHub();
        $pickupAlly = $this->verifiedPickupAlly(['is_verified_destination' => false]);

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_HUB,
            'destination_state' => 'Carabobo',
            'destination_city' => 'Valencia',
            'current_warehouse_id' => $hub->id,
            'destination_warehouse_id' => $hub->id,
            'destination_resolution_status' => LogisticsResolutionResult::STATUS_RESOLVED,
            'requires_delivery' => false,
            'pickup_mode' => Package::PICKUP_MODE_ALLY,
            'pickup_ally_id' => $pickupAlly->id,
        ]);

        $this->expectException(RuntimeException::class);

        $this->service()->release($package, $ally->user_id);
    }

    /*
    |--------------------------------------------------------------------------
    | 3) Delivery
    |--------------------------------------------------------------------------
    */

    public function test_delivery_package_at_destination_hub_transitions_to_listo_retiro_and_becomes_claimable(): void
    {
        $ally = $this->createAlly();
        $hub = $this->destinationHub();

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_HUB,
            'destination_state' => 'Carabobo',
            'destination_city' => 'Valencia',
            'current_warehouse_id' => $hub->id,
            'destination_warehouse_id' => $hub->id,
            'destination_resolution_status' => LogisticsResolutionResult::STATUS_RESOLVED,
            'requires_delivery' => true,
            'pickup_mode' => null,
            'pickup_ally_id' => null,
            'delivery_address' => 'Av. Bolívar, Valencia',
        ]);

        $released = $this->service()->release($package, $ally->user_id);

        $this->assertSame(Package::STATUS_LISTO_RETIRO, $released->current_status);
        $this->assertNull($released->driver_id);
        $this->assertTrue($released->isAvailableForDeliveryClaim());

        $history = PackageHistory::where('package_id', $package->id)->latest('id')->first();
        $this->assertSame(PackageHistory::EVENT_RECEPCION, $history->event_type);
        $this->assertSame(Package::STATUS_LISTO_RETIRO, $history->status);
    }

    /*
    |--------------------------------------------------------------------------
    | Re-sincronización en vivo — WarehouseCoverage puede corregirse
    | DESPUÉS de que un paquete fue recibido en HUB. release() debe
    | recuperar ese caso por sí solo, como parte del flujo normal de
    | "Liberar", sin ninguna pantalla/acción aparte.
    |--------------------------------------------------------------------------
    */

    /**
     * Reproduce la causa raíz real: un paquete quedó EN_HUB con
     * destination_resolution_status = no_coverage porque, en el
     * instante exacto de su recepción, la cobertura del HUB todavía no
     * existía. Si esa cobertura se crea DESPUÉS, release() debe
     * detectarlo en vivo, sincronizar destination_warehouse_id /
     * destination_resolution_status, y liberar con normalidad — sin
     * ningún UPDATE manual ni pantalla aparte.
     */
    public function test_release_resyncs_a_stale_no_coverage_resolution_when_coverage_now_exists(): void
    {
        $ally = $this->createAlly();
        $hub = Warehouse::factory()->create(['state' => 'Carabobo', 'city' => 'Valencia', 'is_active' => true]);

        // El paquete quedó con la resolución "congelada" de cuando fue
        // recibido, ANTES de que existiera cobertura para su destino.
        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_HUB,
            'destination_state' => 'Carabobo',
            'destination_city' => 'Valencia',
            'current_warehouse_id' => $hub->id,
            'destination_warehouse_id' => null,
            'destination_resolution_status' => LogisticsResolutionResult::STATUS_NO_COVERAGE,
            'requires_delivery' => false,
            'pickup_mode' => Package::PICKUP_MODE_HUB,
        ]);

        // Admin corrige la cobertura DESPUÉS de la recepción.
        WarehouseCoverage::create([
            'warehouse_id' => $hub->id,
            'state' => 'Carabobo',
            'city' => 'Valencia',
            'is_active' => true,
        ]);

        $released = $this->service()->release($package, $ally->user_id);

        $this->assertSame(Package::STATUS_LISTO_RETIRO, $released->current_status);
        $this->assertSame($hub->id, $released->destination_warehouse_id);
        $this->assertSame(LogisticsResolutionResult::STATUS_RESOLVED, $released->destination_resolution_status);
    }

    /**
     * Reproduce exactamente VEN-20260916-138347: recibido en "Hub
     * central" (Caracas, Distrito Capital) cuando la cobertura de esa
     * zona todavía no existía, con pickup_mode = HUB y sin delivery —
     * igual que la guía real. Debe terminar en LISTO_RETIRO.
     */
    public function test_release_resolves_the_real_ven_20260916_138347_case(): void
    {
        $ally = $this->createAlly(['city' => 'Cumaná', 'state' => 'Sucre']);
        $hub = Warehouse::factory()->create([
            'name' => 'Hub central',
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
            'is_active' => true,
        ]);

        $package = $this->createPackage($ally, [
            'tracking_number' => 'VEN-TEST-138347',
            'current_status' => Package::STATUS_EN_HUB,
            'destination_city' => 'Caracas',
            'destination_state' => 'Distrito Capital',
            'current_warehouse_id' => $hub->id,
            'destination_warehouse_id' => null,
            'destination_resolution_status' => LogisticsResolutionResult::STATUS_NO_COVERAGE,
            'requires_delivery' => false,
            'pickup_mode' => Package::PICKUP_MODE_HUB,
            'pickup_ally_id' => null,
        ]);

        // La cobertura de Distrito Capital se agrega DESPUÉS, tal como
        // ocurrió con la guía real.
        WarehouseCoverage::create([
            'warehouse_id' => $hub->id,
            'state' => 'Distrito Capital',
            'city' => null,
            'is_active' => true,
        ]);

        $released = $this->service()->release($package, $ally->user_id);

        $this->assertSame(Package::STATUS_LISTO_RETIRO, $released->current_status);
        $this->assertSame($hub->id, $released->destination_warehouse_id);
    }

    /**
     * Un paquete cuya resolución persistida YA es correcta no debe
     * verse afectado por la re-sincronización: release() no reescribe
     * innecesariamente destination_warehouse_id/destination_resolution_status
     * cuando ya coinciden con la resolución en vivo.
     */
    public function test_release_does_not_change_an_already_correct_resolution(): void
    {
        $ally = $this->createAlly();
        $hub = $this->destinationHub();

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_HUB,
            'destination_state' => 'Carabobo',
            'destination_city' => 'Valencia',
            'current_warehouse_id' => $hub->id,
            'destination_warehouse_id' => $hub->id,
            'destination_resolution_status' => LogisticsResolutionResult::STATUS_RESOLVED,
            'requires_delivery' => false,
            'pickup_mode' => Package::PICKUP_MODE_HUB,
        ]);

        $released = $this->service()->release($package, $ally->user_id);

        $this->assertSame(Package::STATUS_LISTO_RETIRO, $released->current_status);
        $this->assertSame($hub->id, $released->destination_warehouse_id);
    }

    /**
     * Si la cobertura SIGUE sin existir, release() debe rechazar de
     * forma segura, sin modificar current_status ni driver_id, y sin
     * crear ningún PackageHistory.
     */
    public function test_release_rejects_safely_when_coverage_still_does_not_exist(): void
    {
        $ally = $this->createAlly();
        $hub = Warehouse::factory()->create(['state' => 'Zulia', 'city' => 'Maracaibo', 'is_active' => true]);

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_HUB,
            'destination_state' => 'Zulia',
            'destination_city' => 'Maracaibo',
            'current_warehouse_id' => $hub->id,
            'destination_warehouse_id' => null,
            'destination_resolution_status' => LogisticsResolutionResult::STATUS_NO_COVERAGE,
            'requires_delivery' => false,
            'pickup_mode' => Package::PICKUP_MODE_HUB,
        ]);

        try {
            $this->service()->release($package, $ally->user_id);
            $this->fail('Se esperaba una RuntimeException por falta de cobertura.');
        } catch (RuntimeException $e) {
            $this->assertSame(
                'Este paquete no tiene un HUB destino resuelto. No se puede liberar hasta que '
                .'Admin revise su cobertura logística.',
                $e->getMessage()
            );
        }

        $package->refresh();
        $this->assertSame(Package::STATUS_EN_HUB, $package->current_status);
        $this->assertNull($package->driver_id);
        $this->assertSame(0, PackageHistory::where('package_id', $package->id)->count());
    }

    /**
     * canReleaseFromHub() (lectura en vivo, presentación) y release()
     * (ejecución) deben coincidir siempre: si el botón "Liberar" se
     * muestra, hacer clic en él debe funcionar. Antes de este fix,
     * canReleaseFromHub() ya resolvía en vivo pero release() confiaba
     * en el campo persistido — podían divergir exactamente en el caso
     * de una cobertura corregida después de la recepción.
     */
    public function test_can_release_from_hub_and_release_agree_after_coverage_is_fixed_later(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN_PRINCIPAL,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $ally = $this->createAlly();
        $hub = Warehouse::factory()->create(['state' => 'Zulia', 'city' => 'Maracaibo', 'is_active' => true]);

        $package = $this->createPackage($ally, [
            'tracking_number' => 'VEN-TEST-CANRELEASE',
            'current_status' => Package::STATUS_EN_HUB,
            'destination_state' => 'Zulia',
            'destination_city' => 'Maracaibo',
            'current_warehouse_id' => $hub->id,
            'destination_warehouse_id' => null,
            'destination_resolution_status' => LogisticsResolutionResult::STATUS_NO_COVERAGE,
            'requires_delivery' => false,
            'pickup_mode' => Package::PICKUP_MODE_HUB,
        ]);

        WarehouseCoverage::create([
            'warehouse_id' => $hub->id,
            'state' => 'Zulia',
            'city' => 'Maracaibo',
            'is_active' => true,
        ]);

        $component = Livewire::actingAs($admin)
            ->test(AdminPackageReception::class)
            ->set('trackingNumber', $package->tracking_number)
            ->call('search');

        // El botón "Liberar" se ofrece (canReleaseFromHub() resuelve en
        // vivo y ya ve la cobertura corregida)...
        $this->assertTrue($component->instance()->canReleaseFromHub());

        // ...y hacer clic en él realmente funciona, sin el rechazo que
        // ocurría antes por la resolución persistida obsoleta.
        $component->call('releaseFromHub')->assertSet('errorMessage', null);

        $this->assertSame(Package::STATUS_LISTO_RETIRO, $package->fresh()->current_status);
    }

    /*
    |--------------------------------------------------------------------------
    | 4-8) Rechazos
    |--------------------------------------------------------------------------
    */

    public function test_rejects_when_package_is_not_en_hub(): void
    {
        $ally = $this->createAlly();
        $hub = $this->destinationHub();

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
            'destination_state' => 'Carabobo',
            'destination_city' => 'Valencia',
            'current_warehouse_id' => $hub->id,
            'destination_warehouse_id' => $hub->id,
            'destination_resolution_status' => LogisticsResolutionResult::STATUS_RESOLVED,
            'requires_delivery' => false,
            'pickup_mode' => Package::PICKUP_MODE_HUB,
        ]);

        $this->expectException(RuntimeException::class);

        $this->service()->release($package, $ally->user_id);
    }

    public function test_rejects_when_current_warehouse_does_not_match_the_destination_hub(): void
    {
        $ally = $this->createAlly();
        $originHub = Warehouse::factory()->create(['is_active' => true]);
        $destinationHub = $this->destinationHub();

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_HUB,
            'destination_state' => 'Carabobo',
            'destination_city' => 'Valencia',
            'current_warehouse_id' => $originHub->id,
            'destination_warehouse_id' => $destinationHub->id,
            'destination_resolution_status' => LogisticsResolutionResult::STATUS_RESOLVED,
            'requires_delivery' => false,
            'pickup_mode' => Package::PICKUP_MODE_HUB,
        ]);

        try {
            $this->service()->release($package, $ally->user_id);
            $this->fail('Se esperaba una RuntimeException por HUB actual distinto del HUB destino.');
        } catch (RuntimeException $e) {
            // esperado
        }

        $this->assertSame(0, PackageHistory::where('package_id', $package->id)->count());
    }

    public function test_rejects_when_resolution_is_not_valid(): void
    {
        $ally = $this->createAlly();
        $hub = Warehouse::factory()->create(['is_active' => true]);

        // Sin WarehouseCoverage configurada: la resolución en vivo da
        // no_coverage, isAtDestinationWarehouse() devuelve false.
        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_HUB,
            'destination_state' => 'Carabobo',
            'destination_city' => 'Valencia',
            'current_warehouse_id' => $hub->id,
            'destination_warehouse_id' => $hub->id,
            'destination_resolution_status' => LogisticsResolutionResult::STATUS_RESOLVED,
            'requires_delivery' => false,
            'pickup_mode' => Package::PICKUP_MODE_HUB,
        ]);

        $this->expectException(RuntimeException::class);

        $this->service()->release($package, $ally->user_id);
    }

    public function test_rejects_ally_pickup_mode_without_a_valid_pickup_ally_id(): void
    {
        $ally = $this->createAlly();
        $hub = $this->destinationHub();

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_HUB,
            'destination_state' => 'Carabobo',
            'destination_city' => 'Valencia',
            'current_warehouse_id' => $hub->id,
            'destination_warehouse_id' => $hub->id,
            'destination_resolution_status' => LogisticsResolutionResult::STATUS_RESOLVED,
            'requires_delivery' => false,
            'pickup_mode' => Package::PICKUP_MODE_ALLY,
            'pickup_ally_id' => null,
        ]);

        $this->expectException(RuntimeException::class);

        $this->service()->release($package, $ally->user_id);
    }

    public function test_rejects_hub_pickup_mode_with_a_pickup_ally_id_set(): void
    {
        $ally = $this->createAlly();
        $hub = $this->destinationHub();
        $pickupAlly = $this->verifiedPickupAlly();

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_HUB,
            'destination_state' => 'Carabobo',
            'destination_city' => 'Valencia',
            'current_warehouse_id' => $hub->id,
            'destination_warehouse_id' => $hub->id,
            'destination_resolution_status' => LogisticsResolutionResult::STATUS_RESOLVED,
            'requires_delivery' => false,
            'pickup_mode' => Package::PICKUP_MODE_HUB,
            'pickup_ally_id' => $pickupAlly->id,
        ]);

        $this->expectException(RuntimeException::class);

        $this->service()->release($package, $ally->user_id);
    }

    /*
    |--------------------------------------------------------------------------
    | 9) Doble ejecución protegida
    |--------------------------------------------------------------------------
    */

    public function test_a_second_release_attempt_is_rejected_and_does_not_duplicate_history(): void
    {
        $ally = $this->createAlly();
        $hub = $this->destinationHub();

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_HUB,
            'destination_state' => 'Carabobo',
            'destination_city' => 'Valencia',
            'current_warehouse_id' => $hub->id,
            'destination_warehouse_id' => $hub->id,
            'destination_resolution_status' => LogisticsResolutionResult::STATUS_RESOLVED,
            'requires_delivery' => false,
            'pickup_mode' => Package::PICKUP_MODE_HUB,
            'pickup_ally_id' => null,
        ]);

        $released = $this->service()->release($package, $ally->user_id);
        $this->assertSame(Package::STATUS_LISTO_RETIRO, $released->current_status);
        $this->assertSame(1, PackageHistory::where('package_id', $package->id)->count());

        try {
            $this->service()->release($released, $ally->user_id);
            $this->fail('Se esperaba una RuntimeException al liberar dos veces el mismo paquete.');
        } catch (RuntimeException $e) {
            // esperado: ya no está EN_HUB.
        }

        $this->assertSame(1, PackageHistory::where('package_id', $package->id)->count());
        $this->assertSame(Package::STATUS_LISTO_RETIRO, $released->fresh()->current_status);
    }

    /*
    |--------------------------------------------------------------------------
    | 10) Regresión — recepción inicial y recepción HUB -> HUB
    |--------------------------------------------------------------------------
    */

    public function test_regression_initial_and_transfer_hub_reception_are_unaffected(): void
    {
        $ally = $this->createAlly();
        $receivingHub = Warehouse::factory()->create(['is_active' => true]);

        $originPackage = $this->createPackage($ally, [
            'current_status' => Package::STATUS_RECOLECTADO_VENEXPRESS,
        ]);

        $receivedOrigin = app(HubReceptionService::class)->receiveAtWarehouse(
            $originPackage,
            $ally->user_id,
            $receivingHub,
        );

        $this->assertSame(Package::STATUS_EN_HUB, $receivedOrigin->current_status);
        $this->assertSame($receivingHub->id, $receivedOrigin->current_warehouse_id);

        $destinationHub = $this->destinationHub('Zulia', 'Maracaibo');

        $transferPackage = $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
            'destination_state' => 'Zulia',
            'destination_city' => 'Maracaibo',
            'destination_warehouse_id' => $destinationHub->id,
            'destination_resolution_status' => LogisticsResolutionResult::STATUS_RESOLVED,
        ]);

        $receivedTransfer = app(HubReceptionService::class)->receiveTransferAtWarehouse(
            $transferPackage,
            $ally->user_id,
            $destinationHub,
        );

        $this->assertSame(Package::STATUS_EN_HUB, $receivedTransfer->current_status);
        $this->assertSame($destinationHub->id, $receivedTransfer->current_warehouse_id);
    }

    /*
    |--------------------------------------------------------------------------
    | 11) Regresión — Ally\PackageReception (retiro en Aliado)
    |--------------------------------------------------------------------------
    */

    public function test_regression_ally_package_reception_still_works_after_hub_release(): void
    {
        $ally = $this->createAlly();
        $hub = $this->destinationHub();
        $pickupAlly = $this->verifiedPickupAlly(['business_name' => 'Farmacia Aliada Valencia']);

        $package = $this->createPackage($ally, [
            'tracking_number' => 'VEN-TEST-ALLY-REGRESSION',
            'current_status' => Package::STATUS_EN_HUB,
            'destination_state' => 'Carabobo',
            'destination_city' => 'Valencia',
            'current_warehouse_id' => $hub->id,
            'destination_warehouse_id' => $hub->id,
            'destination_resolution_status' => LogisticsResolutionResult::STATUS_RESOLVED,
            'requires_delivery' => false,
            'pickup_mode' => Package::PICKUP_MODE_ALLY,
            'pickup_ally_id' => $pickupAlly->id,
        ]);

        $this->service()->release($package, $ally->user_id);

        Livewire::actingAs($pickupAlly->user)
            ->test(AllyPackageReception::class)
            ->set('trackingNumber', 'VEN-TEST-ALLY-REGRESSION')
            ->call('receive')
            ->assertSet('error', null);

        $this->assertSame(Package::STATUS_LISTO_RETIRO, $package->fresh()->current_status);
    }

    /*
    |--------------------------------------------------------------------------
    | 12) Regresión — Delivery Camino A (claimForDelivery)
    |--------------------------------------------------------------------------
    */

    public function test_regression_delivery_camino_a_claim_for_delivery_still_works_after_hub_release(): void
    {
        $ally = $this->createAlly();
        $hub = $this->destinationHub();
        $deliveryDriver = Driver::factory()->create([
            'driver_type' => Driver::TYPE_DELIVERY,
            'status' => Driver::STATUS_ACTIVE,
        ]);

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_HUB,
            'destination_state' => 'Carabobo',
            'destination_city' => 'Valencia',
            'current_warehouse_id' => $hub->id,
            'destination_warehouse_id' => $hub->id,
            'destination_resolution_status' => LogisticsResolutionResult::STATUS_RESOLVED,
            'requires_delivery' => true,
            'pickup_mode' => null,
            'pickup_ally_id' => null,
            'delivery_address' => 'Av. Bolívar, Valencia',
        ]);

        $released = $this->service()->release($package, $ally->user_id);
        $this->assertSame(Package::STATUS_LISTO_RETIRO, $released->current_status);

        $claimed = app(PackageService::class)->claimForDelivery(
            $released,
            $deliveryDriver,
            $ally->user_id,
        );

        $this->assertSame($deliveryDriver->id, $claimed->driver_id);
        $this->assertSame(Package::DELIVERY_ACCEPTED, $claimed->delivery_status);

        // claimForDelivery() lleva LISTO_RETIRO -> EN_TRANSITO_NACIONAL
        // al reclamarlo de verdad (para que completeDelivery() funcione
        // igual sin importar de cuál de los dos estados vino) — esto no
        // cambió, sigue siendo responsabilidad exclusiva de Delivery.
        $this->assertSame(Package::STATUS_EN_TRANSITO_NACIONAL, $claimed->current_status);
    }

    /**
     * Regresión — Client\Dashboard::acceptDelivery() exige
     * current_status === LISTO_RETIRO para que el cliente pueda
     * confirmar que recibirá su pedido a domicilio. Antes de este fix,
     * releaseForDelivery() dejaba el paquete en EN_TRANSITO_NACIONAL,
     * así que el cliente NUNCA podía confirmar (quedaba bloqueado con
     * "tu paquete debe estar Listo para Retiro" indefinidamente). Este
     * test prueba que ese flujo, ya existente y no modificado, ahora
     * funciona de punta a punta para un paquete liberado desde HUB.
     */
    public function test_regression_client_can_accept_delivery_after_hub_release(): void
    {
        $ally = $this->createAlly();
        $hub = $this->destinationHub();

        $clientUser = User::factory()->create([
            'role' => User::ROLE_CLIENTE,
            'status' => User::STATUS_ACTIVE,
            'email' => 'cliente@example.com',
        ]);

        Customer::create([
            'id_doc' => 'V-11111111',
            'name' => 'Cliente de Prueba',
            'phone' => '0414-0000000',
            'email' => $clientUser->email,
        ]);

        $package = $this->createPackage($ally, [
            'recipient_id_doc' => 'V-11111111',
            'current_status' => Package::STATUS_EN_HUB,
            'destination_state' => 'Carabobo',
            'destination_city' => 'Valencia',
            'current_warehouse_id' => $hub->id,
            'destination_warehouse_id' => $hub->id,
            'destination_resolution_status' => LogisticsResolutionResult::STATUS_RESOLVED,
            'requires_delivery' => true,
            'pickup_mode' => null,
            'pickup_ally_id' => null,
            'delivery_status' => Package::DELIVERY_PENDING,
            'delivery_address' => 'Av. Bolívar, Valencia',
        ]);

        $released = $this->service()->release($package, $ally->user_id);
        $this->assertSame(Package::STATUS_LISTO_RETIRO, $released->current_status);

        Livewire::actingAs($clientUser)
            ->test(ClientDashboard::class)
            ->call('acceptDelivery', $package->id)
            ->assertHasNoErrors();

        $this->assertSame(Package::DELIVERY_ACCEPTED, $package->fresh()->delivery_status);
    }
}
