<?php

namespace Tests\Feature;

use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Cubre el hueco de QA de la Fase 4: el hash de seguridad HMAC de las
 * guías (Package::computeSecurityHash / verifySecurityHash), usado por
 * el escáner del repartidor y por DriverScanController@verify para
 * detectar si una guía fue alterada después de imprimirse, no tenía
 * ningún test.
 *
 * Nota para quien despliegue a producción: si se rota APP_KEY, TODOS
 * los hashes ya generados dejan de coincidir (ver test de abajo), así
 * que ese es un evento operativo delicado, no solo de seguridad.
 */
class PackageSecurityHashTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestPackages;

    public function test_hash_is_valid_right_after_creation(): void
    {
        $ally = $this->createAlly();
        $package = $this->createPackage($ally);

        $package->forceFill([
            'security_hash' => Package::computeSecurityHash(
                $package->tracking_number,
                (int) $package->ally_id,
                (float) $package->physical_weight_kg,
                $package->created_at,
            ),
        ])->save();

        $this->assertTrue($package->fresh()->verifySecurityHash());
    }

    public function test_hash_becomes_invalid_if_weight_is_altered_after_printing(): void
    {
        $ally = $this->createAlly();
        $package = $this->createPackage($ally, ['physical_weight_kg' => 2.0]);

        $package->forceFill([
            'security_hash' => Package::computeSecurityHash(
                $package->tracking_number,
                (int) $package->ally_id,
                (float) $package->physical_weight_kg,
                $package->created_at,
            ),
        ])->save();

        // Alguien cambia el peso en el sistema después de que la
        // etiqueta ya se imprimió y se pegó al paquete físico.
        $package->update(['physical_weight_kg' => 9.0]);

        $this->assertFalse($package->fresh()->verifySecurityHash());
    }

    public function test_package_without_hash_is_never_considered_valid(): void
    {
        $ally = $this->createAlly();
        $package = $this->createPackage($ally, ['security_hash' => null]);

        $this->assertFalse($package->verifySecurityHash());
    }

    public function test_hash_is_stable_for_the_same_inputs(): void
    {
        $ally = $this->createAlly();
        $package = $this->createPackage($ally);

        $first = Package::computeSecurityHash(
            $package->tracking_number,
            (int) $package->ally_id,
            (float) $package->physical_weight_kg,
            $package->created_at,
        );

        $second = Package::computeSecurityHash(
            $package->tracking_number,
            (int) $package->ally_id,
            (float) $package->physical_weight_kg,
            $package->created_at,
        );

        $this->assertSame($first, $second);
    }

    public function test_verify_endpoint_flags_a_tampered_package(): void
    {
        $ally = $this->createAlly();
        $package = $this->createPackage($ally, ['physical_weight_kg' => 2.0]);

        $package->forceFill([
            'security_hash' => Package::computeSecurityHash(
                $package->tracking_number,
                (int) $package->ally_id,
                (float) $package->physical_weight_kg,
                $package->created_at,
            ),
        ])->save();

        $package->update(['physical_weight_kg' => 9.0]);

        $driverUser = \App\Models\User::factory()->create([
            'role' => \App\Models\User::ROLE_REPARTIDOR,
            'status' => \App\Models\User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        \App\Models\Driver::factory()->create([
            'user_id' => $driverUser->id,
            'status' => \App\Models\Driver::STATUS_ACTIVE,
        ]);

        $this->actingAs($driverUser)
            ->postJson('/repartidor/verificar-guia', [
                'tracking_number' => $package->tracking_number,
            ])
            ->assertOk()
            ->assertJsonPath('valid', false);
    }
}
