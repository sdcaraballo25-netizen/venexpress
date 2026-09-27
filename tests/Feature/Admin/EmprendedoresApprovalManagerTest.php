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

    private function createEmprendedor(
        string $status = Emprendedor::STATUS_PENDING,
        ?string $verificationStatus = null
    ): Emprendedor {
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
            'verification_status' => Ally::VERIFICATION_VERIFIED,
        ]);

        $user = User::factory()->create(['role' => User::ROLE_EMPRENDEDOR]);

        return Emprendedor::create([
            'user_id' => $user->id,
            'pickup_ally_id' => $ally->id,
            'business_name' => 'Tienda de Prueba',
            'document_id' => 'V-' . random_int(10000000, 99999999),
            'status' => $status,
            // Por defecto, la verificación sigue el mismo criterio que
            // el status pedido (ACTIVO/SUSPENDIDO ya implican que en
            // algún momento pasaron por VERIFICADO), salvo que el test
            // pida explícitamente otra combinación.
            'verification_status' => $verificationStatus ?? match ($status) {
                Emprendedor::STATUS_ACTIVE, Emprendedor::STATUS_SUSPENDED => Emprendedor::VERIFICATION_VERIFIED,
                default => Emprendedor::VERIFICATION_PENDING,
            },
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

    public function test_approving_an_emprendedor_sets_both_verification_status_and_status(): void
    {
        Notification::fake();

        $admin = $this->createAdmin();
        $emprendedor = $this->createEmprendedor(Emprendedor::STATUS_PENDING, Emprendedor::VERIFICATION_IN_REVIEW);

        Livewire::actingAs($admin)
            ->test(EmprendedoresApprovalManager::class)
            ->call('approve', $emprendedor->id);

        $emprendedor->refresh();

        $this->assertSame(Emprendedor::STATUS_ACTIVE, $emprendedor->status);
        $this->assertSame(Emprendedor::VERIFICATION_VERIFIED, $emprendedor->verification_status);
        $this->assertNull($emprendedor->verification_rejection_reason);
        $this->assertNotNull($emprendedor->verification_reviewed_at);
    }

    public function test_rejecting_an_emprendedor_notifies_by_email_and_revokes_tokens(): void
    {
        Notification::fake();

        $admin = $this->createAdmin();
        $emprendedor = $this->createEmprendedor();
        $emprendedor->user->createToken('test-token');

        Livewire::actingAs($admin)
            ->test(EmprendedoresApprovalManager::class)
            ->call('openReject', $emprendedor->id)
            ->set('rejectionReason', 'La foto del RIF no es legible.')
            ->call('reject');

        $emprendedor->refresh();

        // reject() ya no toca el estado operativo (Fase 3): sigue
        // PENDIENTE, solo cambia verification_status.
        $this->assertSame(Emprendedor::STATUS_PENDING, $emprendedor->status);
        $this->assertSame(Emprendedor::VERIFICATION_REJECTED, $emprendedor->verification_status);
        $this->assertSame('La foto del RIF no es legible.', $emprendedor->verification_rejection_reason);
        $this->assertSame(0, $emprendedor->user->tokens()->count());

        Notification::assertSentTo($emprendedor->user, AccountRejected::class, function (AccountRejected $notification) use ($emprendedor) {
            $mail = $notification->toMail($emprendedor->user);
            $lines = [...$mail->introLines, ...$mail->outroLines];

            return collect($lines)->contains(fn ($line) => str_contains($line, 'La foto del RIF no es legible.'));
        });
    }

    public function test_rejecting_an_emprendedor_without_a_reason_fails_validation(): void
    {
        Notification::fake();

        $admin = $this->createAdmin();
        $emprendedor = $this->createEmprendedor();

        Livewire::actingAs($admin)
            ->test(EmprendedoresApprovalManager::class)
            ->call('openReject', $emprendedor->id)
            ->call('reject')
            ->assertHasErrors(['rejectionReason' => 'required']);

        $this->assertSame(Emprendedor::VERIFICATION_PENDING, $emprendedor->fresh()->verification_status);
        Notification::assertNotSentTo($emprendedor->user, AccountRejected::class);
    }

    public function test_suspending_and_reactivating_an_active_emprendedor(): void
    {
        $admin = $this->createAdmin();
        $emprendedor = $this->createEmprendedor(Emprendedor::STATUS_ACTIVE);

        Livewire::actingAs($admin)
            ->test(EmprendedoresApprovalManager::class)
            ->call('suspend', $emprendedor->id);

        $emprendedor->refresh();

        $this->assertSame(Emprendedor::STATUS_SUSPENDED, $emprendedor->status);
        // suspend() no debe alterar verification_status.
        $this->assertSame(Emprendedor::VERIFICATION_VERIFIED, $emprendedor->verification_status);
        $this->assertFalse($emprendedor->canOperate());

        Livewire::actingAs($admin)
            ->test(EmprendedoresApprovalManager::class)
            ->call('activate', $emprendedor->id);

        $emprendedor->refresh();

        $this->assertSame(Emprendedor::STATUS_ACTIVE, $emprendedor->status);
        $this->assertTrue($emprendedor->canOperate());
    }

    public function test_admin_cannot_activate_an_emprendedor_that_is_not_verified(): void
    {
        $admin = $this->createAdmin();
        $emprendedor = $this->createEmprendedor(Emprendedor::STATUS_SUSPENDED, Emprendedor::VERIFICATION_PENDING);

        Livewire::actingAs($admin)
            ->test(EmprendedoresApprovalManager::class)
            ->call('activate', $emprendedor->id);

        $this->assertSame(Emprendedor::STATUS_SUSPENDED, $emprendedor->fresh()->status);
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
