<?php

namespace Tests\Feature;

use App\Jobs\GeocodePackageDeliveryAddress;
use App\Livewire\Admin\PackageModality;
use App\Models\Ally;
use App\Models\AuditLog;
use App\Models\Driver;
use App\Models\Package;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseCoverage;
use App\Services\LogisticsResolutionResult;
use App\Services\PackageModalityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use RuntimeException;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Admin cambia la modalidad (domicilio <-> retiro) antes de que la guía
 * salga a reparto, sin cambiar el total cobrado.
 */
class PackageModalityTest extends TestCase
{
    use CreatesTestPackages;
    use RefreshDatabase;

    private User $admin;

    private Warehouse $hub;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();

        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN_PRINCIPAL,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $this->hub = Warehouse::factory()->create(['state' => 'Carabobo', 'city' => 'Valencia']);

        WarehouseCoverage::create([
            'warehouse_id' => $this->hub->id,
            'state' => 'Carabobo',
            'city' => 'Valencia',
            'is_active' => true,
        ]);
    }

    private function package(array $overrides = []): Package
    {
        return $this->createPackage($this->createAlly(), array_merge([
            'destination_state' => 'Carabobo',
            'destination_city' => 'Valencia',
            'current_warehouse_id' => $this->hub->id,
            'destination_warehouse_id' => $this->hub->id,
            'destination_resolution_status' => LogisticsResolutionResult::STATUS_RESOLVED,
        ], $overrides));
    }

    private function verifiedPickupAlly(string $state = 'Carabobo'): Ally
    {
        return $this->createAlly([
            'state' => $state,
            'city' => 'Valencia',
            'is_verified_destination' => true,
        ]);
    }

    private function service(): PackageModalityService
    {
        return app(PackageModalityService::class);
    }

    public function test_a_package_in_transit_switches_from_hub_pickup_to_home_delivery_without_changing_its_price(): void
    {
        $package = $this->package([
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
            'pickup_mode' => Package::PICKUP_MODE_HUB,
        ]);
        $total = (string) $package->total_price_usd;

        $changed = $this->service()->change($package, [
            'modality' => PackageModalityService::MODALITY_DELIVERY,
            'delivery_address' => 'Av. Bolívar, casa 10',
        ], $this->admin->id);

        $this->assertTrue($changed->requires_delivery);
        $this->assertNull($changed->pickup_mode);
        $this->assertSame('Av. Bolívar, casa 10', $changed->delivery_address);
        $this->assertSame(Package::STATUS_EN_TRANSITO_NACIONAL, $changed->current_status);
        $this->assertSame($total, (string) $changed->total_price_usd);
        $this->assertTrue(AuditLog::where('action', 'package.modality_changed')->where('target_id', $package->id)->exists());
        Queue::assertPushed(GeocodePackageDeliveryAddress::class);
    }

    public function test_a_package_waiting_for_pickup_at_the_hub_becomes_pending_delivery(): void
    {
        $package = $this->package([
            'current_status' => Package::STATUS_LISTO_RETIRO,
            'pickup_mode' => Package::PICKUP_MODE_HUB,
        ]);

        $changed = $this->service()->change($package, [
            'modality' => PackageModalityService::MODALITY_DELIVERY,
            'delivery_address' => 'Av. Bolívar, casa 10',
        ], $this->admin->id);

        $this->assertSame(Package::STATUS_PENDIENTE_ENTREGA, $changed->current_status);
        $this->assertTrue($changed->isAvailableForDeliveryClaim());
    }

    public function test_a_package_pending_delivery_becomes_ready_for_pickup_at_the_hub(): void
    {
        $package = $this->package([
            'current_status' => Package::STATUS_PENDIENTE_ENTREGA,
            'requires_delivery' => true,
            'delivery_address' => 'Calle 1',
        ]);

        $changed = $this->service()->change($package, [
            'modality' => PackageModalityService::MODALITY_HUB,
        ], $this->admin->id);

        $this->assertSame(Package::STATUS_LISTO_RETIRO, $changed->current_status);
        $this->assertFalse($changed->requires_delivery);
        $this->assertSame(Package::PICKUP_MODE_HUB, $changed->pickup_mode);
    }

    public function test_switching_to_an_agency_dispatches_it_to_that_agency(): void
    {
        $pickupAlly = $this->verifiedPickupAlly();

        $package = $this->package([
            'current_status' => Package::STATUS_PENDIENTE_ENTREGA,
            'requires_delivery' => true,
            'delivery_address' => 'Calle 1',
        ]);

        $changed = $this->service()->change($package, [
            'modality' => PackageModalityService::MODALITY_ALLY,
            'pickup_ally_id' => $pickupAlly->id,
        ], $this->admin->id);

        $this->assertSame(Package::STATUS_EN_TRANSITO_NACIONAL, $changed->current_status);
        $this->assertSame($pickupAlly->id, $changed->pickup_ally_id);
    }

    public function test_only_verified_agencies_in_the_destination_state_can_be_chosen(): void
    {
        $package = $this->package(['current_status' => Package::STATUS_EN_HUB, 'pickup_mode' => Package::PICKUP_MODE_HUB]);
        $otherState = $this->verifiedPickupAlly('Zulia');

        $this->expectExceptionMessage('Elige una agencia de retiro activa y verificada del estado destino.');

        $this->service()->change($package, [
            'modality' => PackageModalityService::MODALITY_ALLY,
            'pickup_ally_id' => $otherState->id,
        ], $this->admin->id);
    }

    public function test_it_cannot_change_once_it_left_for_delivery_or_reached_the_agency(): void
    {
        $driver = Driver::factory()->create(['driver_type' => Driver::TYPE_DELIVERY]);
        $pickupAlly = $this->verifiedPickupAlly();

        $cases = [
            $this->package(['current_status' => Package::STATUS_EN_RUTA, 'requires_delivery' => true, 'driver_id' => $driver->id]),
            $this->package(['current_status' => Package::STATUS_ENTREGADO]),
            $this->package(['current_status' => Package::STATUS_LISTO_RETIRO, 'pickup_mode' => Package::PICKUP_MODE_ALLY, 'pickup_ally_id' => $pickupAlly->id]),
        ];

        foreach ($cases as $package) {
            try {
                $this->service()->change($package, [
                    'modality' => PackageModalityService::MODALITY_HUB,
                ], $this->admin->id);
                $this->fail("Se cambió la modalidad de una guía en {$package->current_status}.");
            } catch (RuntimeException $e) {
                $this->assertNotNull($this->service()->blockedReason($package));
            }
        }
    }

    public function test_admin_changes_the_modality_from_the_panel(): void
    {
        $package = $this->package([
            'current_status' => Package::STATUS_EN_HUB,
            'pickup_mode' => Package::PICKUP_MODE_HUB,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.packages.modality', ['guia' => $package->tracking_number]))
            ->assertOk()
            ->assertSee('Retiro en almacén');

        Livewire::actingAs($this->admin)
            ->test(PackageModality::class, ['trackingNumber' => $package->tracking_number])
            ->set('modality', PackageModalityService::MODALITY_DELIVERY)
            ->set('deliveryAddress', '')
            ->call('save')
            ->assertHasErrors(['deliveryAddress' => 'required'])
            ->set('deliveryAddress', 'Av. Bolívar, casa 10')
            ->call('save')
            ->assertSet('errorMessage', null);

        $this->assertTrue($package->fresh()->requires_delivery);
    }

    public function test_non_admins_cannot_open_the_page(): void
    {
        $this->actingAs($this->createAlly()->user)
            ->get(route('admin.packages.modality'))
            ->assertForbidden();
    }
}
