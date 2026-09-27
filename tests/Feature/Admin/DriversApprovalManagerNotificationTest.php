<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\DriversApprovalManager;
use App\Models\Driver;
use App\Models\User;
use App\Notifications\AccountApproved;
use App\Notifications\AccountRejected;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * El repartidor no tiene otra forma de enterarse de que su solicitud
 * fue aprobada o rechazada más que revisando su panel manualmente,
 * así que approve()/reject() ahora también le avisan por correo.
 */
class DriversApprovalManagerNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_ADMIN_PRINCIPAL,
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    public function test_approving_a_driver_notifies_it_by_email(): void
    {
        Notification::fake();

        $admin = $this->createAdmin();
        $driver = Driver::factory()->create(['status' => Driver::STATUS_PENDING]);

        Livewire::actingAs($admin)
            ->test(DriversApprovalManager::class)
            ->call('approve', $driver->id);

        $this->assertSame(Driver::STATUS_ACTIVE, $driver->fresh()->status);

        Notification::assertSentTo($driver->user, AccountApproved::class);
        Notification::assertNotSentTo($driver->user, AccountRejected::class);
    }

    public function test_approving_a_driver_sets_both_verification_status_and_status(): void
    {
        Notification::fake();

        $admin = $this->createAdmin();
        $driver = Driver::factory()->create([
            'status' => Driver::STATUS_PENDING,
            'verification_status' => Driver::VERIFICATION_IN_REVIEW,
        ]);

        Livewire::actingAs($admin)
            ->test(DriversApprovalManager::class)
            ->call('approve', $driver->id);

        $driver->refresh();

        $this->assertSame(Driver::STATUS_ACTIVE, $driver->status);
        $this->assertSame(Driver::VERIFICATION_VERIFIED, $driver->verification_status);
        $this->assertNull($driver->verification_rejection_reason);
        $this->assertNotNull($driver->verification_reviewed_at);
    }

    public function test_rejecting_a_driver_notifies_it_by_email(): void
    {
        Notification::fake();

        $admin = $this->createAdmin();
        $driver = Driver::factory()->create(['status' => Driver::STATUS_PENDING]);

        Livewire::actingAs($admin)
            ->test(DriversApprovalManager::class)
            ->call('openReject', $driver->id)
            ->set('rejectionReason', 'La foto de la licencia está vencida.')
            ->call('reject');

        $driver->refresh();

        // reject() ya no toca el estado operativo (Fase 3): sigue
        // PENDIENTE, solo cambia verification_status.
        $this->assertSame(Driver::STATUS_PENDING, $driver->status);
        $this->assertSame(Driver::VERIFICATION_REJECTED, $driver->verification_status);
        $this->assertSame('La foto de la licencia está vencida.', $driver->verification_rejection_reason);

        Notification::assertSentTo($driver->user, AccountRejected::class, function (AccountRejected $notification) use ($driver) {
            $mail = $notification->toMail($driver->user);
            $lines = [...$mail->introLines, ...$mail->outroLines];

            return collect($lines)->contains(fn ($line) => str_contains($line, 'La foto de la licencia está vencida.'));
        });
        Notification::assertNotSentTo($driver->user, AccountApproved::class);
    }

    public function test_rejecting_a_driver_without_a_reason_fails_validation(): void
    {
        Notification::fake();

        $admin = $this->createAdmin();
        $driver = Driver::factory()->create([
            'status' => Driver::STATUS_PENDING,
            'verification_status' => Driver::VERIFICATION_PENDING,
        ]);

        Livewire::actingAs($admin)
            ->test(DriversApprovalManager::class)
            ->call('openReject', $driver->id)
            ->call('reject')
            ->assertHasErrors(['rejectionReason' => 'required']);

        $this->assertSame(Driver::VERIFICATION_PENDING, $driver->fresh()->verification_status);
        Notification::assertNotSentTo($driver->user, AccountRejected::class);
    }

    public function test_suspending_a_verified_driver_keeps_verification_status(): void
    {
        $admin = $this->createAdmin();
        $driver = Driver::factory()->create([
            'status' => Driver::STATUS_ACTIVE,
            'verification_status' => Driver::VERIFICATION_VERIFIED,
        ]);

        Livewire::actingAs($admin)
            ->test(DriversApprovalManager::class)
            ->call('suspend', $driver->id);

        $driver->refresh();

        $this->assertSame(Driver::STATUS_SUSPENDED, $driver->status);
        $this->assertSame(Driver::VERIFICATION_VERIFIED, $driver->verification_status);
        $this->assertFalse($driver->canOperate());
    }

    public function test_admin_cannot_activate_a_driver_that_is_not_verified(): void
    {
        $admin = $this->createAdmin();
        $driver = Driver::factory()->create([
            'status' => Driver::STATUS_SUSPENDED,
            'verification_status' => Driver::VERIFICATION_PENDING,
        ]);

        Livewire::actingAs($admin)
            ->test(DriversApprovalManager::class)
            ->call('activate', $driver->id);

        $this->assertSame(Driver::STATUS_SUSPENDED, $driver->fresh()->status);
    }
}
