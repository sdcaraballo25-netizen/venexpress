<?php

namespace Tests\Feature\Auth;

use App\Livewire\Pages\Auth\VerifyAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Cubre el flujo de verificación de cuenta por código de 6 dígitos y,
 * en particular, el rate limiting de intentos añadido para evitar
 * fuerza bruta sobre el código (antes solo el login tenía throttle).
 */
class VerifyAccountTest extends TestCase
{
    use RefreshDatabase;

    private function createPendingUser(): array
    {
        $user = User::factory()->create([
            'role' => User::ROLE_CLIENTE,
            'status' => User::STATUS_ACTIVE,
        ]);

        $plainToken = $user->generateVerificationToken();

        $this->withSession(['pending_verification_user_id' => $user->id]);

        return [$user, $plainToken];
    }

    public function test_correct_code_verifies_account_and_logs_in(): void
    {
        [$user, $plainToken] = $this->createPendingUser();

        Livewire::test(VerifyAccount::class)
            ->set('code', $plainToken)
            ->call('verify')
            ->assertRedirect(route('cliente.dashboard', absolute: false));

        $this->assertTrue(Auth::check());
        $this->assertNotNull($user->fresh()->account_verified_at);
    }

    public function test_incorrect_code_shows_error_and_does_not_log_in(): void
    {
        $this->createPendingUser();

        Livewire::test(VerifyAccount::class)
            ->set('code', '000000')
            ->call('verify')
            ->assertHasErrors('code');

        $this->assertFalse(Auth::check());
    }

    public function test_too_many_incorrect_attempts_are_blocked_even_with_the_correct_code(): void
    {
        [, $plainToken] = $this->createPendingUser();

        $component = Livewire::test(VerifyAccount::class);

        // Cinco intentos fallidos (mismo límite que LoginForm).
        for ($i = 0; $i < 5; $i++) {
            $component->set('code', '000000')->call('verify');
        }

        // El sexto intento, aunque use el código correcto, debe
        // quedar bloqueado por el rate limiter.
        $component->set('code', $plainToken)
            ->call('verify')
            ->assertHasErrors('code');

        $this->assertFalse(Auth::check());
    }
}
