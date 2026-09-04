<?php

namespace Tests\Feature;

use App\Models\AllyFinancialTransaction;
use App\Models\AllySettlement;
use App\Models\User;
use App\Services\AllyFinancialService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

class AllyFinancialServiceTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestPackages;

    protected AllyFinancialService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(AllyFinancialService::class);
    }

    public function test_package_commission_is_credited_automatically_and_is_idempotent(): void
    {
        $ally = $this->createAlly();

        $package = $this->createPackage($ally, [
            'commission_percentage_used' => 10.00,
            'commission_amount_usd' => 5.00,
        ]);

        // El PackageObserver ya debió generar el crédito al crear la guía.
        $this->assertSame(5.00, $this->service->getBalance($ally->id));

        $this->assertSame(
            1,
            AllyFinancialTransaction::query()
                ->where('ally_id', $ally->id)
                ->where('type', AllyFinancialTransaction::TYPE_COMMISSION)
                ->count()
        );

        // Llamar de nuevo al servicio para la misma guía NO debe duplicar el crédito.
        $this->service->recordPackageCommission($package);

        $this->assertSame(
            1,
            AllyFinancialTransaction::query()
                ->where('ally_id', $ally->id)
                ->where('type', AllyFinancialTransaction::TYPE_COMMISSION)
                ->count()
        );

        $this->assertSame(5.00, $this->service->getBalance($ally->id));
    }

    public function test_settlement_cannot_be_created_above_available_balance(): void
    {
        $ally = $this->createAlly();

        $this->createPackage($ally, [
            'commission_percentage_used' => 10.00,
            'commission_amount_usd' => 20.00,
        ]);

        $this->expectException(RuntimeException::class);

        $this->service->createSettlement(
            allyId: $ally->id,
            amountUsd: 100.00, // supera el saldo disponible de $20
            paymentMethod: null,
            reference: null,
            notes: null,
            userId: null,
        );
    }

    public function test_pending_settlement_reserves_balance_for_new_settlements(): void
    {
        $ally = $this->createAlly();

        $this->createPackage($ally, [
            'commission_percentage_used' => 10.00,
            'commission_amount_usd' => 20.00,
        ]);

        // Reservamos todo el saldo disponible con una primera solicitud.
        $this->service->createSettlement(
            allyId: $ally->id,
            amountUsd: 20.00,
            paymentMethod: null,
            reference: null,
            notes: null,
            userId: null,
        );

        // Una segunda solicitud ya no debería poder crearse: el saldo
        // disponible real es 0 porque la primera está pendiente.
        $this->expectException(RuntimeException::class);

        $this->service->createSettlement(
            allyId: $ally->id,
            amountUsd: 0.01,
            paymentMethod: null,
            reference: null,
            notes: null,
            userId: null,
        );
    }

    public function test_mark_settlement_paid_deducts_balance_and_cannot_be_processed_twice(): void
    {
        $ally = $this->createAlly();
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN_PRINCIPAL,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->createPackage($ally, [
            'commission_percentage_used' => 10.00,
            'commission_amount_usd' => 30.00,
        ]);

        $settlement = $this->service->createSettlement(
            allyId: $ally->id,
            amountUsd: 30.00,
            paymentMethod: 'transferencia',
            reference: 'REF-1',
            notes: null,
            userId: $admin->id,
        );

        $this->service->markSettlementPaid($settlement->id, $admin->id);

        $this->assertSame(0.00, $this->service->getBalance($ally->id));
        $this->assertSame(30.00, $this->service->getPaidAmount($ally->id));

        $this->expectException(RuntimeException::class);
        $this->service->markSettlementPaid($settlement->id, $admin->id);
    }

    public function test_reverse_settlement_restores_balance_and_cannot_be_reversed_twice(): void
    {
        $ally = $this->createAlly();
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN_PRINCIPAL,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->createPackage($ally, [
            'commission_percentage_used' => 10.00,
            'commission_amount_usd' => 15.00,
        ]);

        $settlement = $this->service->createSettlement(
            allyId: $ally->id,
            amountUsd: 15.00,
            paymentMethod: null,
            reference: null,
            notes: null,
            userId: $admin->id,
        );

        $this->service->markSettlementPaid($settlement->id, $admin->id);
        $this->assertSame(0.00, $this->service->getBalance($ally->id));

        $this->service->reverseSettlement($settlement->id, $admin->id, 'Error de monto');

        // El saldo vuelve a estar disponible para el aliado.
        $this->assertSame(15.00, $this->service->getBalance($ally->id));

        $this->assertSame(
            AllySettlement::STATUS_REVERSED,
            $settlement->fresh()->status
        );

        $this->expectException(RuntimeException::class);
        $this->service->reverseSettlement($settlement->id, $admin->id, 'Segundo intento');
    }

    public function test_debit_adjustment_cannot_exceed_available_balance(): void
    {
        $ally = $this->createAlly();

        $this->createPackage($ally, [
            'commission_percentage_used' => 10.00,
            'commission_amount_usd' => 5.00,
        ]);

        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN_PRINCIPAL,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->expectException(RuntimeException::class);

        $this->service->createAdjustment(
            allyId: $ally->id,
            amountUsd: 50.00,
            direction: AllyFinancialTransaction::DIRECTION_DEBIT,
            description: 'Ajuste que no debería aplicarse',
            adminUserId: $admin->id,
        );
    }

    public function test_credit_adjustment_increases_balance(): void
    {
        $ally = $this->createAlly();

        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN_PRINCIPAL,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->service->createAdjustment(
            allyId: $ally->id,
            amountUsd: 12.50,
            direction: AllyFinancialTransaction::DIRECTION_CREDIT,
            description: 'Bono por volumen',
            adminUserId: $admin->id,
        );

        $this->assertSame(12.50, $this->service->getBalance($ally->id));
    }
}
