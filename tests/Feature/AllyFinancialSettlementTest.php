<?php

namespace Tests\Feature;

use App\Models\AllyFinancialTransaction;
use App\Models\AllySettlement;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\AllyFinancialService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use RuntimeException;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Cubre los hallazgos de auditoría #1 (sin pruebas sobre dinero) y
 * #2 (sin auditoría en liquidaciones/reversos de AllyFinancialService).
 * Cada operación financiera aquí probada debe dejar un AuditLog.
 */
class AllyFinancialSettlementTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestPackages;

    protected AllyFinancialService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(AllyFinancialService::class);
    }

    public function test_settlement_cannot_exceed_available_balance(): void
    {
        $ally = $this->createAlly();
        $admin = User::factory()->create();

        $this->createPackage($ally, [
            'commission_percentage_used' => 10.00,
            'commission_amount_usd' => 5.00,
        ]);

        $this->expectException(RuntimeException::class);

        $this->service->createSettlement($ally->id, 50.00, null, null, null, $admin->id);
    }

    public function test_create_settlement_is_audited(): void
    {
        $ally = $this->createAlly();
        $admin = User::factory()->create();

        $this->createPackage($ally, [
            'commission_percentage_used' => 10.00,
            'commission_amount_usd' => 5.00,
        ]);

        $settlement = $this->service->createSettlement($ally->id, 5.00, null, null, null, $admin->id);

        $this->assertSame(1, AuditLog::query()
            ->where('action', 'ally_settlement.requested')
            ->where('target_id', $settlement->id)
            ->count());
    }

    public function test_full_settlement_lifecycle_is_audited_and_moves_balance(): void
    {
        $ally = $this->createAlly();
        $admin = User::factory()->create();

        $this->createPackage($ally, [
            'commission_percentage_used' => 10.00,
            'commission_amount_usd' => 20.00,
        ]);

        $this->assertSame(20.00, $this->service->getBalance($ally->id));

        $settlement = $this->service->createSettlement($ally->id, 20.00, 'transferencia', 'REF-1', null, $admin->id);

        // Una solicitud pendiente todavía no descuenta el saldo real.
        $this->assertSame(20.00, $this->service->getBalance($ally->id));

        $paid = $this->service->markSettlementPaid($settlement->id, $admin->id);

        $this->assertTrue($paid->isPaid());
        $this->assertSame(0.00, $this->service->getBalance($ally->id));

        $this->assertSame(1, AuditLog::query()
            ->where('action', 'ally_settlement.paid')
            ->where('target_id', $settlement->id)
            ->count());

        $reversed = $this->service->reverseSettlement($settlement->id, $admin->id, 'Pago duplicado por error');

        $this->assertSame(AllySettlement::STATUS_REVERSED, $reversed->status);
        $this->assertSame(20.00, $this->service->getBalance($ally->id));

        $this->assertSame(1, AuditLog::query()
            ->where('action', 'ally_settlement.reversed')
            ->where('target_id', $settlement->id)
            ->count());

        // El reverso no debe duplicarse.
        $this->expectException(RuntimeException::class);
        $this->service->reverseSettlement($settlement->id, $admin->id);
    }

    public function test_cancel_settlement_is_audited_and_does_not_touch_balance(): void
    {
        $ally = $this->createAlly();
        $admin = User::factory()->create();

        $this->createPackage($ally, [
            'commission_percentage_used' => 10.00,
            'commission_amount_usd' => 10.00,
        ]);

        $settlement = $this->service->createSettlement($ally->id, 10.00, null, null, null, $admin->id);

        $cancelled = $this->service->cancelSettlement($settlement->id, $admin->id);

        $this->assertSame(AllySettlement::STATUS_CANCELLED, $cancelled->status);
        $this->assertSame(10.00, $this->service->getBalance($ally->id));

        $this->assertSame(1, AuditLog::query()
            ->where('action', 'ally_settlement.cancelled')
            ->where('target_id', $settlement->id)
            ->count());
    }

    public function test_manual_adjustment_is_audited(): void
    {
        $ally = $this->createAlly();
        $admin = User::factory()->create();

        $adjustment = $this->service->createAdjustment(
            $ally->id,
            15.00,
            AllyFinancialTransaction::DIRECTION_CREDIT,
            'Bono de bienvenida',
            $admin->id,
        );

        $this->assertSame(15.00, $this->service->getBalance($ally->id));

        $this->assertSame(1, AuditLog::query()
            ->where('action', 'ally_financial.adjustment_created')
            ->where('target_id', $adjustment->id)
            ->count());
    }

    public function test_debit_adjustment_cannot_exceed_balance(): void
    {
        $ally = $this->createAlly();
        $admin = User::factory()->create();

        $this->expectException(RuntimeException::class);

        $this->service->createAdjustment(
            $ally->id,
            15.00,
            AllyFinancialTransaction::DIRECTION_DEBIT,
            'Corrección',
            $admin->id,
        );
    }

    public function test_adjustment_requires_a_description(): void
    {
        $ally = $this->createAlly();
        $admin = User::factory()->create();

        $this->expectException(InvalidArgumentException::class);

        $this->service->createAdjustment(
            $ally->id,
            15.00,
            AllyFinancialTransaction::DIRECTION_CREDIT,
            '',
            $admin->id,
        );
    }
}
