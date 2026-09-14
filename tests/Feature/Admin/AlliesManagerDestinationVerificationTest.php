<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\AlliesManager;
use App\Models\Ally;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Fase 1: estructura preparatoria para "Aliado verificado como punto
 * final de entrega/retiro". No implementa el flujo documental
 * completo (eso queda para una fase posterior) — solo la capacidad
 * mínima de que el Admin active/desactive el flag manualmente, de
 * forma explícitamente marcada como temporal.
 */
class AlliesManagerDestinationVerificationTest extends TestCase
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

    public function test_new_ally_defaults_to_not_verified_as_destination(): void
    {
        $ally = $this->createAlly();

        $this->assertFalse($ally->fresh()->is_verified_destination);
        $this->assertNull($ally->fresh()->destination_verification_status);
        $this->assertNull($ally->fresh()->destination_verified_at);
        $this->assertFalse($ally->fresh()->isVerifiedDestination());
    }

    public function test_admin_can_manually_mark_an_ally_as_verified_destination(): void
    {
        $admin = $this->createAdmin();
        $ally = $this->createAlly();

        Livewire::actingAs($admin)
            ->test(AlliesManager::class)
            ->call('toggleVerifiedDestination', $ally->id);

        $ally->refresh();

        $this->assertTrue($ally->is_verified_destination);
        $this->assertSame(Ally::DESTINATION_VERIFICATION_APPROVED, $ally->destination_verification_status);
        $this->assertNotNull($ally->destination_verified_at);
    }

    public function test_admin_can_revert_the_manual_verification(): void
    {
        $admin = $this->createAdmin();
        $ally = $this->createAlly([
            'is_verified_destination' => true,
            'destination_verification_status' => Ally::DESTINATION_VERIFICATION_APPROVED,
            'destination_verified_at' => now(),
        ]);

        Livewire::actingAs($admin)
            ->test(AlliesManager::class)
            ->call('toggleVerifiedDestination', $ally->id);

        $ally->refresh();

        $this->assertFalse($ally->is_verified_destination);
        $this->assertNull($ally->destination_verification_status);
        $this->assertNull($ally->destination_verified_at);
    }

    public function test_toggling_verification_is_recorded_in_the_audit_log(): void
    {
        $admin = $this->createAdmin();
        $ally = $this->createAlly();

        Livewire::actingAs($admin)
            ->test(AlliesManager::class)
            ->call('toggleVerifiedDestination', $ally->id);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'ally.destination_verification_toggled',
            'target_type' => Ally::class,
            'target_id' => $ally->id,
        ]);
    }
}
