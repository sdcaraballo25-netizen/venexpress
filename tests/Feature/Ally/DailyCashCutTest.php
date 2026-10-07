<?php

namespace Tests\Feature\Ally;

use App\Livewire\Ally\DailyCashCut;
use App\Models\AllySettlement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Corte de caja del Aliado Administrador (/ally/corte-caja): muestra
 * su saldo de comisiones y le deja solicitar una liquidación, que
 * queda pendiente hasta que administración la pague.
 */
class DailyCashCutTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestPackages;

    public function test_ally_sees_the_balance_of_their_commissions(): void
    {
        $ally = $this->createAlly();
        $this->createPackage($ally, ['commission_amount_usd' => 12.50]);

        // La comisión de otra agencia no debe aparecer en este corte.
        $this->createPackage($this->createAlly(), ['commission_amount_usd' => 99.00]);

        Livewire::actingAs($ally->user)
            ->test(DailyCashCut::class)
            ->assertViewHas('balance', 12.50)
            ->assertViewHas('generated', fn ($generated) => (float) $generated === 12.50);
    }

    public function test_ally_requests_a_settlement_within_the_balance(): void
    {
        $ally = $this->createAlly();
        $this->createPackage($ally, ['commission_amount_usd' => 30.00]);

        Livewire::actingAs($ally->user)
            ->test(DailyCashCut::class)
            ->set('amountUsd', '20')
            ->set('paymentMethod', 'pago_movil')
            ->set('paymentReference', 'REF-ALIADO-1')
            ->call('requestSettlement')
            ->assertHasNoErrors()
            ->assertSet('amountUsd', '');

        $settlement = AllySettlement::query()->where('ally_id', $ally->id)->sole();

        $this->assertSame(AllySettlement::STATUS_PENDING, $settlement->status);
        $this->assertSame(20.00, (float) $settlement->amount_usd);
        $this->assertSame('pago_movil', $settlement->payment_method);
    }

    public function test_settlement_above_the_balance_is_rejected(): void
    {
        $ally = $this->createAlly();
        $this->createPackage($ally, ['commission_amount_usd' => 10.00]);

        Livewire::actingAs($ally->user)
            ->test(DailyCashCut::class)
            ->set('amountUsd', '50')
            ->call('requestSettlement')
            ->assertHasErrors('amountUsd');

        $this->assertSame(0, AllySettlement::query()->where('ally_id', $ally->id)->count());
    }

    public function test_pending_settlement_blocks_requesting_the_same_balance_twice(): void
    {
        $ally = $this->createAlly();
        $this->createPackage($ally, ['commission_amount_usd' => 10.00]);

        $component = Livewire::actingAs($ally->user)->test(DailyCashCut::class);

        $component->set('amountUsd', '10')->call('requestSettlement')->assertHasNoErrors();
        $component->set('amountUsd', '10')->call('requestSettlement')->assertHasErrors('amountUsd');

        $this->assertSame(1, AllySettlement::query()->where('ally_id', $ally->id)->count());
    }

    public function test_request_validates_amount_and_payment_method(): void
    {
        $ally = $this->createAlly();

        Livewire::actingAs($ally->user)
            ->test(DailyCashCut::class)
            ->set('amountUsd', '0')
            ->set('paymentMethod', 'criptomonedas')
            ->call('requestSettlement')
            ->assertHasErrors(['amountUsd' => 'min', 'paymentMethod' => 'in']);
    }

    public function test_ally_owner_can_open_the_page(): void
    {
        $ally = $this->createAlly();

        $this->actingAs($ally->user)
            ->get(route('ally.cash-cut'))
            ->assertOk();
    }

    public function test_taquilla_cannot_open_the_cash_cut(): void
    {
        $ally = $this->createAlly();
        $staff = User::factory()->create([
            'role' => User::ROLE_ALIADO_TAQUILLA,
            'status' => User::STATUS_ACTIVE,
            'ally_id' => $ally->id,
        ]);

        $this->actingAs($staff)
            ->get(route('ally.cash-cut'))
            ->assertForbidden();
    }
}
