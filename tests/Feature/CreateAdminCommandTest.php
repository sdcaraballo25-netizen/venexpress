<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Primer administrador de un despliegue sin consola (Render gratis).
 */
class CreateAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        putenv('ADMIN_PASSWORD');

        parent::tearDown();
    }

    public function test_it_creates_an_active_principal_admin(): void
    {
        $this->artisan('venexpress:create-admin', [
            '--email' => 'Admin@Venexpress.com',
            '--name' => 'Salva',
            '--password' => 'una-clave-larga',
        ])->assertSuccessful();

        $admin = User::where('email', 'admin@venexpress.com')->firstOrFail();

        $this->assertSame(User::ROLE_ADMIN_PRINCIPAL, $admin->role);
        $this->assertSame(User::STATUS_ACTIVE, $admin->status);
        $this->assertSame('Salva', $admin->name);
        $this->assertNotNull($admin->email_verified_at);
        $this->assertTrue(Hash::check('una-clave-larga', $admin->password));
    }

    public function test_it_reads_the_password_from_the_environment(): void
    {
        putenv('ADMIN_PASSWORD=clave-desde-render');

        $this->artisan('venexpress:create-admin', ['--email' => 'admin@venexpress.com'])
            ->assertSuccessful();

        $this->assertTrue(Hash::check('clave-desde-render', User::where('email', 'admin@venexpress.com')->value('password')));
    }

    public function test_an_existing_user_is_left_untouched(): void
    {
        $existing = User::factory()->create([
            'email' => 'admin@venexpress.com',
            'role' => User::ROLE_ADMIN_PRINCIPAL,
            'password' => 'clave-cambiada-por-el-admin',
        ]);

        $this->artisan('venexpress:create-admin', [
            '--email' => 'admin@venexpress.com',
            '--password' => 'la-clave-inicial',
        ])->assertSuccessful();

        $this->assertSame(1, User::count());
        $this->assertTrue(Hash::check('clave-cambiada-por-el-admin', $existing->fresh()->password));
    }

    public function test_it_rejects_a_short_password_or_an_invalid_email(): void
    {
        $this->artisan('venexpress:create-admin', ['--email' => 'admin@venexpress.com', '--password' => 'corta'])
            ->assertFailed();

        $this->artisan('venexpress:create-admin', ['--email' => 'no-es-un-correo', '--password' => 'una-clave-larga'])
            ->assertFailed();

        $this->assertSame(0, User::count());
    }
}
