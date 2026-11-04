<?php

namespace Tests\Feature\Ally;

use App\Livewire\Admin\UsersManager;
use App\Livewire\Almacen\Dashboard as WarehouseDashboard;
use App\Models\Ally;
use App\Models\Driver;
use App\Models\Package;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseCoverage;
use App\Services\AllyStaffService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * La franquicia también funciona como almacén de la red: tiene su
 * propio Warehouse (misma dirección física), aparece como parada de
 * las rutas HUB y el Franquiciado (y sus Taquillas) reciben paquetes y
 * los despachan al cliente o a un repartidor desde el panel Aliado.
 */
class FranchiseWarehouseTest extends TestCase
{
    use CreatesTestPackages;
    use RefreshDatabase;

    /** Franquicia en Valencia con su almacén y cobertura para Valencia. */
    private function createFranchiseWithWarehouse(): Ally
    {
        $ally = $this->createAlly([
            'commission_percentage' => 0,
            'city' => 'Valencia',
            'state' => 'Carabobo',
            'address' => 'Av. Bolívar Norte',
        ]);
        $ally->user->update(['role' => User::ROLE_FRANQUICIADO, 'account_verified_at' => now()]);

        $warehouse = Warehouse::create([
            'ally_id' => $ally->id,
            'name' => $ally->business_name,
            'city' => 'Valencia',
            'state' => 'Carabobo',
            'address' => 'Av. Bolívar Norte',
            'is_active' => true,
        ]);

        WarehouseCoverage::create([
            'warehouse_id' => $warehouse->id,
            'state' => 'Carabobo',
            'city' => 'Valencia',
            'is_active' => true,
        ]);

        return $ally->fresh();
    }

    private function taquilla(Ally $ally): User
    {
        return app(AllyStaffService::class)->create($ally, [
            'name' => 'Taquilla Valencia',
            'username' => 'taq-'.$ally->id,
            'password' => 'password-seguro',
        ]);
    }

    public function test_admin_creating_a_franchise_also_creates_its_warehouse_at_the_same_address(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN_PRINCIPAL,
            'status' => User::STATUS_ACTIVE,
            'password' => 'admin-password',
        ]);

        Livewire::actingAs($admin)
            ->test(UsersManager::class)
            ->set('name', 'Franquicia Valencia')
            ->set('email', 'valencia@example.com')
            ->set('password', 'Password-seguro-123')
            ->set('password_confirmation', 'Password-seguro-123')
            ->set('role', User::ROLE_FRANQUICIADO)
            ->set('franchise_fee_percentage', '8')
            ->set('business_name', 'Venexpress Valencia')
            ->set('rif', 'J-11111111-1')
            ->set('state', 'Carabobo')
            ->set('city', 'Valencia')
            ->set('address', 'Av. Bolívar Norte, local 4')
            ->set('adminPassword', 'admin-password')
            ->call('createUser')
            ->assertHasNoErrors();

        $ally = User::where('email', 'valencia@example.com')->firstOrFail()->ally;
        $warehouse = $ally->warehouse;

        $this->assertNotNull($warehouse);
        $this->assertTrue($warehouse->isFranchise());
        $this->assertTrue($warehouse->is_active);
        $this->assertSame('Venexpress Valencia', $warehouse->name);
        $this->assertSame('Valencia', $warehouse->city);
        $this->assertSame('Carabobo', $warehouse->state);
        $this->assertSame('Av. Bolívar Norte, local 4', $warehouse->address);
    }

    public function test_a_regular_ally_does_not_get_a_warehouse(): void
    {
        $ally = $this->createAlly();

        $this->assertNull($ally->user->operatingWarehouse());
        $this->assertNull($ally->warehouse);
    }

    public function test_deactivating_the_franchise_deactivates_its_warehouse(): void
    {
        $ally = $this->createFranchiseWithWarehouse();
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN_PRINCIPAL, 'status' => User::STATUS_ACTIVE]);

        Livewire::actingAs($admin)->test(UsersManager::class)->call('toggleStatus', $ally->user_id);
        $this->assertFalse($ally->warehouse->fresh()->is_active);

        Livewire::actingAs($admin)->test(UsersManager::class)->call('toggleStatus', $ally->user_id);
        $this->assertTrue($ally->warehouse->fresh()->is_active);
    }

    public function test_franchise_owner_and_taquilla_can_open_the_warehouse_panel_but_regular_allies_cannot(): void
    {
        $franchise = $this->createFranchiseWithWarehouse();
        $taquilla = $this->taquilla($franchise);

        $this->actingAs($franchise->user)->get(route('ally.warehouse'))
            ->assertOk()
            ->assertSee($franchise->business_name)
            ->assertSee('Almacén (recibir y despachar)');

        $this->actingAs($taquilla)->get(route('ally.warehouse'))->assertOk();

        $regular = $this->createAlly();
        $regularTaquilla = $this->taquilla($regular);

        $this->actingAs($regular->user)->get(route('ally.warehouse'))->assertForbidden();
        $this->actingAs($regularTaquilla)->get(route('ally.warehouse'))->assertForbidden();
        $this->actingAs($regular->user)->get(route('ally.dashboard'))
            ->assertOk()
            ->assertDontSee('Almacén (recibir y despachar)');
    }

    public function test_franchise_receives_a_package_from_a_hub_driver_on_its_route_stop(): void
    {
        $franchise = $this->createFranchiseWithWarehouse();
        $warehouse = $franchise->warehouse;
        $taquilla = $this->taquilla($franchise);

        $hubDriver = Driver::factory()->create(['driver_type' => Driver::TYPE_HUB]);

        $route = Route::create([
            'name' => 'HUB Caracas → Valencia',
            'city' => 'Valencia',
            'state' => 'Carabobo',
            'route_type' => Route::TYPE_HUB_DISTRIBUTION,
            'status' => Route::STATUS_IN_PROGRESS,
            'driver_id' => $hubDriver->id,
            'created_by' => $franchise->user_id,
            'started_at' => now(),
        ]);

        RouteStop::create([
            'route_id' => $route->id,
            'warehouse_id' => $warehouse->id,
            'sequence' => 1,
            'status' => RouteStop::STATUS_PENDING,
        ]);

        $package = $this->createPackage($this->createAlly(), [
            'destination_city' => 'Valencia',
            'destination_state' => 'Carabobo',
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
            'driver_id' => $hubDriver->id,
            'pickup_mode' => Package::PICKUP_MODE_HUB,
            'recipient_id_doc' => 'V-87654321',
        ]);

        // La parada HUB de la franquicia aparece en su panel.
        Livewire::actingAs($taquilla)
            ->test(WarehouseDashboard::class)
            ->assertSee('Ruta #'.$route->id)
            ->set('trackingNumber', $package->tracking_number)
            ->call('scanArrival')
            ->assertSet('scanError', null);

        $package->refresh();

        $this->assertSame(Package::STATUS_LISTO_RETIRO, $package->current_status);
        $this->assertSame($warehouse->id, $package->current_warehouse_id);
        $this->assertNull($package->driver_id);

        // ...y luego lo entrega al cliente que viene a retirarlo.
        Livewire::actingAs($franchise->user)
            ->test(WarehouseDashboard::class)
            ->set('dispatchTrackingNumber', $package->tracking_number)
            ->call('searchDispatch')
            ->set('recipientIdDoc', 'V-87654321')
            ->call('deliverToClient')
            ->assertHasNoErrors();

        $this->assertSame(Package::STATUS_ENTREGADO, $package->fresh()->current_status);
    }

    public function test_franchise_hands_a_package_to_a_delivery_driver(): void
    {
        $franchise = $this->createFranchiseWithWarehouse();
        $warehouse = $franchise->warehouse;

        $driver = Driver::factory()->create([
            'user_id' => User::factory()->create(['role' => User::ROLE_REPARTIDOR, 'status' => User::STATUS_ACTIVE])->id,
            'driver_type' => Driver::TYPE_DELIVERY,
            'status' => Driver::STATUS_ACTIVE,
        ]);

        $route = Route::create([
            'name' => 'Reparto desde la franquicia',
            'city' => 'Valencia',
            'state' => 'Carabobo',
            'route_type' => Route::TYPE_DELIVERY,
            'status' => Route::STATUS_IN_PROGRESS,
            'driver_id' => $driver->id,
            'created_by' => $franchise->user_id,
            'started_at' => now(),
            'origin_warehouse_id' => $warehouse->id,
        ]);

        $package = $this->createPackage($this->createAlly(), [
            'destination_city' => 'Valencia',
            'destination_state' => 'Carabobo',
            'current_status' => Package::STATUS_PENDIENTE_ENTREGA,
            'current_warehouse_id' => $warehouse->id,
            'requires_delivery' => true,
        ]);

        Livewire::actingAs($franchise->user)
            ->test(WarehouseDashboard::class)
            ->set('dispatchTrackingNumber', $package->tracking_number)
            ->call('searchDispatch')
            ->assertSee('Reparto desde la franquicia')
            ->call('assignToDriver', $route->id)
            ->assertSet('dispatchError', null);

        $package->refresh();

        $this->assertSame(Package::STATUS_EN_RUTA, $package->current_status);
        $this->assertSame($driver->id, $package->driver_id);
    }

    public function test_franchise_warehouse_is_offered_as_a_hub_route_stop(): void
    {
        $franchise = $this->createFranchiseWithWarehouse();
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN_PRINCIPAL, 'status' => User::STATUS_ACTIVE]);

        $this->actingAs($admin)->get(route('admin.warehouses'))
            ->assertOk()
            ->assertSee($franchise->business_name)
            ->assertSee('Franquicia');
    }
}
