<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\BcvRateManager;
use App\Models\BcvRate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BcvRateManagerTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_ADMIN_PRINCIPAL,
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    /**
     * Antes, guardar SIEMPRE fijaba effective_at = now(), sin importar
     * qué effective_date eligió el admin. Backfillear una corrección
     * de una fecha pasada volvía esa fila "vigente" al instante,
     * pisando la tasa real de hoy.
     */
    public function test_backfilling_a_past_date_does_not_override_todays_current_rate(): void
    {
        $admin = $this->createAdmin();

        $today = BcvRate::create([
            'rate' => 200.00,
            'effective_date' => now()->toDateString(),
            'effective_at' => now(),
            'source' => 'manual',
        ]);

        Livewire::actingAs($admin)
            ->test(BcvRateManager::class)
            ->set('rate', 190.00)
            ->set('effective_date', now()->subDays(3)->toDateString())
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame($today->id, BcvRate::current()->id);
        $this->assertEquals(200.00, (float) BcvRate::current()->rate);
    }

    /**
     * Una tasa registrada para hoy sí debe poder desplazar a una
     * sincronización automática anterior del mismo día.
     */
    public function test_a_manual_correction_for_today_becomes_the_current_rate(): void
    {
        $admin = $this->createAdmin();

        BcvRate::create([
            'rate' => 150.00,
            'effective_date' => now()->toDateString(),
            'effective_at' => now()->subHours(2),
            'source' => 'api',
        ]);

        Livewire::actingAs($admin)
            ->test(BcvRateManager::class)
            ->set('rate', 155.00)
            ->set('effective_date', now()->toDateString())
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals(155.00, (float) BcvRate::current()->rate);
    }

    /**
     * Corregir solo el rate de una fila pasada (sin cambiar su fecha)
     * no debe destruir su effective_at preciso: si ese día tuvo dos
     * publicaciones (mañana y tarde), la corrección de la mañana no
     * debe terminar ordenando después de la tarde.
     */
    public function test_editing_only_the_rate_of_a_past_day_preserves_its_precise_effective_at(): void
    {
        $admin = $this->createAdmin();

        $morning = now()->subDays(2)->setTime(8, 3);
        $afternoon = now()->subDays(2)->setTime(15, 0);

        $morningRate = BcvRate::create([
            'rate' => 180.00,
            'effective_date' => $morning->toDateString(),
            'effective_at' => $morning,
            'source' => 'api',
        ]);

        BcvRate::create([
            'rate' => 185.00,
            'effective_date' => $afternoon->toDateString(),
            'effective_at' => $afternoon,
            'source' => 'api',
        ]);

        Livewire::actingAs($admin)
            ->test(BcvRateManager::class)
            ->call('edit', $morningRate->id)
            ->set('rate', 180.50)
            ->call('save')
            ->assertHasNoErrors();

        $morningRate->refresh();
        $this->assertEquals(180.50, (float) $morningRate->rate);
        $this->assertTrue($morningRate->effective_at->equalTo($morning));

        // La tasa de la tarde sigue siendo la vigente de ese día.
        $this->assertEquals(185.00, (float) BcvRate::current()->rate);
    }

    /**
     * Antes, delete() solo bloqueaba borrar cuando quedaba una sola
     * fila en la tabla — no cuando la fila a borrar era la vigente.
     */
    public function test_cannot_delete_the_currently_active_rate(): void
    {
        $admin = $this->createAdmin();

        BcvRate::create([
            'rate' => 150.00,
            'effective_date' => now()->subDay()->toDateString(),
            'effective_at' => now()->subDay(),
            'source' => 'api',
        ]);

        $current = BcvRate::create([
            'rate' => 200.00,
            'effective_date' => now()->toDateString(),
            'effective_at' => now(),
            'source' => 'api',
        ]);

        Livewire::actingAs($admin)
            ->test(BcvRateManager::class)
            ->call('delete', $current->id);

        $this->assertDatabaseHas('bcv_rates', ['id' => $current->id]);
        $this->assertEquals(200.00, (float) BcvRate::current()->rate);
    }

    public function test_can_delete_a_rate_that_is_not_the_currently_active_one(): void
    {
        $admin = $this->createAdmin();

        $old = BcvRate::create([
            'rate' => 150.00,
            'effective_date' => now()->subDay()->toDateString(),
            'effective_at' => now()->subDay(),
            'source' => 'api',
        ]);

        BcvRate::create([
            'rate' => 200.00,
            'effective_date' => now()->toDateString(),
            'effective_at' => now(),
            'source' => 'api',
        ]);

        Livewire::actingAs($admin)
            ->test(BcvRateManager::class)
            ->call('delete', $old->id);

        $this->assertDatabaseMissing('bcv_rates', ['id' => $old->id]);
    }
}
