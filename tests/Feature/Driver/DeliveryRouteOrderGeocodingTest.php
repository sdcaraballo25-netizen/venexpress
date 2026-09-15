<?php

namespace Tests\Feature\Driver;

use App\Jobs\GeocodePackageDeliveryAddress;
use App\Models\Driver;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Antes, /api/driver/deliveries/route-order solo ENCOLABA la
 * geocodificación de un paquete sin coordenadas y lo excluía de la
 * respuesta — así que la primera vez que un repartidor pedía su ruta,
 * SIEMPRE salía vacía, y solo se completaba si un worker de colas
 * (fácil de olvidar en desarrollo local) llegaba a procesar el job.
 *
 * Ahora GeocodingService::geocodePackageDeliveryAddress() se llama de
 * forma síncrona dentro de la misma petición, así que el repartidor
 * ve su ruta calculada de una vez, sin depender de ningún worker. El
 * job en cola queda solo como respaldo para cuando Nominatim falla o
 * no encuentra la dirección.
 */
class DeliveryRouteOrderGeocodingTest extends TestCase
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

    public function test_route_order_geocodes_synchronously_when_package_has_no_coordinates_yet(): void
    {
        Http::fake([
            'nominatim.openstreetmap.org/*' => Http::response([
                ['lat' => '10.1621', 'lon' => '-67.9942'],
            ], 200),
        ]);
        Queue::fake();

        [$user, $driver] = $this->createDeliveryDriverUser();
        $ally = $this->createAlly();

        $package = $this->createPackage($ally, [
            'requires_delivery' => true,
            'driver_id' => $driver->id,
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
            'destination_city' => 'Cumaná',
            'delivery_address' => 'Av. Bermúdez',
        ]);

        $this->getJson(
            '/api/driver/deliveries/route-order?latitude=10.4806&longitude=-66.9036',
            $this->authHeaders($user)
        )->assertOk()
            ->assertJsonCount(1, 'stops')
            ->assertJsonPath('pending_location', []);

        $package->refresh();
        $this->assertNotNull($package->delivery_latitude);
        $this->assertNotNull($package->delivery_longitude);
        $this->assertNotNull($package->delivery_geocoded_at);

        // Se resolvió en vivo: no debería haber quedado nada encolado.
        Queue::assertNothingPushed();
    }

    public function test_route_order_falls_back_to_sector_level_when_full_address_is_not_found(): void
    {
        // Caso real reportado: Nominatim no tiene "terrazas cumanesas
        // torre B" (el nombre del edificio/torre lo hace fallar como
        // texto libre), pero SÍ conoce "calle bolivar" como calle de
        // Cumaná. Caer directo a nivel de ciudad aquí sería peor de lo
        // necesario: dos pedidos en calles distintas de la misma
        // ciudad quedarían con la MISMA coordenada (y la misma
        // distancia), perdiendo cualquier orden real entre ellos.
        Http::fake(function ($request) {
            $url = $request->url();

            if (str_contains($url, 'torre+B') || str_contains($url, 'torre%20B')) {
                return Http::response([], 200);
            }

            return Http::response([
                ['lat' => '10.4715248', 'lon' => '-64.1546614'],
            ], 200);
        });
        Queue::fake();

        [$user, $driver] = $this->createDeliveryDriverUser();
        $ally = $this->createAlly();

        $package = $this->createPackage($ally, [
            'requires_delivery' => true,
            'driver_id' => $driver->id,
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
            'destination_city' => 'Cumaná',
            'destination_state' => 'Sucre',
            'delivery_address' => 'terrazas cumanesas torre B',
            'delivery_sector' => 'calle bolivar',
        ]);

        $this->getJson(
            '/api/driver/deliveries/route-order?latitude=10.4806&longitude=-66.9036',
            $this->authHeaders($user)
        )->assertOk()
            ->assertJsonCount(1, 'stops')
            ->assertJsonPath('pending_location', []);

        $package->refresh();
        $this->assertEquals(10.4715248, (float) $package->delivery_latitude);
        $this->assertEquals(-64.1546614, (float) $package->delivery_longitude);

        Queue::assertNothingPushed();
    }

    public function test_route_order_falls_back_to_city_level_when_full_address_and_sector_are_not_found(): void
    {
        Http::fake(function ($request) {
            $url = $request->url();

            // Ni la dirección completa ni el sector ("super bloques")
            // aparecen en Nominatim; solo la ciudad.
            if (str_contains($url, 'super') || str_contains($url, 'bloques')) {
                return Http::response([], 200);
            }

            return Http::response([
                ['lat' => '10.4495706', 'lon' => '-64.1578046'],
            ], 200);
        });
        Queue::fake();

        [$user, $driver] = $this->createDeliveryDriverUser();
        $ally = $this->createAlly();

        $package = $this->createPackage($ally, [
            'requires_delivery' => true,
            'driver_id' => $driver->id,
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
            'destination_city' => 'Cumaná',
            'destination_state' => 'Sucre',
            'delivery_address' => 'super bloques',
            'delivery_sector' => 'super bloques',
        ]);

        $this->getJson(
            '/api/driver/deliveries/route-order?latitude=10.4806&longitude=-66.9036',
            $this->authHeaders($user)
        )->assertOk()
            ->assertJsonCount(1, 'stops')
            ->assertJsonPath('pending_location', []);

        $package->refresh();
        $this->assertEquals(10.4495706, (float) $package->delivery_latitude);
        $this->assertEquals(-64.1578046, (float) $package->delivery_longitude);

        Queue::assertNothingPushed();
    }

    public function test_route_order_falls_back_to_queue_when_nominatim_finds_nothing(): void
    {
        Http::fake([
            'nominatim.openstreetmap.org/*' => Http::response([], 200),
        ]);
        Queue::fake();

        [$user, $driver] = $this->createDeliveryDriverUser();
        $ally = $this->createAlly();

        $package = $this->createPackage($ally, [
            'requires_delivery' => true,
            'driver_id' => $driver->id,
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
            'delivery_address' => 'Dirección inexistente xyz',
        ]);

        $this->getJson(
            '/api/driver/deliveries/route-order?latitude=10.4806&longitude=-66.9036',
            $this->authHeaders($user)
        )->assertOk()
            ->assertJsonCount(0, 'stops')
            ->assertJsonPath('pending_location', [$package->tracking_number]);

        $package->refresh();
        $this->assertNull($package->delivery_latitude);

        Queue::assertPushed(GeocodePackageDeliveryAddress::class);
    }
}
