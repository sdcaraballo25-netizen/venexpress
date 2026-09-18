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

    public function test_rejecting_an_ally_notifies_its_owner_by_email(): void
    {
        Notification::fake();

        $admin = $this->createAdmin();
        $ally = $this->createAlly(['status' => Ally::STATUS_PENDING]);

        Livewire::actingAs($admin)
            ->test(AlliesManager::class)
            ->call('reject', $ally->id);

        $this->assertSame(Ally::STATUS_REJECTED, $ally->fresh()->status);

        Notification::assertSentTo($ally->user, AccountRejected::class);
        Notification::assertNotSentTo($ally->user, AccountApproved::class);
    }
}
