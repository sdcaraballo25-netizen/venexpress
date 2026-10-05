<?php

namespace Tests\Feature\Driver;

use App\Livewire\Driver\Dashboard;
use App\Livewire\Driver\PackageDetail;
use App\Livewire\Driver\Scanner;
use App\Models\AuditLog;
use App\Models\Driver;
use App\Models\Incident;
use App\Models\Package;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseCoverage;
use App\Services\RouteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Operación del repartidor de entrega desde el panel web (MVP sin
 * depender de Flutter) y la API, compartiendo la misma decisión
 * (LogisticsScanService::scanForDelivery()):
 *
 * - Paquetes de SU ruta (recolección en una parada o asignación de
 *   Admin a la ruta).
 * - Toma por escaneo (PackageService::claimForDelivery()): solo con una
 *   ruta de reparto en curso, y solo un paquete LISTO_RETIRO que ya
 *   está en el HUB destino de la zona de esa ruta (WarehouseCoverage).
 *   Conocer el número de guía no basta.
 * - No existe ninguna lista ni búsqueda global de paquetes, y los datos
 *   personales solo se muestran una vez validado que la guía le toca.
 */
class DriverDeliveryScanTest extends TestCase
{
    use CreatesTestPackages;
    use RefreshDatabase;

    private const RECIPIENT = 'María Gómez';

    /** HUB de Valencia, con cobertura activa de Valencia (Carabobo). */
    private function valenciaHub(): Warehouse
    {
        return $this->hubCovering('Carabobo', 'Valencia');
    }

    private function hubCovering(string $state, string $city): Warehouse
    {
        $hub = Warehouse::factory()->create(['state' => $state, 'city' => $city]);

        WarehouseCoverage::create([
            'warehouse_id' => $hub->id,
            'state' => $state,
            'city' => $city,
            'is_active' => true,
        ]);

        return $hub;
    }

    /** Paquete a domicilio ya recibido (LISTO_RETIRO) en su HUB destino. */
    private function readyAtHub(Warehouse $hub, array $overrides = []): Package
    {
        return $this->readyForDelivery(array_merge([
            'current_warehouse_id' => $hub->id,
            'destination_warehouse_id' => $hub->id,
        ], $overrides));
    }

    private function deliveryDriver(string $password = 'password-seguro'): array
    {
        $user = User::factory()->create([
            'role' => User::ROLE_REPARTIDOR,
            'status' => User::STATUS_ACTIVE,
            'password' => bcrypt($password),
        ]);

        $driver = Driver::factory()->create([
            'user_id' => $user->id,
            'status' => Driver::STATUS_ACTIVE,
            'verification_status' => Driver::VERIFICATION_VERIFIED,
            'driver_type' => Driver::TYPE_DELIVERY,
        ]);

        return [$user, $driver];
    }

    private function deliveryRoute(Driver $driver, User $user, array $overrides = []): Route
    {
        return Route::create(array_merge([
            'city' => 'Valencia',
            'state' => 'Carabobo',
            'name' => 'Reparto Valencia',
            'driver_id' => $driver->id,
            'created_by' => $user->id,
            'status' => Route::STATUS_IN_PROGRESS,
            'started_at' => now(),
            'route_type' => Route::TYPE_DELIVERY,
        ], $overrides));
    }

    /** Paquete listo para entrega a domicilio, sin repartidor ni ruta. */
    private function readyForDelivery(array $overrides = []): Package
    {
        return $this->createPackage($this->createAlly(), array_merge([
            'requires_delivery' => true,
            'delivery_address' => 'Av. Bolívar, casa 10',
            'current_status' => Package::STATUS_LISTO_RETIRO,
        ], $overrides));
    }

    /** Paquete que Admin asignó a la ruta del repartidor (DeliveryAssignmentService). */
    private function assignedToRoute(Route $route, Driver $driver): Package
    {
        $package = $this->readyForDelivery([
            'driver_id' => $driver->id,
            'delivery_status' => Package::DELIVERY_ACCEPTED,
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
        ]);

        AuditLog::create([
            'actor_user_id' => $route->created_by,
            'action' => 'package.delivery_assigned',
            'target_type' => Package::class,
            'target_id' => $package->id,
            'description' => 'Asignado a la ruta.',
            'metadata' => ['route_id' => $route->id, 'driver_id' => $driver->id],
        ]);

        return $package;
    }

    private function scan(User $user, string $trackingNumber)
    {
        return Livewire::actingAs($user)
            ->test(Scanner::class)
            ->set('trackingNumber', $trackingNumber)
            ->call('searchPackage');
    }

    private function apiHeaders(User $user): array
    {
        $token = $this->postJson('/api/driver/login', [
            'email' => $user->email,
            'password' => 'password-seguro',
            'device_name' => 'telefono',
        ])->assertOk()->json('token');

        return ['Authorization' => "Bearer {$token}"];
    }

    /*
    |--------------------------------------------------------------------------
    | Modelo con ruta
    |--------------------------------------------------------------------------
    */

    public function test_driver_collects_a_package_at_a_stop_of_their_delivery_route(): void
    {
        [$user, $driver] = $this->deliveryDriver();
        $route = $this->deliveryRoute($driver, $user);
        $ally = $this->createAlly();

        RouteStop::create([
            'route_id' => $route->id,
            'ally_id' => $ally->id,
            'sequence' => 1,
            'status' => RouteStop::STATUS_PENDING,
        ]);

        $package = $this->createPackage($ally, [
            'requires_delivery' => true,
            'current_status' => Package::STATUS_RECIBIDO_AGENCIA,
        ]);

        $this->scan($user, $package->tracking_number)
            ->assertSet('errorMessage', null)
            ->assertSet('lastAction', 'delivery_collection')
            ->assertSee('Abrir entrega');

        $package->refresh();
        $this->assertSame(Package::STATUS_RECOLECTADO_VENEXPRESS, $package->current_status);
        $this->assertSame($driver->id, $package->driver_id);
    }

    public function test_driver_can_open_a_package_assigned_to_their_route(): void
    {
        [$user, $driver] = $this->deliveryDriver();
        $route = $this->deliveryRoute($driver, $user);
        $package = $this->assignedToRoute($route, $driver);

        $this->scan($user, $package->tracking_number)
            ->assertSet('errorMessage', null)
            ->assertSet('lastAction', 'delivery_assigned')
            ->assertSee(self::RECIPIENT);
    }

    public function test_driver_cannot_process_a_package_from_another_drivers_route(): void
    {
        [$userA] = $this->deliveryDriver();
        [$userB, $driverB] = $this->deliveryDriver();
        $routeB = $this->deliveryRoute($driverB, $userB);
        $package = $this->assignedToRoute($routeB, $driverB);

        $this->scan($userA, $package->tracking_number)
            ->assertSet('errorMessage', 'Esta guía está asignada a otro repartidor.')
            ->assertSet('package', null)
            ->assertDontSee(self::RECIPIENT)
            ->assertDontSee('Av. Bolívar');

        $this->assertSame($driverB->id, $package->fresh()->driver_id);
    }

    public function test_driver_cannot_collect_at_an_agency_that_is_not_a_stop_of_their_route(): void
    {
        [$user, $driver] = $this->deliveryDriver();
        $this->deliveryRoute($driver, $user);

        $package = $this->createPackage($this->createAlly(), [
            'requires_delivery' => true,
            'current_status' => Package::STATUS_RECIBIDO_AGENCIA,
        ]);

        $this->scan($user, $package->tracking_number)
            ->assertSet('package', null)
            ->assertDontSee(self::RECIPIENT);

        $this->assertNull($package->fresh()->driver_id);
    }

    public function test_route_packages_are_listed_in_the_dashboard(): void
    {
        [$user, $driver] = $this->deliveryDriver();
        $route = $this->deliveryRoute($driver, $user);
        $package = $this->assignedToRoute($route, $driver);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->assertSee('Entregas de esta ruta')
            ->assertSee($package->tracking_number)
            ->assertDontSee('Sin ruta asignada');
    }

    /*
    |--------------------------------------------------------------------------
    | Toma por escaneo (claimForDelivery)
    |--------------------------------------------------------------------------
    */

    /**
     * Antes: test_a_ready_package_without_route_can_be_taken_by_scanning
     * validaba que cualquier repartidor tomara un paquete solo con su
     * guía, sin ruta. La regla actual lo prohíbe: sin ruta de reparto
     * en curso no hay relación operativa con el paquete.
     */
    public function test_a_ready_package_cannot_be_taken_without_a_delivery_route(): void
    {
        [$user, $driver] = $this->deliveryDriver();
        $package = $this->readyAtHub($this->valenciaHub());

        $this->assertFalse(Route::where('driver_id', $driver->id)->exists());

        $this->scan($user, $package->tracking_number)
            ->assertSet('errorMessage', 'Necesitas una ruta de reparto en curso para tomar entregas. Toma e inicia una ruta antes de escanear.')
            ->assertSet('package', null)
            ->assertDontSee(self::RECIPIENT);

        $package->refresh();
        $this->assertNull($package->driver_id);
        $this->assertSame(Package::STATUS_LISTO_RETIRO, $package->current_status);
    }

    public function test_a_ready_package_at_the_route_hub_is_taken_by_scanning_and_linked_to_the_route(): void
    {
        [$user, $driver] = $this->deliveryDriver();
        $route = $this->deliveryRoute($driver, $user);
        $package = $this->readyAtHub($this->valenciaHub());

        $this->scan($user, $package->tracking_number)
            ->assertSet('errorMessage', null)
            ->assertSet('lastAction', 'delivery_claimed')
            ->assertSee(self::RECIPIENT);

        $package->refresh();
        $this->assertSame($driver->id, $package->driver_id);
        $this->assertSame(Package::STATUS_EN_TRANSITO_NACIONAL, $package->current_status);

        // Queda ligado a SU ruta: cuenta para completarla/cancelarla.
        $this->assertTrue(
            app(RouteService::class)->packageIdsForRoute($route)->contains($package->id)
        );
    }

    public function test_a_package_still_in_national_transit_cannot_be_taken_even_with_a_route(): void
    {
        [$user, $driver] = $this->deliveryDriver();
        $this->deliveryRoute($driver, $user);
        $hub = $this->valenciaHub();

        // Viajando entre HUBs (custodia ya liberada por scanHubArrival,
        // o despachado sin repartidor): antes era reclamable.
        $package = $this->readyForDelivery([
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
            'destination_warehouse_id' => $hub->id,
        ]);

        $this->scan($user, $package->tracking_number)
            ->assertSet('errorMessage', 'Este paquete todavía no está listo para reparto. Estado actual: En Tránsito.')
            ->assertSet('package', null)
            ->assertDontSee(self::RECIPIENT);

        $package->refresh();
        $this->assertNull($package->driver_id);
        $this->assertSame(Package::STATUS_EN_TRANSITO_NACIONAL, $package->current_status);
    }

    public function test_a_package_released_by_a_cancelled_route_cannot_be_taken_by_another_driver(): void
    {
        [$userA, $driverA] = $this->deliveryDriver();
        [$userB, $driverB] = $this->deliveryDriver();
        $hub = $this->valenciaHub();

        $routeA = $this->deliveryRoute($driverA, $userA);
        $this->deliveryRoute($driverB, $userB);

        $package = $this->readyAtHub($hub);
        $this->scan($userA, $package->tracking_number)->assertSet('errorMessage', null);

        app(RouteService::class)->cancel($routeA, $userA->id);

        $package->refresh();
        $this->assertNull($package->driver_id);
        $this->assertSame(Package::STATUS_EN_TRANSITO_NACIONAL, $package->current_status);

        $this->scan($userB, $package->tracking_number)
            ->assertSet('package', null)
            ->assertNotSet('errorMessage', null)
            ->assertDontSee(self::RECIPIENT);

        $this->assertNull($package->fresh()->driver_id);
    }

    public function test_a_package_of_another_zone_cannot_be_taken(): void
    {
        [$user, $driver] = $this->deliveryDriver();
        $this->valenciaHub();
        $this->deliveryRoute($driver, $user); // Zona: Valencia.

        $maracaiboHub = $this->hubCovering('Zulia', 'Maracaibo');
        $package = $this->readyAtHub($maracaiboHub, [
            'destination_city' => 'Maracaibo',
            'destination_state' => 'Zulia',
        ]);

        $this->scan($user, $package->tracking_number)
            ->assertSet('errorMessage', 'Este paquete no pertenece a la zona de tu ruta de reparto.')
            ->assertSet('package', null)
            ->assertDontSee(self::RECIPIENT);

        $this->assertNull($package->fresh()->driver_id);
    }

    public function test_a_package_not_yet_received_at_its_destination_hub_cannot_be_taken(): void
    {
        [$user, $driver] = $this->deliveryDriver();
        $this->deliveryRoute($driver, $user);
        $valencia = $this->valenciaHub();
        $otherHub = $this->hubCovering('Zulia', 'Maracaibo');

        $cases = [
            // LISTO_RETIRO sin HUB físico registrado.
            $this->readyForDelivery(['current_warehouse_id' => null]),
            // Físicamente en un HUB que no es su destino.
            $this->readyForDelivery([
                'current_warehouse_id' => $otherHub->id,
                'destination_warehouse_id' => $valencia->id,
            ]),
        ];

        foreach ($cases as $package) {
            $this->scan($user, $package->tracking_number)
                ->assertSet('errorMessage', 'Este paquete todavía no fue recibido en su HUB destino. No puede salir a reparto.')
                ->assertSet('package', null);

            $this->assertNull($package->fresh()->driver_id);
        }
    }

    public function test_a_route_that_is_only_assigned_but_not_started_cannot_take_packages(): void
    {
        [$user, $driver] = $this->deliveryDriver();
        $this->deliveryRoute($driver, $user, ['status' => Route::STATUS_ASSIGNED, 'started_at' => null]);
        $package = $this->readyAtHub($this->valenciaHub());

        $this->scan($user, $package->tracking_number)
            ->assertSet('package', null)
            ->assertNotSet('errorMessage', null);

        $this->assertNull($package->fresh()->driver_id);
    }

    public function test_once_taken_by_driver_a_driver_b_cannot_take_it(): void
    {
        [$userA, $driverA] = $this->deliveryDriver();
        [$userB, $driverB] = $this->deliveryDriver();
        $this->deliveryRoute($driverA, $userA);
        $this->deliveryRoute($driverB, $userB);
        $package = $this->readyAtHub($this->valenciaHub());

        $this->scan($userA, $package->tracking_number)->assertSet('errorMessage', null);

        $this->scan($userB, $package->tracking_number)
            ->assertSet('errorMessage', 'Esta guía está asignada a otro repartidor.')
            ->assertSet('package', null)
            ->assertDontSee(self::RECIPIENT);

        $this->assertSame($driverA->id, $package->fresh()->driver_id);
    }

    public function test_a_package_not_available_for_delivery_cannot_be_taken(): void
    {
        [$user] = $this->deliveryDriver();

        $cases = [
            // Todavía en la agencia de origen.
            $this->readyForDelivery(['current_status' => Package::STATUS_RECIBIDO_AGENCIA]),
            // Ya entregado.
            $this->readyForDelivery(['current_status' => Package::STATUS_ENTREGADO]),
            // Retiro en agencia: no requiere domicilio.
            $this->readyForDelivery(['requires_delivery' => false]),
        ];

        foreach ($cases as $package) {
            $this->scan($user, $package->tracking_number)
                ->assertSet('package', null)
                ->assertDontSee(self::RECIPIENT)
                ->assertNotSet('errorMessage', null);

            $this->assertNull($package->fresh()->driver_id);
        }
    }

    /**
     * Antes: test_an_individual_delivery_is_completed_without_any_route.
     * Ahora la entrega tomada por escaneo pertenece a la ruta en curso.
     */
    public function test_a_delivery_taken_by_scanning_on_the_route_is_completed(): void
    {
        [$user, $driver] = $this->deliveryDriver();
        $this->deliveryRoute($driver, $user);
        $package = $this->readyAtHub($this->valenciaHub());

        $this->scan($user, $package->tracking_number)->assertSet('errorMessage', null);

        Livewire::actingAs($user)
            ->test(PackageDetail::class, ['packageId' => $package->id])
            ->assertSee('Confirmar entrega')
            ->set('receiverName', 'María Gómez')
            ->set('receiverIdDoc', 'V-87654321')
            ->set('deliveryConfirmationMethod', 'cedula')
            ->call('completeDelivery');

        $package->refresh();
        $this->assertSame(Package::STATUS_ENTREGADO, $package->current_status);
        $this->assertSame('María Gómez', $package->receiver_name);
        $this->assertSame('cedula', $package->delivery_confirmation_method);
    }

    public function test_delivery_cannot_be_confirmed_from_the_web_without_receiver_data(): void
    {
        [$user, $driver] = $this->deliveryDriver();
        $package = $this->readyForDelivery([
            'driver_id' => $driver->id,
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
        ]);

        Livewire::actingAs($user)
            ->test(PackageDetail::class, ['packageId' => $package->id])
            ->call('completeDelivery')
            ->assertHasErrors(['receiverName', 'receiverIdDoc', 'deliveryConfirmationMethod']);

        $this->assertSame(Package::STATUS_EN_TRANSITO_NACIONAL, $package->fresh()->current_status);
    }

    public function test_dashboard_without_route_offers_individual_scan_but_no_package_list(): void
    {
        [$user] = $this->deliveryDriver();
        $freePackage = $this->readyForDelivery();

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->assertSee('Sin ruta asignada')
            ->assertSee('Escanear guía')
            ->assertDontSee($freePackage->tracking_number)
            ->assertDontSee(self::RECIPIENT);
    }

    public function test_driver_can_report_a_failed_delivery_from_the_web(): void
    {
        [$user, $driver] = $this->deliveryDriver();
        $package = $this->readyForDelivery([
            'driver_id' => $driver->id,
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
        ]);

        Livewire::actingAs($user)
            ->test(PackageDetail::class, ['packageId' => $package->id])
            ->set('showIncidentForm', true)
            ->set('incidentType', 'CLIENTE_AUSENTE')
            ->set('incidentDescription', 'Nadie atendió en la dirección.')
            ->call('reportIncident')
            ->assertHasNoErrors();

        $incident = Incident::where('package_id', $package->id)->firstOrFail();

        $this->assertSame('CLIENTE_AUSENTE', $incident->type);
        $this->assertSame($user->id, $incident->reported_by_user_id);

        // Igual que en la app: el estado del paquete no cambia.
        $this->assertSame(Package::STATUS_EN_TRANSITO_NACIONAL, $package->fresh()->current_status);
    }

    /*
    |--------------------------------------------------------------------------
    | Seguridad
    |--------------------------------------------------------------------------
    */

    public function test_a_non_existent_tracking_number_is_rejected(): void
    {
        [$user] = $this->deliveryDriver();

        $this->scan($user, 'VEN-NO-EXISTE')
            ->assertSet('errorMessage', 'No existe una guía con número: VEN-NO-EXISTE')
            ->assertSet('package', null);
    }

    public function test_a_hub_driver_scan_does_not_expose_data_of_a_package_that_is_not_theirs(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_REPARTIDOR, 'status' => User::STATUS_ACTIVE]);
        Driver::factory()->create([
            'user_id' => $user->id,
            'status' => Driver::STATUS_ACTIVE,
            'verification_status' => Driver::VERIFICATION_VERIFIED,
            'driver_type' => Driver::TYPE_HUB,
        ]);

        $package = $this->readyForDelivery(['current_status' => Package::STATUS_RECIBIDO_AGENCIA]);

        // Sin ruta en curso: antes la vista igual mostraba destinatario,
        // teléfono, dirección y COD de cualquier guía escaneada.
        $this->scan($user, $package->tracking_number)
            ->assertSet('package', null)
            ->assertDontSee(self::RECIPIENT)
            ->assertDontSee('Av. Bolívar');
    }

    public function test_driver_cannot_open_the_detail_of_another_drivers_package(): void
    {
        [$userA] = $this->deliveryDriver();
        [, $driverB] = $this->deliveryDriver();

        $package = $this->readyForDelivery([
            'driver_id' => $driverB->id,
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
        ]);

        $this->actingAs($userA)
            ->get(route('repartidor.package-detail', $package->id))
            ->assertNotFound();
    }

    public function test_api_no_longer_exposes_a_global_list_of_available_packages(): void
    {
        [$user] = $this->deliveryDriver();
        $package = $this->readyForDelivery();

        $this->getJson('/api/driver/deliveries/available', $this->apiHeaders($user))
            ->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertDontSee($package->recipient_id_doc)
            ->assertDontSee($package->recipient_phone);
    }

    public function test_api_claim_by_id_is_rejected_and_does_not_assign_the_package(): void
    {
        [$user] = $this->deliveryDriver();
        $package = $this->readyForDelivery();

        $this->postJson("/api/driver/packages/{$package->id}/claim", [], $this->apiHeaders($user))
            ->assertUnprocessable()
            ->assertJsonMissingPath('package');

        $this->assertNull($package->fresh()->driver_id);
    }

    public function test_api_claim_by_scan_takes_a_delivery_for_the_drivers_route(): void
    {
        [$user, $driver] = $this->deliveryDriver();
        $this->deliveryRoute($driver, $user);
        $package = $this->readyAtHub($this->valenciaHub());

        $this->postJson('/api/driver/deliveries/claim-by-scan', [
            'tracking_number' => $package->tracking_number,
        ], $this->apiHeaders($user))
            ->assertOk()
            ->assertJsonPath('package.recipient.name', self::RECIPIENT)
            ->assertJsonMissingPath('package.recipient.id_doc')
            ->assertJsonMissingPath('package.sender.id_doc')
            ->assertDontSee($package->recipient_id_doc)
            ->assertDontSee($package->sender_id_doc);

        $this->assertSame($driver->id, $package->fresh()->driver_id);
    }

    /**
     * Una petición HTTP manual tampoco puede saltarse la regla: sin ruta
     * de reparto en curso, la API rechaza la toma y no expone nada.
     */
    public function test_api_claim_by_scan_without_route_is_rejected_and_exposes_nothing(): void
    {
        [$user] = $this->deliveryDriver();
        $package = $this->readyAtHub($this->valenciaHub());

        $this->postJson('/api/driver/deliveries/claim-by-scan', [
            'tracking_number' => $package->tracking_number,
        ], $this->apiHeaders($user))
            ->assertUnprocessable()
            ->assertJsonMissingPath('package')
            ->assertDontSee(self::RECIPIENT);

        $this->assertNull($package->fresh()->driver_id);
    }

    public function test_api_claim_by_scan_of_a_package_in_transit_is_rejected(): void
    {
        [$user, $driver] = $this->deliveryDriver();
        $this->deliveryRoute($driver, $user);
        $this->valenciaHub();
        $package = $this->readyForDelivery(['current_status' => Package::STATUS_EN_TRANSITO_NACIONAL]);

        $this->postJson('/api/driver/deliveries/claim-by-scan', [
            'tracking_number' => $package->tracking_number,
        ], $this->apiHeaders($user))
            ->assertUnprocessable()
            ->assertJsonMissingPath('package');

        $this->assertNull($package->fresh()->driver_id);
    }

    public function test_api_claim_by_scan_of_another_drivers_package_exposes_nothing(): void
    {
        [$userA] = $this->deliveryDriver();
        [, $driverB] = $this->deliveryDriver();

        $package = $this->readyForDelivery([
            'driver_id' => $driverB->id,
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
        ]);

        $this->postJson('/api/driver/deliveries/claim-by-scan', [
            'tracking_number' => $package->tracking_number,
        ], $this->apiHeaders($userA))
            ->assertUnprocessable()
            ->assertJsonMissingPath('package')
            ->assertDontSee(self::RECIPIENT);

        $this->assertSame($driverB->id, $package->fresh()->driver_id);
    }

    public function test_api_driver_a_cannot_read_driver_b_package(): void
    {
        [$userA] = $this->deliveryDriver();
        [, $driverB] = $this->deliveryDriver();

        $package = $this->readyForDelivery([
            'driver_id' => $driverB->id,
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
        ]);

        $headers = $this->apiHeaders($userA);

        $this->getJson("/api/driver/packages/{$package->id}", $headers)->assertNotFound();

        $this->getJson('/api/driver/packages/lookup?tracking_number='.$package->tracking_number, $headers)
            ->assertNotFound()
            ->assertDontSee(self::RECIPIENT);
    }

    public function test_api_lookup_of_an_unassigned_package_exposes_nothing(): void
    {
        [$user] = $this->deliveryDriver();
        $package = $this->readyForDelivery();

        $this->getJson('/api/driver/packages/lookup?tracking_number='.$package->tracking_number, $this->apiHeaders($user))
            ->assertNotFound()
            ->assertDontSee(self::RECIPIENT);
    }
}
