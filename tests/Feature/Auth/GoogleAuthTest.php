<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as SocialiteUser;
use Livewire\Volt\Volt;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    private function fakeGoogleCallback(string $email, string $googleId = 'google-123', string $name = 'Google Person', array $extra = []): void
    {
        $socialiteUser = SocialiteUser::fake(array_merge([
            'id' => $googleId,
            'name' => $name,
            'email' => $email,
        ], $extra));

        $provider = Mockery::mock(Provider::class);
        $provider->shouldNotReceive('stateless');
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

    public function test_tampering_with_the_email_property_cannot_spoof_a_different_account(): void
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
            ->set('phone', '+58 412 1234567')
            // $email es una prop pública editable (necesaria para el
            // registro manual); un cliente manipulado podría intentar
            // cambiarla antes de enviar el formulario.
            ->set('email', 'atacante@evil.com');

        $component->call('register');

        $component->assertRedirect('/cliente/dashboard');

        $this->assertDatabaseHas('users', [
            'email' => 'nuevo@example.com',
            'google_id' => 'google-abc',
        ]);

        $this->assertDatabaseMissing('users', [
            'email' => 'atacante@evil.com',
        ]);
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

    public function test_a_client_registered_through_google_can_actually_open_their_dashboard(): void
    {
        session([
            'google_pending' => [
                'google_id' => 'google-dash',
                'name' => 'Cliente Google',
                'email' => 'cliente-google@example.com',
            ],
        ]);

        Volt::test('pages.auth.register')
            ->set('id_doc', 'V-22223333')
            ->set('phone', '+58 412 1234567')
            ->call('register')
            ->assertRedirect('/cliente/dashboard');

        $user = User::where('email', 'cliente-google@example.com')->firstOrFail();

        $this->assertTrue($user->isAccountVerified());

        // Antes EnsureAccountIsVerified lo mandaba a /verify-account.
        $this->actingAs($user)->get('/cliente/dashboard')->assertOk();
    }

    public function test_an_inactive_account_cannot_log_in_through_google(): void
    {
        User::factory()->create([
            'email' => 'inactivo@example.com',
            'role' => User::ROLE_CLIENTE,
            'status' => User::STATUS_INACTIVE,
        ]);

        $this->fakeGoogleCallback('inactivo@example.com', 'google-inactivo');

        $this->get(route('auth.google.callback'))->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_an_emprendedor_is_sent_to_their_own_dashboard(): void
    {
        User::factory()->create([
            'email' => 'emprendedor@example.com',
            'role' => User::ROLE_EMPRENDEDOR,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->fakeGoogleCallback('emprendedor@example.com', 'google-emp');

        $this->get(route('auth.google.callback'))->assertRedirect(route('emprendedor.dashboard'));
    }

    public function test_an_unverified_google_email_cannot_take_over_an_existing_account(): void
    {
        $user = User::factory()->create([
            'email' => 'victima@example.com',
            'role' => User::ROLE_CLIENTE,
            'status' => User::STATUS_ACTIVE,
            'google_id' => null,
        ]);

        $this->fakeGoogleCallback('victima@example.com', 'google-atacante', 'X', ['email_verified' => false]);

        $this->get(route('auth.google.callback'))->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertNull($user->fresh()->google_id);
    }

    /**
     * Antes, entrar con Google vinculaba (e iniciaba sesión en)
     * cualquier cuenta con ese correo aunque nunca se hubiera
     * verificado. Cualquiera puede registrar una cuenta con un correo
     * ajeno y conocer su contraseña: vincularla dejaba al dueño real
     * del correo dentro de una cuenta que otra persona controla.
     */
    public function test_an_unverified_account_is_not_auto_linked_through_google(): void
    {
        $user = User::factory()->unverifiedAccount()->create([
            'email' => 'sin-codigo@example.com',
            'role' => User::ROLE_CLIENTE,
            'status' => User::STATUS_ACTIVE,
            'google_id' => null,
        ]);

        $this->assertFalse($user->isAccountVerified());

        $this->fakeGoogleCallback('sin-codigo@example.com', 'google-sin-codigo');

        $this->get(route('auth.google.callback'))->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertNull($user->fresh()->google_id);
        $this->assertFalse($user->fresh()->isAccountVerified());
    }

    public function test_an_unverified_operator_account_is_not_auto_linked_through_google(): void
    {
        $user = User::factory()->unverifiedAccount()->create([
            'email' => 'aliado-sin-codigo@example.com',
            'role' => User::ROLE_ALIADO,
            'status' => User::STATUS_ACTIVE,
            'google_id' => null,
        ]);

        $this->fakeGoogleCallback('aliado-sin-codigo@example.com', 'google-aliado');

        $this->get(route('auth.google.callback'))->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertNull($user->fresh()->google_id);
    }

    public function test_an_account_already_linked_to_the_same_google_id_still_logs_in(): void
    {
        $user = User::factory()->create([
            'email' => 'vinculada@example.com',
            'role' => User::ROLE_CLIENTE,
            'status' => User::STATUS_ACTIVE,
            'google_id' => 'google-vinculada',
        ]);

        $this->fakeGoogleCallback('vinculada@example.com', 'google-vinculada');

        $this->get(route('auth.google.callback'))->assertRedirect(route('cliente.dashboard'));

        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_an_operator_registered_through_google_is_verified_without_a_code(): void
    {
        session([
            'google_pending' => [
                'google_id' => 'google-repartidor',
                'name' => 'Repartidor Google',
                'email' => 'repartidor-google@example.com',
            ],
        ]);

        Volt::test('pages.auth.register')
            ->set('role', 'repartidor')
            ->set('vehicle_plate', 'GOO123')
            ->set('vehicle_type', 'Moto')
            ->set('phone', '+58 412 1234567')
            ->call('register')
            ->assertRedirect(route('repartidor.dashboard', absolute: false));

        $user = User::where('email', 'repartidor-google@example.com')->firstOrFail();

        $this->assertTrue($user->isAccountVerified());
        $this->assertAuthenticatedAs($user);
    }

    public function test_an_invalid_oauth_state_redirects_to_login_instead_of_crashing(): void
    {
        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->once()->andThrow(new InvalidStateException);

        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

        $this->get(route('auth.google.callback', ['code' => 'x', 'state' => 'forjado']))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
