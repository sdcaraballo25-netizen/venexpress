<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use App\Notifications\WelcomeVerificationToken;
use App\Services\AllyStaffService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Livewire\Volt\Volt;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Antes un aliado/repartidor/emprendedor podía poner cualquier correo
 * en su perfil sin probar que era suyo. Ahora el correo nuevo se
 * confirma con el código de verificación, como en el registro.
 */
class EmailChangeVerificationTest extends TestCase
{
    use CreatesTestPackages;
    use RefreshDatabase;

    public function test_changing_email_requires_confirming_a_code_sent_to_the_new_address(): void
    {
        Notification::fake();

        $ally = $this->createAlly();
        $user = $ally->user;
        $user->forceFill(['account_verified_at' => now()])->save();

        $this->actingAs($user);

        Volt::test('profile.update-profile-information-form')
            ->set('name', $user->name)
            ->set('email', 'nuevo@example.com')
            ->call('updateProfileInformation')
            ->assertRedirect(route('verify-account'));

        $user->refresh();

        $this->assertSame('nuevo@example.com', $user->email);
        $this->assertNull($user->account_verified_at);
        $this->assertFalse(Auth::check());
        $this->assertSame($user->id, session('pending_verification_user_id'));
        Notification::assertSentTo($user, WelcomeVerificationToken::class);
    }

    public function test_same_email_keeps_the_session(): void
    {
        $ally = $this->createAlly();
        $user = $ally->user;
        $user->forceFill(['account_verified_at' => now()])->save();

        $this->actingAs($user);

        Volt::test('profile.update-profile-information-form')
            ->set('name', 'Nombre Nuevo')
            ->call('updateProfileInformation')
            ->assertNoRedirect();

        $this->assertNotNull($user->fresh()->account_verified_at);
    }

    public function test_taquilla_and_franchise_cannot_change_their_email_themselves(): void
    {
        $ally = $this->createAlly();
        $taquilla = app(AllyStaffService::class)->create($ally, [
            'name' => 'Taquilla', 'username' => 'taq-mail', 'password' => 'password-seguro',
        ]);
        $original = $taquilla->email;

        $this->actingAs($taquilla);

        Volt::test('profile.update-profile-information-form')
            ->assertSet('canChangeEmail', false)
            ->set('email', 'otro@example.com')
            ->call('updateProfileInformation');

        $this->assertSame($original, $taquilla->fresh()->email);

        $franchise = $this->createAlly();
        $franchise->user->update(['role' => User::ROLE_FRANQUICIADO]);
        $this->actingAs($franchise->user->fresh());

        Volt::test('profile.update-profile-information-form')->assertSet('canChangeEmail', false);
    }
}
