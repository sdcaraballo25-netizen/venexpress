<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\UsersManager;
use App\Models\Ally;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * toggleStatus() ya sincronizaba Driver::status al desactivar/
 * reactivar un repartidor, pero no hacía lo mismo con Ally::status
 * para un aliado — quedaba desactivado en users.status (por lo que de
 * todas formas no podía entrar, bloqueado por EnsureUserHasRole) pero
 * "ACTIVO" en Gestión de Aliados, lo cual es engañoso para un Admin.
 */
class UsersManagerToggleStatusTest extends TestCase
{
    use CreatesTestPackages;
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_ADMIN_PRINCIPAL,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);
    }

    public function test_deactivating_an_ally_user_also_suspends_their_ally_record(): void
    {
        $admin = $this->createAdmin();
        $ally = $this->createAlly(['status' => Ally::STATUS_ACTIVE]);

        Livewire::actingAs($admin)
            ->test(UsersManager::class)
            ->call('toggleStatus', $ally->user_id);

        $ally->refresh();

        $this->assertSame(User::STATUS_INACTIVE, $ally->user->status);
        $this->assertSame(Ally::STATUS_SUSPENDED, $ally->status);
    }

    public function test_reactivating_an_ally_user_also_activates_their_ally_record(): void
    {
        $admin = $this->createAdmin();
        $ally = $this->createAlly(['status' => Ally::STATUS_SUSPENDED]);
        $ally->user->update(['status' => User::STATUS_INACTIVE]);

        Livewire::actingAs($admin)
            ->test(UsersManager::class)
            ->call('toggleStatus', $ally->user_id);

        $ally->refresh();

        $this->assertSame(User::STATUS_ACTIVE, $ally->user->status);
        $this->assertSame(Ally::STATUS_ACTIVE, $ally->status);
    }

    public function test_deactivating_a_driver_user_still_suspends_their_driver_record(): void
    {
        $admin = $this->createAdmin();
        $driverUser = User::factory()->create([
            'role' => User::ROLE_REPARTIDOR,
            'status' => User::STATUS_ACTIVE,
        ]);
        $driver = Driver::factory()->create([
            'user_id' => $driverUser->id,
            'status' => Driver::STATUS_ACTIVE,
        ]);

        Livewire::actingAs($admin)
            ->test(UsersManager::class)
            ->call('toggleStatus', $driver->user_id);

        $this->assertSame(Driver::STATUS_SUSPENDED, $driver->fresh()->status);
    }
}
