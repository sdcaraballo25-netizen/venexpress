<?php

namespace Tests\Feature\Auth;

use App\Livewire\Pages\Auth\VerifyAccount;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Livewire\Volt\Volt;
use Tests\Feature\Concerns\CreatesTestEmprendedores;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * El middleware nativo `verified` no tiene efecto en este proyecto
 * (User no implementa MustVerifyEmail), así que Aliado, Repartidor y
 * Emprendedor entraban a su panel sin haber demostrado nunca que el
 * correo era suyo. Ahora todos los roles que se autorregistran usan el
 * mismo código de 6 dígitos que ya usaba Cliente (EnsureAccountIsVerified),
 * y los roles creados por alguien de confianza (Admin, Taquilla, Almacén)
 * no quedan bloqueados.
 */
class AccountVerificationRolesTest extends TestCase
{
    use CreatesTestEmprendedores;
    use CreatesTestPackages;
    use RefreshDatabase;

    private function approvedDriverUser(bool $verified): User
    {
        $factory = User::factory();

        $user = ($verified ? $factory : $factory->unverifiedAccount())->create([
            'role' => User::ROLE_REPARTIDOR,
            'status' => User::STATUS_ACTIVE,
            'password' => bcrypt('password-seguro'),
        ]);

        Driver::factory()->create([
            'user_id' => $user->id,
            'status' => Driver::STATUS_ACTIVE,
            'verification_status' => Driver::VERIFICATION_VERIFIED,
            'driver_type' => Driver::TYPE_DELIVERY,
        ]);

        return $user;
    }

    public function test_an_unverified_driver_is_sent_to_the_verification_screen(): void
    {
        $user = $this->approvedDriverUser(verified: false);

        $this->actingAs($user)
            ->get(route('repartidor.dashboard'))
            ->assertRedirect(route('verify-account'));

        $this->assertGuest();
        $this->assertSame($user->id, session('pending_verification_user_id'));
    }

    public function test_a_verified_driver_can_open_the_dashboard(): void
    {
        $user = $this->approvedDriverUser(verified: true);

        $this->actingAs($user)
            ->get(route('repartidor.dashboard'))
            ->assertOk();
    }

    public function test_an_unverified_ally_owner_is_sent_to_the_verification_screen(): void
    {
        $ally = $this->createAlly();
        $ally->user->forceFill(['account_verified_at' => null])->save();

        $this->actingAs($ally->user->fresh())
            ->get(route('ally.dashboard'))
            ->assertRedirect(route('verify-account'));

        // Tampoco puede llegar a "Mi Verificación" documental sin
        // verificar antes el correo.
        $this->actingAs($ally->user->fresh())
            ->get(route('ally.verificacion'))
            ->assertRedirect(route('verify-account'));
    }

    public function test_an_unverified_emprendedor_is_sent_to_the_verification_screen(): void
    {
        $emprendedor = $this->createEmprendedor();
        $emprendedor->user->forceFill(['account_verified_at' => null])->save();

        $this->actingAs($emprendedor->user->fresh())
            ->get(route('emprendedor.dashboard'))
            ->assertRedirect(route('verify-account'));
    }

    public function test_staff_created_by_a_trusted_actor_is_not_blocked(): void
    {
        $ally = $this->createAlly();

        $taquilla = User::factory()->unverifiedAccount()->create([
            'role' => User::ROLE_ALIADO_TAQUILLA,
            'status' => User::STATUS_ACTIVE,
            'ally_id' => $ally->id,
        ]);

        $this->actingAs($taquilla)
            ->get(route('ally.sales-closeout'))
            ->assertOk();

        $admin = User::factory()->unverifiedAccount()->create([
            'role' => User::ROLE_ADMIN_PRINCIPAL,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    public function test_the_code_verifies_an_ally_and_sends_them_to_their_own_panel(): void
    {
        $ally = $this->createAlly();
        $user = $ally->user;
        $user->forceFill(['account_verified_at' => null])->save();

        $plainToken = $user->generateVerificationToken();

        $this->withSession(['pending_verification_user_id' => $user->id]);

        Livewire::test(VerifyAccount::class)
            ->set('code', $plainToken)
            ->call('verify')
            ->assertRedirect(route('ally.dashboard', absolute: false));

        $this->assertTrue(Auth::check());
        $this->assertTrue($user->fresh()->isAccountVerified());
    }

    public function test_an_unverified_driver_cannot_get_an_api_token(): void
    {
        $user = $this->approvedDriverUser(verified: false);

        $this->postJson('/api/driver/login', [
            'email' => $user->email,
            'password' => 'password-seguro',
            'device_name' => 'telefono',
        ])->assertUnprocessable()
            ->assertJsonMissingPath('token');
    }

    public function test_an_existing_token_stops_working_until_the_driver_verifies(): void
    {
        $user = $this->approvedDriverUser(verified: true);

        $token = $this->postJson('/api/driver/login', [
            'email' => $user->email,
            'password' => 'password-seguro',
            'device_name' => 'telefono',
        ])->assertOk()->json('token');

        $user->forceFill(['account_verified_at' => null])->save();

        $this->getJson('/api/driver/me', ['Authorization' => "Bearer {$token}"])
            ->assertForbidden();
    }

    public function test_login_of_an_unverified_ally_ends_in_the_verification_screen(): void
    {
        $ally = $this->createAlly();
        $ally->user->forceFill(['account_verified_at' => null, 'password' => bcrypt('password-seguro')])->save();

        Volt::test('pages.auth.login')
            ->set('form.email', $ally->user->email)
            ->set('form.password', 'password-seguro')
            ->call('login')
            ->assertRedirect(route('ally.dashboard', absolute: false));

        // Al seguir la redirección, el middleware lo manda a verificar.
        $this->get(route('ally.dashboard'))->assertRedirect(route('verify-account'));
        $this->assertGuest();
    }

    public function test_existing_operator_accounts_are_backfilled_as_verified(): void
    {
        $driver = User::factory()->unverifiedAccount()->create(['role' => User::ROLE_REPARTIDOR]);
        $client = User::factory()->unverifiedAccount()->create(['role' => User::ROLE_CLIENTE]);

        $migration = require database_path('migrations/2026_10_03_000001_backfill_account_verified_at_for_operator_roles.php');
        $migration->up();

        $this->assertTrue($driver->fresh()->isAccountVerified());

        // A los clientes ya se les exigía el código: no se tocan.
        $this->assertFalse($client->fresh()->isAccountVerified());
    }

    public function test_requires_account_verification_only_for_self_registered_roles(): void
    {
        foreach ([User::ROLE_CLIENTE, User::ROLE_ALIADO, User::ROLE_REPARTIDOR, User::ROLE_EMPRENDEDOR] as $role) {
            $this->assertTrue((new User(['role' => $role]))->requiresAccountVerification(), $role);
        }

        foreach ([User::ROLE_ADMIN_PRINCIPAL, User::ROLE_ADMIN_OPERATIVO, User::ROLE_ALIADO_TAQUILLA, User::ROLE_ALMACEN] as $role) {
            $this->assertFalse((new User(['role' => $role]))->requiresAccountVerification(), $role);
        }
    }
}
