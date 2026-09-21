<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\EmprendedoresApprovalManager;
use App\Models\Ally;
use App\Models\Emprendedor;
use App\Models\User;
use App\Notifications\AccountApproved;
use App\Notifications\AccountRejected;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class EmprendedoresApprovalManagerTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_ADMIN_PRINCIPAL,
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    private function createEmprendedor(string $status = Emprendedor::STATUS_PENDING): Emprendedor
    {
        $allyUser = User::factory()->create(['role' => User::ROLE_ALIADO]);

        $ally = Ally::create([
            'user_id' => $allyUser->id,
            'business_name' => 'Agencia de Retiro',
            'rif' => 'J-' . random_int(10000000, 99999999) . '-0',
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
            'address' => 'Av. Principal',
            'commission_percentage' => 10,
            'status' => Ally::STATUS_ACTIVE,
        ]);

        $user = User::factory()->create(['role' => User::ROLE_EMPRENDEDOR]);

        return Emprendedor::create([
            'user_id' => $user->id,
            'pickup_ally_id' => $ally->id,
            'business_name' => 'Tienda de Prueba',
            'document_id' => 'V-' . random_int(10000000, 99999999),
            'status' => $status,
        ]);
    }

    public function test_approving_an_emprendedor_activates_it_and_notifies_by_email(): void
    {
        Notification::fake();

        $admin = $this->createAdmin();
        $emprendedor = $this->createEmprendedor();

        Livewire::actingAs($admin)
            ->test(EmprendedoresApprovalManager::class)
            ->call('approve', $emprendedor->id);

        $this->assertSame(Emprendedor::STATUS_ACTIVE, $emprendedor->fresh()->status);

        Notification::assertSentTo($emprendedor->user, AccountApproved::class);
    }

    public function test_rejecting_an_emprendedor_notifies_by_email_and_revokes_tokens(): void
    {
        Notification::fake();

        $admin = $this->createAdmin();
        $emprendedor = $this->createEmprendedor();
        $emprendedor->user->createToken('test-token');

        Livewire::actingAs($admin)
            ->test(EmprendedoresApprovalManager::class)
            ->call('reject', $emprendedor->id);

        $this->assertSame(Emprendedor::STATUS_REJECTED, $emprendedor->fresh()->status);
        $this->assertSame(0, $emprendedor->user->tokens()->count());

        Notification::assertSentTo($emprendedor->user, AccountRejected::class);
    }

    public function test_suspending_and_reactivating_an_active_emprendedor(): void
    {
        $admin = $this->createAdmin();
        $emprendedor = $this->createEmprendedor(Emprendedor::STATUS_ACTIVE);

        Livewire::actingAs($admin)
            ->test(EmprendedoresApprovalManager::class)
            ->call('suspend', $emprendedor->id);

        $this->assertSame(Emprendedor::STATUS_SUSPENDED, $emprendedor->fresh()->status);

        Livewire::actingAs($admin)
            ->test(EmprendedoresApprovalManager::class)
            ->call('activate', $emprendedor->id);

        $this->assertSame(Emprendedor::STATUS_ACTIVE, $emprendedor->fresh()->status);
    }

    public function test_the_screen_lists_emprendedores_and_filters_by_search(): void
    {
        $admin = $this->createAdmin();
        $match = $this->createEmprendedor();
        $match->update(['business_name' => 'Tienda Buscable']);

        $other = $this->createEmprendedor();
        $other->update(['business_name' => 'Otra Tienda']);

        Livewire::actingAs($admin)
            ->test(EmprendedoresApprovalManager::class)
            ->set('search', 'Buscable')
            ->assertSee('Tienda Buscable')
            ->assertDontSee('Otra Tienda');
    }
}
