<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Livewire\Volt\Volt;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    private function fakeGoogleCallback(string $email, string $googleId = 'google-123', string $name = 'Google Person'): void
    {
        $socialiteUser = SocialiteUser::fake([
            'id' => $googleId,
            'name' => $name,
            'email' => $email,
        ]);

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('stateless')->once()->andReturnSelf();
        $provider->shouldReceive('user')->once()->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);
    }

    public function test_a_brand_new_google_account_is_sent_to_finish_registering(): void
    {
        $this->fakeGoogleCallback('nuevo@example.com', 'google-abc', 'Nuevo Usuario');

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('register'));

        $this->assertDatabaseMissing('users', ['email' => 'nuevo@example.com']);

        $this->assertSame(
            [
                'google_id' => 'google-abc',
                'name' => 'Nuevo Usuario',
                'email' => 'nuevo@example.com',
            ],
            session('google_pending')
        );
    }

    public function test_the_register_form_prefills_name_and_email_from_the_pending_google_session(): void
    {
        session([
            'google_pending' => [
                'google_id' => 'google-abc',
                'name' => 'Nuevo Usuario',
                'email' => 'nuevo@example.com',
            ],
        ]);

        Volt::test('pages.auth.register')
            ->assertSet('viaGoogle', true)
            ->assertSet('name', 'Nuevo Usuario')
            ->assertSet('email', 'nuevo@example.com')
            ->assertDontSee('Confirmar contraseña');
    }

    public function test_completing_registration_via_google_skips_password_and_logs_in_immediately(): void
    {
        session([
            'google_pending' => [
                'google_id' => 'google-abc',
                'name' => 'Nuevo Usuario',
                'email' => 'nuevo@example.com',
            ],
        ]);

        $component = Volt::test('pages.auth.register')
            ->set('id_doc', 'V-12345678')
            ->set('phone', '+58 412 1234567');

        $component->call('register');

        $component->assertRedirect('/cliente/dashboard');

        $this->assertDatabaseHas('users', [
            'email' => 'nuevo@example.com',
            'google_id' => 'google-abc',
        ]);

        $user = User::where('email', 'nuevo@example.com')->first();

        $this->assertNotNull($user->email_verified_at);
        $this->assertAuthenticatedAs($user);

        $this->assertDatabaseHas('customers', [
            'id_doc' => 'V-12345678',
            'email' => 'nuevo@example.com',
        ]);

        $this->assertNull(session('google_pending'));
    }

    public function test_an_existing_account_logs_in_directly_through_google_and_gets_linked(): void
    {
        $user = User::factory()->create([
            'email' => 'ya-existe@example.com',
            'role' => User::ROLE_CLIENTE,
            'status' => User::STATUS_ACTIVE,
            'google_id' => null,
        ]);

        $this->fakeGoogleCallback('ya-existe@example.com', 'google-xyz');

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('cliente.dashboard'));

        $this->assertAuthenticatedAs($user->fresh());
        $this->assertSame('google-xyz', $user->fresh()->google_id);
    }

    public function test_an_admin_account_cannot_log_in_through_google(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'role' => User::ROLE_ADMIN_PRINCIPAL,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->fakeGoogleCallback('admin@example.com', 'google-admin');

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_canceling_the_google_prompt_redirects_to_login_without_crashing(): void
    {
        $response = $this->get(route('auth.google.callback', ['error' => 'access_denied']));

        $response->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
