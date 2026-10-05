<?php

namespace Tests\Feature\Driver;

use App\Models\Driver;
use App\Models\Package;
use App\Models\Route;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseCoverage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Un repartidor de entrega puede tomar (escaneando) un paquete que ya
 * fue recibido en su HUB destino (LISTO_RETIRO), sin depender de que un
 * admin lo asigne primero desde "Asignar Repartidor" — pero solo con
 * una ruta de reparto en curso en la zona de ese HUB
 * (PackageService::claimForDelivery()).
 */
class DeliveryClaimFromDestinationAgencyTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestPackages;

    private function createDeliveryDriverUser(): array
    {
        $user = User::factory()->create([
            'role' => User::ROLE_REPARTIDOR,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
            'password' => bcrypt('password-seguro'),
        ]);

        $driver = Driver::factory()->create([
            'user_id' => $user->id,
            'status' => Driver::STATUS_ACTIVE,
            'driver_type' => Driver::TYPE_DELIVERY,
        ]);

        return [$user, $driver];
    }

    private function authHeaders(User $user): array
    {
        $token = $this->postJson('/api/driver/login', [
            'email' => $user->email,
            'password' => 'password-seguro',
            'device_name' => 'telefono-de-prueba',
        ])->json('token');

        return ['Authorization' => "Bearer {$token}"];
    }

    private function valenciaHub(): Warehouse
    {
        $hub = Warehouse::factory()->create(['state' => 'Carabobo', 'city' => 'Valencia']);

        WarehouseCoverage::create([
            'warehouse_id' => $hub->id,
            'state' => 'Carabobo',
            'city' => 'Valencia',
            'is_active' => true,
        ]);

        return $hub;
    }

    private function startDeliveryRoute(User $user, Driver $driver): Route
    {
        return Route::create([
            'city' => 'Valencia',
            'state' => 'Carabobo',
            'name' => 'Reparto Valencia',
            'driver_id' => $driver->id,
            'created_by' => $user->id,
            'status' => Route::STATUS_IN_PROGRESS,
            'started_at' => now(),
            'route_type' => Route::TYPE_DELIVERY,
        ]);
    }

    /**
     * Antes este test validaba que bastaba escanear un paquete
     * LISTO_RETIRO para tomarlo, sin ruta ni HUB. La regla vigente
     * exige ruta de reparto en curso y que el paquete esté en el HUB
     * destino de la zona de esa ruta; con eso sí se puede tomar y
     * entregar de punta a punta.
     */
    public function test_driver_with_a_route_can_claim_by_scan_a_package_received_at_its_destination_hub(): void
    {
        [$user, $driver] = $this->createDeliveryDriverUser();
        $ally = $this->createAlly();
        $hub = $this->valenciaHub();
        $this->startDeliveryRoute($user, $driver);

        $package = $this->createPackage($ally, [
            'requires_delivery' => true,
            'current_status' => Package::STATUS_LISTO_RETIRO,
            'current_warehouse_id' => $hub->id,
            'destination_warehouse_id' => $hub->id,
        ]);

        $headers = $this->authHeaders($user);

        $this->postJson('/api/driver/deliveries/claim-by-scan', [
            'tracking_number' => $package->tracking_number,
        ], $headers)
            ->assertOk()
            ->assertJsonPath('package.current_status', Package::STATUS_EN_TRANSITO_NACIONAL);

        $package->refresh();
        $this->assertSame(Package::STATUS_EN_TRANSITO_NACIONAL, $package->current_status);
        $this->assertNotNull($package->driver_id);

        // Y ya puede completar la entrega normalmente.
        $this->postJson(
            "/api/driver/packages/{$package->id}/complete-delivery",
            [
                'receiver_name' => 'María Gómez',
                'receiver_id_doc' => 'V-87654321',
                'delivery_confirmation_method' => 'cedula',
            ],
            $headers
        )->assertOk()
            ->assertJsonPath('package.current_status', Package::STATUS_ENTREGADO);
    }

    /**
     * Negativo de la regla anterior: un paquete LISTO_RETIRO no se puede
     * tomar solo por conocer su guía, sin ruta de reparto en curso.
     */
    public function test_driver_without_a_route_cannot_claim_a_package_received_at_destination(): void
    {
        [$user] = $this->createDeliveryDriverUser();
        $hub = $this->valenciaHub();

        $package = $this->createPackage($this->createAlly(), [
            'requires_delivery' => true,
            'current_status' => Package::STATUS_LISTO_RETIRO,
            'current_warehouse_id' => $hub->id,
            'destination_warehouse_id' => $hub->id,
        ]);

        $this->postJson('/api/driver/deliveries/claim-by-scan', [
            'tracking_number' => $package->tracking_number,
        ], $this->authHeaders($user))
            ->assertUnprocessable()
            ->assertJsonMissingPath('package');

        $package->refresh();
        $this->assertNull($package->driver_id);
        $this->assertSame(Package::STATUS_LISTO_RETIRO, $package->current_status);
    }

    /**
     * Ya no existe una lista global de paquetes para elegir: el
     * endpoint se conserva (mismo formato, para versiones anteriores de
     * la app) pero nunca devuelve paquetes ni sus datos personales.
     * Tomar una entrega se hace escaneando la guía (claim-by-scan).
     */
    public function test_available_deliveries_endpoint_no_longer_lists_packages(): void
    {
        [$user] = $this->createDeliveryDriverUser();
        $ally = $this->createAlly();

        $package = $this->createPackage($ally, [
            'requires_delivery' => true,
            'current_status' => Package::STATUS_LISTO_RETIRO,
        ]);

        $this->getJson('/api/driver/deliveries/available', $this->authHeaders($user))
            ->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('meta.total', 0)
            ->assertDontSee($package->tracking_number)
            ->assertDontSee($package->recipient_name);
    }

    public function test_still_cannot_claim_an_already_delivered_package(): void
    {
        [$user] = $this->createDeliveryDriverUser();
        $ally = $this->createAlly();

        $package = $this->createPackage($ally, [
            'requires_delivery' => true,
            'current_status' => Package::STATUS_ENTREGADO,
        ]);

        $this->postJson('/api/driver/deliveries/claim-by-scan', [
            'tracking_number' => $package->tracking_number,
        ], $this->authHeaders($user))
            ->assertUnprocessable()
            ->assertJsonFragment(['message' => 'Este paquete todavía no está listo para reparto. Estado actual: Entregado.']);
    }
}
