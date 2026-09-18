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

    public function test_rejecting_a_driver_notifies_it_by_email(): void
    {
        Notification::fake();

        $admin = $this->createAdmin();
        $driver = Driver::factory()->create(['status' => Driver::STATUS_PENDING]);

        Livewire::actingAs($admin)
            ->test(DriversApprovalManager::class)
            ->call('reject', $driver->id);

        $this->assertSame(Driver::STATUS_REJECTED, $driver->fresh()->status);

        Notification::assertSentTo($driver->user, AccountRejected::class);
        Notification::assertNotSentTo($driver->user, AccountApproved::class);
    }
}
