<?php

namespace Tests\Feature\Driver;

use App\Models\Driver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fase 2: DriverAuthController::login ahora exige Driver::canOperate()
 * (verification_status === VERIFICADO Y status === ACTIVO), no
 * solamente status === ACTIVO. Un repartidor con status ACTIVO pero
 * todavía no verificado documentalmente no debe poder obtener un
 * token operativo de la app.
 */
class DriverLoginVerificationTest extends TestCase
{
    use RefreshDatabase;

    private function attemptLogin(User $user): \Illuminate\Testing\TestResponse
    {
        return $this->postJson('/api/driver/login', [
            'email' => $user->email,
            'password' => 'password-seguro',
            'device_name' => 'telefono-de-prueba',
        ]);
    }

    private function createDriverUser(string $status, string $verificationStatus): User
    {
        $user = User::factory()->create([
            'role' => User::ROLE_REPARTIDOR,
            'status' => User::STATUS_ACTIVE,
            'password' => bcrypt('password-seguro'),
        ]);

        Driver::factory()->create([
            'user_id' => $user->id,
            'status' => $status,
            'verification_status' => $verificationStatus,
        ]);

        return $user;
    }

    public function test_a_verified_and_active_driver_can_log_in(): void
    {
        $user = $this->createDriverUser(Driver::STATUS_ACTIVE, Driver::VERIFICATION_VERIFIED);

        $this->attemptLogin($user)
            ->assertOk()
            ->assertJsonStructure(['token', 'user', 'driver']);
    }

    public function test_an_active_but_unverified_driver_cannot_log_in(): void
    {
        $user = $this->createDriverUser(Driver::STATUS_ACTIVE, Driver::VERIFICATION_PENDING);

        $this->attemptLogin($user)->assertUnprocessable();

        $this->assertNull($user->fresh()->tokens()->first());
    }

    public function test_an_in_review_driver_cannot_log_in(): void
    {
        $user = $this->createDriverUser(Driver::STATUS_ACTIVE, Driver::VERIFICATION_IN_REVIEW);

        $this->attemptLogin($user)->assertUnprocessable();
    }

    /**
     * status === RECHAZADO es el valor histórico (pre-Fase 2): a
     * partir de ahora reject() deja status intacto (ver Fase 3), pero
     * los registros ya rechazados antes de este cambio conservan
     * status = RECHAZADO y deben seguir mostrando el mismo mensaje de
     * siempre.
     */
    public function test_a_legacy_rejected_status_driver_cannot_log_in(): void
    {
        $user = $this->createDriverUser(Driver::STATUS_REJECTED, Driver::VERIFICATION_REJECTED);

        $this->attemptLogin($user)
            ->assertUnprocessable()
            ->assertJsonFragment(['email' => ['Tu solicitud de repartidor fue rechazada. Contacta al administrador.']]);
    }

    /**
     * Bajo la Fase 3 (todavía no implementada), reject() deja status
     * en PENDIENTE (no lo mueve a RECHAZADO) y solo cambia
     * verification_status. Este es el caso real que un repartidor
     * rechazado verá una vez exista ese flujo.
     */
    public function test_a_verification_rejected_driver_with_pending_status_sees_the_verification_pending_message(): void
    {
        $user = $this->createDriverUser(Driver::STATUS_PENDING, Driver::VERIFICATION_REJECTED);

        $this->attemptLogin($user)
            ->assertUnprocessable()
            ->assertJsonFragment(['email' => ['Tu cuenta de repartidor está pendiente de aprobación por un administrador.']]);
    }

    /**
     * El mensaje por status (SUSPENDIDO) se mantiene igual que antes,
     * sin importar que además esté verificado.
     */
    public function test_a_verified_but_suspended_driver_cannot_log_in_and_sees_the_suspended_message(): void
    {
        $user = $this->createDriverUser(Driver::STATUS_SUSPENDED, Driver::VERIFICATION_VERIFIED);

        $this->attemptLogin($user)
            ->assertUnprocessable()
            ->assertJsonFragment(['email' => ['Tu cuenta de repartidor está suspendida. Contacta al administrador.']]);
    }

    /**
     * Caso nuevo: status ACTIVO pero verification_status todavía no
     * VERIFICADO — el único mensaje que no existía antes de Fase 2.
     */
    public function test_an_active_but_unverified_driver_sees_a_verification_pending_message(): void
    {
        $user = $this->createDriverUser(Driver::STATUS_ACTIVE, Driver::VERIFICATION_PENDING);

        $this->attemptLogin($user)
            ->assertUnprocessable()
            ->assertJsonFragment(['email' => ['Tu cuenta de repartidor está pendiente de verificación por un administrador.']]);
    }
}
