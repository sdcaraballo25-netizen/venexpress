<?php

namespace Tests\Feature\Ally;

use App\Livewire\Ally\StaffManager;
use App\Models\User;
use App\Services\AllyStaffService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Livewire\Volt\Volt;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * RF-ALI-02: el Aliado Administrador abre y administra las taquillas
 * de su propio comercio. AllyStaffService y Ally::staffUsers() ya
 * existían (de una fase anterior) pero no tenían ninguna pantalla —
 * esto conecta esa lógica ya construida con una UI real.
 *
 * Taquilla inicia sesión con un "usuario" simple (no un correo real):
 * un negocio con varias taquillas no debería tener que inventarse un
 * correo por cada una.
 */
class StaffManagerTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestPackages;

    public function test_ally_can_create_a_taquilla_user(): void
    {
        $ally = $this->createAlly();

        Livewire::actingAs($ally->user)
            ->test(StaffManager::class)
            ->call('startCreate')
            ->set('name', 'Taquilla Centro')
            ->set('username', 'taquilla1')
            ->set('password', 'password-seguro')
            ->set('password_confirmation', 'password-seguro')
            ->call('save')
            ->assertHasNoErrors();

        $staff = User::where('username', 'taquilla1')->firstOrFail();

        $this->assertSame(User::ROLE_ALIADO_TAQUILLA, $staff->role);
        $this->assertSame($ally->id, $staff->ally_id);
        $this->assertTrue($staff->isActive());
        $this->assertNotNull($staff->email_verified_at);
    }

    public function test_taquilla_user_can_log_in_with_their_username_not_an_email(): void
    {
        $ally = $this->createAlly();

        app(AllyStaffService::class)->create($ally, [
            'name' => 'Taquilla Centro',
            'username' => 'taquilla1',
            'password' => 'password-seguro',
        ]);

        // El mismo campo del formulario acepta correo o usuario — ver
        // LoginForm::authenticate(). Aquí probamos con el usuario.
        Volt::test('pages.auth.login')
            ->set('form.email', 'taquilla1')
            ->set('form.password', 'password-seguro')
            ->call('login')
            ->assertHasNoErrors();

        $this->assertAuthenticated();
        $this->assertSame('taquilla1', auth()->user()->username);
    }

    public function test_taquilla_user_cannot_access_staff_manager(): void
    {
        $ally = $this->createAlly();
        $staffUser = app(\App\Services\AllyStaffService::class)->create($ally, [
            'name' => 'Taquilla 1',
            'username' => 'taquilla1',
            'password' => 'password-seguro',
        ]);

        $this->actingAs($staffUser)
            ->get(route('ally.staff'))
            ->assertForbidden();
    }

    public function test_ally_can_edit_and_deactivate_their_own_staff(): void
    {
        $ally = $this->createAlly();
        $staff = app(\App\Services\AllyStaffService::class)->create($ally, [
            'name' => 'Taquilla 1',
            'username' => 'taquilla1',
            'password' => 'password-seguro',
        ]);

        Livewire::actingAs($ally->user)
            ->test(StaffManager::class)
            ->call('edit', $staff->id)
            ->set('name', 'Taquilla Renombrada')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Taquilla Renombrada', $staff->fresh()->name);

        Livewire::actingAs($ally->user)
            ->test(StaffManager::class)
            ->call('toggleActive', $staff->id);

        $this->assertFalse($staff->fresh()->isActive());
    }

    public function test_ally_cannot_manage_another_allys_staff(): void
    {
        $allyA = $this->createAlly();
        $allyB = $this->createAlly();

        $staffOfB = app(\App\Services\AllyStaffService::class)->create($allyB, [
            'name' => 'Taquilla de B',
            'username' => 'taquillab',
            'password' => 'password-seguro',
        ]);

        $this->expectException(ModelNotFoundException::class);

        Livewire::actingAs($allyA->user)
            ->test(StaffManager::class)
            ->call('edit', $staffOfB->id);
    }
}
