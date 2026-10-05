<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\UsersManager;
use App\Models\Ally;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Livewire\Volt\Volt;
use Tests\TestCase;

/**
 * Regla: si un Admin crea directamente un Repartidor o un Aliado, la
 * creación administrativa cuenta como aprobación y puede operar de
 * inmediato. Antes quedaban con verification_status = PENDIENTE (el
 * default de la columna), canOperate() devolvía false y
 * EnsureAccountIsApproved los bloqueaba aunque el Admin los hubiera
 * creado activos. Quien se registra por su cuenta sigue el flujo de
 * verificación de siempre.
 */
class AdminCreatedOperatorVerificationTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_ADMIN_PRINCIPAL,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
            'password' => bcrypt('admin-password'),
        ]);
    }

    public function test_a_driver_created_by_an_admin_is_verified_and_can_operate(): void
    {
        Livewire::actingAs($this->createAdmin())
            ->test(UsersManager::class)
            ->call('openCreateModal')
            ->set('role', 'repartidor')
            ->set('name', 'Repartidor Admin')
            ->set('email', 'driver-admin@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('driver_type', Driver::TYPE_DELIVERY)
            ->set('vehicle_plate', 'ADM-001')
            ->set('vehicle_type', 'Moto')
            ->set('phone', '04120000000')
            ->call('requestCreate')
            ->set('adminPassword', 'admin-password')
            ->call('createUser')
            ->assertHasNoErrors();

        $user = User::where('email', 'driver-admin@example.com')->firstOrFail();
        $driver = $user->driver;

        $this->assertSame(Driver::STATUS_ACTIVE, $driver->status);
        $this->assertSame(Driver::VERIFICATION_VERIFIED, $driver->verification_status);
        $this->assertNotNull($driver->verification_reviewed_at);
        $this->assertTrue($driver->canOperate());

        // Y el middleware account.approved lo deja entrar a su panel.
        $this->actingAs($user)
            ->get(route('repartidor.dashboard'))
            ->assertOk();
    }

    public function test_an_ally_created_by_an_admin_is_verified_and_can_operate(): void
    {
        Livewire::actingAs($this->createAdmin())
            ->test(UsersManager::class)
            ->call('openCreateModal')
            ->set('role', 'aliado')
            ->set('name', 'Aliado Admin')
            ->set('email', 'ally-admin@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('business_name', 'Agencia Creada por Admin')
            ->set('rif', 'J-12345678-9')
            ->set('state', 'Carabobo')
            ->set('city', 'Valencia')
            ->set('address', 'Av. Bolívar')
            ->call('requestCreate')
            ->set('adminPassword', 'admin-password')
            ->call('createUser')
            ->assertHasNoErrors();

        $user = User::where('email', 'ally-admin@example.com')->firstOrFail();
        $ally = Ally::where('user_id', $user->id)->firstOrFail();

        $this->assertSame(Ally::STATUS_ACTIVE, $ally->status);
        $this->assertSame(Ally::VERIFICATION_VERIFIED, $ally->verification_status);
        $this->assertNotNull($ally->verification_reviewed_at);
        $this->assertTrue($ally->canOperate());

        $this->actingAs($user)
            ->get(route('ally.dashboard'))
            ->assertOk();
    }

    public function test_a_self_registered_driver_still_goes_through_verification(): void
    {
        Notification::fake();

        Volt::test('pages.auth.register')
            ->set('name', 'Repartidor Autorregistrado')
            ->set('email', 'self-driver@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->set('role', 'repartidor')
            ->set('vehicle_plate', 'SELF-01')
            ->set('vehicle_type', 'Moto')
            ->set('phone', '+58 412 1234567')
            ->call('register')
            ->assertHasNoErrors();

        $driver = User::where('email', 'self-driver@example.com')->firstOrFail()->driver;

        $this->assertSame(Driver::VERIFICATION_PENDING, $driver->verification_status);
        $this->assertFalse($driver->canOperate());
    }
}
