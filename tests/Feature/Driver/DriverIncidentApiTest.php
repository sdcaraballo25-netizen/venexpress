<?php

namespace Tests\Feature\Driver;

use App\Models\Driver;
use App\Models\Incident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * DriverIncidentController::store() creaba la incidencia sin
 * verificar driver->status, a diferencia de toda otra acción que
 * muta datos (RouteService, LogisticsScanService, PackageService),
 * que sí repiten ese chequeo. Un repartidor suspendido/rechazado
 * podía seguir reportando incidencias mientras su token siguiera
 * vigente.
 */
class DriverIncidentApiTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestPackages;

    private function createDriverUser(string $status = Driver::STATUS_ACTIVE): array
    {
        $user = User::factory()->create([
            'role' => User::ROLE_REPARTIDOR,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
            'password' => bcrypt('password-seguro'),
        ]);

        $driver = Driver::factory()->create([
            'user_id' => $user->id,
            'status' => $status,
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

    public function test_an_active_driver_can_report_an_incident(): void
    {
        [$user, $driver] = $this->createDriverUser();
        $ally = $this->createAlly();
        $package = $this->createPackage($ally, ['driver_id' => $driver->id]);

        $this->postJson("/api/driver/packages/{$package->id}/incidents", [
            'type' => 'CLIENTE_AUSENTE',
            'description' => 'Nadie respondió en la dirección.',
        ], $this->authHeaders($user))->assertCreated();

        $this->assertDatabaseHas('incidents', [
            'package_id' => $package->id,
            'type' => 'CLIENTE_AUSENTE',
        ]);
    }

    public function test_a_driver_suspended_after_login_cannot_report_an_incident(): void
    {
        // El login ya bloquea a un repartidor suspendido/rechazado
        // (ver DriverAuthController::login), así que para probar este
        // chequeo hay que simular el caso real: el token se emitió
        // mientras estaba activo y lo suspendieron después, sin que
        // ese token se revocara.
        [$user, $driver] = $this->createDriverUser();
        $headers = $this->authHeaders($user);

        $driver->update(['status' => Driver::STATUS_SUSPENDED]);

        $ally = $this->createAlly();
        $package = $this->createPackage($ally, ['driver_id' => $driver->id]);

        $this->postJson("/api/driver/packages/{$package->id}/incidents", [
            'type' => 'CLIENTE_AUSENTE',
            'description' => 'Nadie respondió en la dirección.',
        ], $headers)->assertForbidden();

        $this->assertSame(0, Incident::where('package_id', $package->id)->count());
    }
}
