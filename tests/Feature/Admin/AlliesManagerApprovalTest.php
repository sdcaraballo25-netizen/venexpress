<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\AlliesManager;
use App\Models\Ally;
use App\Models\User;
use App\Notifications\AccountApproved;
use App\Notifications\AccountRejected;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * El aliado no tiene otra forma de enterarse de que su solicitud fue
 * aprobada o rechazada más que revisando su panel manualmente, así
 * que approve()/reject() ahora también le avisan por correo.
 */
class AlliesManagerApprovalTest extends TestCase
{
    use CreatesTestPackages;
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_ADMIN_PRINCIPAL,
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    public function test_approving_an_ally_notifies_its_owner_by_email(): void
    {
        Notification::fake();

        $admin = $this->createAdmin();
        $ally = $this->createAlly(['status' => Ally::STATUS_PENDING]);

        Livewire::actingAs($admin)
            ->test(AlliesManager::class)
            ->call('approve', $ally->id);

        $this->assertSame(Ally::STATUS_ACTIVE, $ally->fresh()->status);

        Notification::assertSentTo($ally->user, AccountApproved::class);
        Notification::assertNotSentTo($ally->user, AccountRejected::class);
    }

    public function test_approving_an_ally_sets_both_verification_status_and_status(): void
    {
        Notification::fake();

        $admin = $this->createAdmin();
        $ally = $this->createAlly([
            'status' => Ally::STATUS_PENDING,
            'verification_status' => Ally::VERIFICATION_IN_REVIEW,
        ]);

        Livewire::actingAs($admin)
            ->test(AlliesManager::class)
            ->call('approve', $ally->id);

        $ally->refresh();

        $this->assertSame(Ally::STATUS_ACTIVE, $ally->status);
        $this->assertSame(Ally::VERIFICATION_VERIFIED, $ally->verification_status);
        $this->assertNull($ally->verification_rejection_reason);
        $this->assertNotNull($ally->verification_reviewed_at);
    }

    public function test_suspending_a_verified_ally_keeps_verification_status(): void
    {
        $admin = $this->createAdmin();
        $ally = $this->createAlly([
            'status' => Ally::STATUS_ACTIVE,
            'verification_status' => Ally::VERIFICATION_VERIFIED,
        ]);

        Livewire::actingAs($admin)
            ->test(AlliesManager::class)
            ->call('suspend', $ally->id);

        $ally->refresh();

        $this->assertSame(Ally::STATUS_SUSPENDED, $ally->status);
        $this->assertSame(Ally::VERIFICATION_VERIFIED, $ally->verification_status);
        $this->assertFalse($ally->canOperate());
    }

    public function test_admin_cannot_activate_an_ally_that_is_not_verified(): void
    {
        $admin = $this->createAdmin();
        $ally = $this->createAlly([
            'status' => Ally::STATUS_SUSPENDED,
            'verification_status' => Ally::VERIFICATION_PENDING,
        ]);

        Livewire::actingAs($admin)
            ->test(AlliesManager::class)
            ->call('activate', $ally->id);

        $this->assertSame(Ally::STATUS_SUSPENDED, $ally->fresh()->status);
    }

    public function test_rejecting_an_ally_notifies_its_owner_by_email(): void
    {
        Notification::fake();

        $admin = $this->createAdmin();
        $ally = $this->createAlly(['status' => Ally::STATUS_PENDING]);

        Livewire::actingAs($admin)
            ->test(AlliesManager::class)
            ->call('openReject', $ally->id)
            ->set('rejectionReason', 'La foto de la cédula está borrosa.')
            ->call('reject');

        $ally->refresh();

        // reject() ya no toca el estado operativo (Fase 3): sigue
        // PENDIENTE, solo cambia verification_status.
        $this->assertSame(Ally::STATUS_PENDING, $ally->status);
        $this->assertSame(Ally::VERIFICATION_REJECTED, $ally->verification_status);
        $this->assertSame('La foto de la cédula está borrosa.', $ally->verification_rejection_reason);

        Notification::assertSentTo($ally->user, AccountRejected::class, function (AccountRejected $notification) use ($ally) {
            $mail = $notification->toMail($ally->user);
            $lines = [...$mail->introLines, ...$mail->outroLines];

            return collect($lines)->contains(fn ($line) => str_contains($line, 'La foto de la cédula está borrosa.'));
        });
        Notification::assertNotSentTo($ally->user, AccountApproved::class);
    }

    public function test_rejecting_an_ally_without_a_reason_fails_validation(): void
    {
        Notification::fake();

        $admin = $this->createAdmin();
        $ally = $this->createAlly([
            'status' => Ally::STATUS_PENDING,
            'verification_status' => Ally::VERIFICATION_PENDING,
        ]);

        Livewire::actingAs($admin)
            ->test(AlliesManager::class)
            ->call('openReject', $ally->id)
            ->call('reject')
            ->assertHasErrors(['rejectionReason' => 'required']);

        $this->assertSame(Ally::VERIFICATION_PENDING, $ally->fresh()->verification_status);
        Notification::assertNotSentTo($ally->user, AccountRejected::class);
    }
}
