<?php

namespace Tests\Feature\Driver;

use App\Models\Ally;
use App\Models\Driver;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Cubre vía la API real (routes/api.php) los endpoints que la app
 * Flutter necesita para el repartidor de HUB Recolección (driver_type
 * = hub, ruta hub_transfer) y que hasta ahora solo existían para el
 * portal web (Livewire\Driver\Scanner):
 *
 * - GET  /api/driver/packages/lookup    (identificar sin ejecutar nada)
 * - POST /api/driver/scan/hub-reception (segunda mitad de "Aliado -> HUB")
 *
 * /api/driver/scan (scanCollection) ya tenía cobertura de API en
 * DriverApiFlowTest, así que aquí solo se reutiliza como primer paso.
 *
 * Fase 5A — la recepción en HUB dejó de ser una operación que el
 * Driver pueda confirmar: LogisticsScanService::scanHubReception()
 * ahora siempre rechaza (ver ese método), y Admin la registra desde
 * HubReceptionService. El endpoint /api/driver/scan/hub-reception
 * sigue existiendo (no se elimina en esta fase), pero solo puede
 * devolver el error explicado — nunca completar la recepción.
 */
class DriverHubTransferApiTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestPackages;

    private function createHubDriverUser(): array
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
            'driver_type' => Driver::TYPE_HUB,
        ]);

        return [$user, $driver];
    }

    public function test_hub_driver_collects_but_hub_reception_via_api_is_rejected(): void
    {
        [$user, $driver] = $this->createHubDriverUser();

        $token = $this->postJson('/api/driver/login', [
            'email' => $user->email,
            'password' => 'password-seguro',
            'device_name' => 'telefono-de-prueba',
        ])->assertOk()->json('token');

        $headers = ['Authorization' => "Bearer {$token}"];

        $ally = $this->createAlly();

        $route = Route::create([
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
            'name' => 'Recolección Caracas',
            'driver_id' => $driver->id,
            'created_by' => $user->id,
            'status' => Route::STATUS_ASSIGNED,
            'route_type' => Route::TYPE_HUB_TRANSFER,
        ]);

        RouteStop::create([
            'route_id' => $route->id,
            'ally_id' => $ally->id,
            'sequence' => 1,
            'status' => RouteStop::STATUS_PENDING,
        ]);

        $this->postJson('/api/driver/route/start', [], $headers)->assertOk();

        $package = $this->createPackage($ally, [
            'current_status' => \App\Models\Package::STATUS_RECIBIDO_AGENCIA,
        ]);

        // 1. Identificar la guía antes de escanear: debe verse
        //    RECIBIDO_AGENCIA (operación esperada del lado Flutter: 'collection').
        $this->getJson(
            '/api/driver/packages/lookup?tracking_number=' . $package->tracking_number,
            $headers
        )->assertOk()
            ->assertJsonPath('package.current_status', \App\Models\Package::STATUS_RECIBIDO_AGENCIA);

        // 2. Confirmar recolección.
        $this->postJson('/api/driver/scan', [
            'tracking_number' => $package->tracking_number,
        ], $headers)
            ->assertOk()
            ->assertJsonPath('package.current_status', \App\Models\Package::STATUS_RECOLECTADO_VENEXPRESS);

        // 3. Identificar de nuevo: ahora debe verse RECOLECTADO_VENEXPRESS
        //    (operación esperada: 'hub_reception').
        $this->getJson(
            '/api/driver/packages/lookup?tracking_number=' . $package->tracking_number,
            $headers
        )->assertOk()
            ->assertJsonPath('package.current_status', \App\Models\Package::STATUS_RECOLECTADO_VENEXPRESS);

        // 4. Fase 5A: la recepción en HUB vía Driver está bloqueada.
        //    El endpoint responde 422 con el mensaje explicando que
        //    ahora se confirma desde Admin, y el paquete no avanza.
        $this->postJson('/api/driver/scan/hub-reception', [
            'tracking_number' => $package->tracking_number,
        ], $headers)
            ->assertUnprocessable()
            ->assertJsonPath(
                'message',
                'La recepción de paquetes en HUB ahora se confirma desde la operación interna de HUB '
                .'en el panel de Admin. Los repartidores ya no pueden confirmarla desde aquí.'
            )
            ->assertJsonPath('package.current_status', \App\Models\Package::STATUS_RECOLECTADO_VENEXPRESS);

        $package->refresh();
        $this->assertSame(\App\Models\Package::STATUS_RECOLECTADO_VENEXPRESS, $package->current_status);
        $this->assertSame($driver->id, $package->driver_id);
    }

    public function test_lookup_returns_404_for_unknown_tracking_number(): void
    {
        [$user] = $this->createHubDriverUser();

        $token = $this->postJson('/api/driver/login', [
            'email' => $user->email,
            'password' => 'password-seguro',
            'device_name' => 'telefono-de-prueba',
        ])->json('token');

        $headers = ['Authorization' => "Bearer {$token}"];

        $this->getJson('/api/driver/packages/lookup?tracking_number=NO-EXISTE', $headers)
            ->assertNotFound();
    }

    public function test_hub_reception_rejects_a_delivery_driver(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_REPARTIDOR,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
            'password' => bcrypt('password-seguro'),
        ]);

        Driver::factory()->create([
            'user_id' => $user->id,
            'status' => Driver::STATUS_ACTIVE,
            'driver_type' => Driver::TYPE_DELIVERY,
        ]);

        $token = $this->postJson('/api/driver/login', [
            'email' => $user->email,
            'password' => 'password-seguro',
            'device_name' => 'telefono-de-prueba',
        ])->json('token');

        $headers = ['Authorization' => "Bearer {$token}"];

        $ally = $this->createAlly();
        $package = $this->createPackage($ally, [
            'current_status' => \App\Models\Package::STATUS_RECOLECTADO_VENEXPRESS,
        ]);

        $this->postJson('/api/driver/scan/hub-reception', [
            'tracking_number' => $package->tracking_number,
        ], $headers)->assertUnprocessable();
    }
}
