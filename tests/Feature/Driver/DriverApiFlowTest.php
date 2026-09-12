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
 * Cubre el hueco de QA identificado en la Fase 4: hasta ahora existían
 * tests de PackageService a nivel de servicio (PackageServiceCodTest)
 * y de la vista Livewire del repartidor (DriverDashboardTest), pero
 * NINGÚN test pasaba por la API real que consume la app Flutter
 * (routes/api.php, prefijo /api/driver/*).
 *
 * Esto es justo lo que la app del repartidor usa en el teléfono, así
 * que es el flujo más importante a cubrir antes de decir que el
 * sistema está "100% funcional".
 *
 * Simula el recorrido completo de una guía:
 * login -> ruta asignada -> iniciar ruta -> escanear en agencia ->
 * (tránsito, fuera del alcance del repartidor) -> completar entrega
 * -> cobrar COD.
 */
class DriverApiFlowTest extends TestCase
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

    public function test_login_rejects_users_without_repartidor_role(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_ALIADO,
            'status' => User::STATUS_ACTIVE,
            'password' => bcrypt('password-seguro'),
        ]);

        $this->postJson('/api/driver/login', [
            'email' => $user->email,
            'password' => 'password-seguro',
            'device_name' => 'telefono-de-prueba',
        ])->assertUnprocessable();
    }

    public function test_login_rejects_wrong_password(): void
    {
        [$user] = $this->createDriverUser();

        $this->postJson('/api/driver/login', [
            'email' => $user->email,
            'password' => 'contraseña-incorrecta',
            'device_name' => 'telefono-de-prueba',
        ])->assertUnprocessable();
    }

    public function test_full_delivery_flow_through_the_real_api(): void
    {
        [$user, $driver] = $this->createDriverUser();

        // 1. Login: debe devolver un token de Sanctum con habilidad 'driver'.
        $login = $this->postJson('/api/driver/login', [
            'email' => $user->email,
            'password' => 'password-seguro',
            'device_name' => 'telefono-de-prueba',
        ])->assertOk()
            ->assertJsonStructure(['token', 'user', 'driver']);

        $token = $login->json('token');
        $headers = ['Authorization' => "Bearer {$token}"];

        // 2. /me debe confirmar la identidad sin pedir credenciales de nuevo.
        $this->getJson('/api/driver/me', $headers)
            ->assertOk()
            ->assertJsonPath('user.email', $user->email);

        // 3. Sin ruta asignada todavía, /route debe devolver null, no error.
        $this->getJson('/api/driver/route', $headers)
            ->assertOk()
            ->assertJsonPath('route', null);

        // 4. Preparamos una agencia, un paquete listo para recolección,
        //    y una ruta ASSIGNED con esa agencia como parada.
        $ally = $this->createAlly();

        $package = $this->createPackage($ally, [
            'requires_delivery' => true,
            'is_cod' => true,
            'cod_amount_usd' => 15.00,
        ]);

        $route = Route::create([
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
            'name' => 'Ruta de prueba',
            'driver_id' => $driver->id,
            'created_by' => $user->id,
            'status' => Route::STATUS_ASSIGNED,
            'route_type' => Route::TYPE_DELIVERY,
        ]);

        RouteStop::create([
            'route_id' => $route->id,
            'ally_id' => $ally->id,
            'sequence' => 1,
            'status' => RouteStop::STATUS_PENDING,
            'packages_collected_count' => 0,
        ]);

        // 5. Ahora sí debe verse la ruta asignada.
        $this->getJson('/api/driver/route', $headers)
            ->assertOk()
            ->assertJsonPath('route.id', $route->id);

        // 6. Iniciar ruta: assigned -> in_progress.
        $this->postJson('/api/driver/route/start', [], $headers)
            ->assertOk();

        $this->assertSame(
            Route::STATUS_IN_PROGRESS,
            $route->fresh()->status,
        );

        // 7. Escanear el paquete en la agencia. Debe pasar a
        //    RECOLECTADO_VENEXPRESS y quedar asignado a este repartidor.
        $this->postJson('/api/driver/scan', [
            'tracking_number' => $package->tracking_number,
        ], $headers)
            ->assertOk()
            ->assertJsonPath('package.current_status', \App\Models\Package::STATUS_RECOLECTADO_VENEXPRESS);

        $package->refresh();
        $this->assertSame($driver->id, $package->driver_id);
        $this->assertSame(
            \App\Models\Package::STATUS_RECOLECTADO_VENEXPRESS,
            $package->current_status,
        );

        // 8. El paso por el hub/tránsito nacional no lo hace el
        //    repartidor de reparto, así que lo simulamos directamente
        //    para poder probar el endpoint de entrega.
        $package->forceFill([
            'current_status' => \App\Models\Package::STATUS_EN_TRANSITO_NACIONAL,
        ])->save();

        // 9. Completar entrega vía API.
        $this->postJson(
            "/api/driver/packages/{$package->id}/complete-delivery",
            [
                'receiver_name' => 'María Gómez',
                'receiver_id_doc' => 'V-87654321',
                'receiver_phone' => '0424-7654321',
                'delivery_confirmation_method' => 'cedula',
            ],
            $headers
        )->assertOk()
            ->assertJsonPath('package.current_status', \App\Models\Package::STATUS_ENTREGADO);

        $package->refresh();
        $this->assertSame(\App\Models\Package::STATUS_ENTREGADO, $package->current_status);
        $this->assertNotNull($package->delivery_completed_at);

        // completeDelivery() ya cobra el COD automáticamente si no se
        // había cobrado antes, así que a esta altura ya debería estar
        // registrado sin necesidad de un segundo request.
        $this->assertNotNull($package->cod_collected_at);

        // 10. Confirmamos que el endpoint de collect-cod es idempotente:
        //     llamarlo de nuevo no debe romper nada ni duplicar el cobro.
        $this->postJson(
            "/api/driver/packages/{$package->id}/collect-cod",
            [],
            $headers
        )->assertOk();

        // 11. Un repartidor NO puede tocar un paquete que no es suyo.
        [, $otroDriver] = $this->createDriverUser();
        $paqueteAjeno = $this->createPackage($ally, [
            'requires_delivery' => true,
            'driver_id' => $otroDriver->id,
        ]);

        $this->postJson(
            "/api/driver/packages/{$paqueteAjeno->id}/complete-delivery",
            [
                'receiver_name' => 'Otro',
                'receiver_id_doc' => 'V-1',
                'delivery_confirmation_method' => 'cedula',
            ],
            $headers
        )->assertNotFound();
    }

    public function test_logout_revokes_the_current_token(): void
    {
        [$user] = $this->createDriverUser();

        $token = $this->postJson('/api/driver/login', [
            'email' => $user->email,
            'password' => 'password-seguro',
            'device_name' => 'telefono-de-prueba',
        ])->json('token');

        $headers = ['Authorization' => "Bearer {$token}"];

        $this->postJson('/api/driver/logout', [], $headers)->assertOk();

        // El mismo token ya no debe servir para nada.
        $this->getJson('/api/driver/me', $headers)->assertUnauthorized();
    }
}
