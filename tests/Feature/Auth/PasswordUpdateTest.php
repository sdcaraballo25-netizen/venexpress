<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\PasswordChangeCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Livewire\Volt\Volt;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_can_be_updated_with_a_valid_code(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->actingAs($user);

        Volt::test('profile.update-password-form')->call('sendCode');

        $plainCode = $this->sentPlainCode($user);

        $component = Volt::test('profile.update-password-form')
            ->set('code', $plainCode)
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('updatePassword');

        $component
            ->assertHasNoErrors()
            ->assertNoRedirect();

        $user->refresh();

        $this->assertTrue(Hash::check('new-password', $user->password));
        $this->assertNull($user->password_change_code);
    }

    public function test_password_cannot_be_updated_without_requesting_a_code_first(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Volt::test('profile.update-password-form')
            ->set('code', '123456')
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('updatePassword');

        $component->assertHasErrors(['code']);

        $this->assertTrue(Hash::check('password', $user->refresh()->password));
    }

    public function test_an_incorrect_code_is_rejected(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->actingAs($user);

        Volt::test('profile.update-password-form')->call('sendCode');

        $component = Volt::test('profile.update-password-form')
            ->set('code', '000000')
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('updatePassword');

        $component->assertHasErrors(['code']);

        $this->assertTrue(Hash::check('password', $user->refresh()->password));
    }

    public function test_an_expired_code_is_rejected(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->actingAs($user);

        Volt::test('profile.update-password-form')->call('sendCode');

        $plainCode = $this->sentPlainCode($user);

        $user->forceFill([
            'password_change_code_expires_at' => now()->subMinute(),
        ])->save();

        $component = Volt::test('profile.update-password-form')
            ->set('code', $plainCode)
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('updatePassword');

        $component->assertHasErrors(['code']);

        $this->assertTrue(Hash::check('password', $user->refresh()->password));
    }

    /**
     * PasswordChangeCode::via() no expone el código en claro (solo se
     * guarda hasheado en la base de datos), así que lo leemos de la
     * notificación interceptada por Notification::fake() vía reflexión.
     */
    private function sentPlainCode(User $user): string
    {
        $plainCode = null;

        Notification::assertSentTo(
            $user,
            PasswordChangeCode::class,
            function (PasswordChangeCode $notification) use (&$plainCode) {
                $property = new \ReflectionProperty($notification, 'plainCode');
                $property->setAccessible(true);
                $plainCode = $property->getValue($notification);

                return true;
            }
        );

        return $plainCode;
    }
}
