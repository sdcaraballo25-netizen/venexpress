<?php

namespace Tests\Feature\Driver;

use App\Models\Driver;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * DriverPackageController::lookup()/scan()/hubReception() resolvían
 * el paquete solo por tracking_number, sin verificar que le tocara al
 * repartidor autenticado (ni por ruta activa ni por ya ser suyo).
 * Como el número de guía es adivinable/enumerable, cualquier
 * repartidor podía leer nombre, cédula y teléfono del remitente y
 * destinatario, y el monto COD, de guías de otras agencias/rutas.
 */
class DriverPackagePiiExposureTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestPackages;

    private function createDriverUser(): array
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
        ]);

        return [$user, $driver];
    }

    private function authHeaders(User $user): array
    {
        $token = $this->postJson('/api/driver/login', [
            'email' => $user->email,
            'password' => 'password-seguro',
            'device_name' => 'telefono-de-prueba',
        ])->assertOk()->json('token');

        return ['Authorization' => "Bearer {$token}"];
    }

    public function test_lookup_does_not_disclose_a_package_outside_the_drivers_route(): void
    {
        [$user, $driver] = $this->createDriverUser();
        $headers = $this->authHeaders($user);

        // El repartidor tiene una ruta activa, pero para OTRA agencia
        // distinta a la del paquete que va a intentar consultar.
        $myAlly = $this->createAlly();
        $route = Route::create([
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
            'name' => 'Ruta de prueba',
            'driver_id' => $driver->id,
            'created_by' => $user->id,
            'status' => Route::STATUS_IN_PROGRESS,
            'route_type' => Route::TYPE_DELIVERY,
            'started_at' => now(),
        ]);
        RouteStop::create([
            'route_id' => $route->id,
            'ally_id' => $myAlly->id,
            'sequence' => 1,
            'status' => RouteStop::STATUS_PENDING,
        ]);

        $otherAlly = $this->createAlly();
        $foreignPackage = $this->createPackage($otherAlly, [
            'sender_name' => 'Remitente Secreto',
            'recipient_name' => 'Destinatario Secreto',
            'recipient_phone' => '0424-0000000',
            'is_cod' => true,
            'cod_amount_usd' => 250.00,
        ]);

        $response = $this->getJson(
            '/api/driver/packages/lookup?tracking_number=' . $foreignPackage->tracking_number,
            $headers
        );

        $response->assertNotFound();
        $response->assertJsonMissing(['package' => []]);
        $this->assertStringNotContainsString('Remitente Secreto', $response->getContent());
        $this->assertStringNotContainsString('Destinatario Secreto', $response->getContent());
        $this->assertStringNotContainsString('250', $response->getContent());
    }

    public function test_scan_error_response_does_not_disclose_a_package_outside_the_drivers_route(): void
    {
        [$user, $driver] = $this->createDriverUser();
        $headers = $this->authHeaders($user);

        // Sin ninguna ruta activa: el escaneo va a fallar, pero antes
        // no debía filtrar los datos del paquete en la respuesta 422.
        $otherAlly = $this->createAlly();
        $foreignPackage = $this->createPackage($otherAlly, [
            'sender_name' => 'Remitente Secreto',
            'recipient_name' => 'Destinatario Secreto',
            'is_cod' => true,
            'cod_amount_usd' => 250.00,
        ]);

        $response = $this->postJson('/api/driver/scan', [
            'tracking_number' => $foreignPackage->tracking_number,
        ], $headers);

        $response->assertStatus(422);
        $this->assertStringNotContainsString('Remitente Secreto', $response->getContent());
        $this->assertStringNotContainsString('Destinatario Secreto', $response->getContent());
        $this->assertStringNotContainsString('250', $response->getContent());
    }
}
