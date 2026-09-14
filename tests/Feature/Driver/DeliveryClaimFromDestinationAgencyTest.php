<?php

namespace Tests\Feature\Driver;

use App\Models\Driver;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Un repartidor de entrega debe poder autoasignarse (escaneando) un
 * paquete que ya fue recibido en la agencia destino (LISTO_RETIRO),
 * sin depender de que un admin lo asigne primero desde
 * "Asignar Repartidor". Antes solo se podía reclamar en
 * EN_TRANSITO_NACIONAL, así que cualquier paquete que la agencia ya
 * hubiera recibido en mostrador (Ally\PackageReception, que no
 * distingue si requiere entrega a domicilio) quedaba inalcanzable
 * para "Escanear para Reclamar" en la app.
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

    public function test_driver_can_claim_by_scan_a_package_already_received_at_destination_agency(): void
    {
        [$user] = $this->createDeliveryDriverUser();
        $ally = $this->createAlly();

        $package = $this->createPackage($ally, [
            'requires_delivery' => true,
            'current_status' => Package::STATUS_LISTO_RETIRO,
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

    public function test_available_deliveries_list_includes_packages_at_destination_agency(): void
    {
        [$user] = $this->createDeliveryDriverUser();
        $ally = $this->createAlly();

        $this->createPackage($ally, [
            'requires_delivery' => true,
            'current_status' => Package::STATUS_LISTO_RETIRO,
        ]);

        $this->getJson('/api/driver/deliveries/available', $this->authHeaders($user))
            ->assertOk()
            ->assertJsonCount(1, 'data');
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
