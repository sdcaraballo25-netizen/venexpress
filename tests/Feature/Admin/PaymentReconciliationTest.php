<?php

namespace Tests\Feature\Admin;

use App\Models\Ally;
use App\Models\AllyFinancialTransaction;
use App\Models\Package;
use App\Models\PaymentOrder;
use App\Models\User;
use App\Services\AllyFinancialService;
use App\Services\PaymentReconciliationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * PaymentReconciliationService: conciliación de órdenes de pago.
 * Marca COD como liquidado y abona pagos de deuda al ledger del
 * aliado.
 */
class PaymentReconciliationTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestPackages;

    private function admin(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_ADMIN_PRINCIPAL,
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    private function codPackage(Ally $ally, float $codAmount = 25.00): Package
    {
        return $this->createPackage($ally, [
            'is_cod' => true,
            'cod_amount_usd' => $codAmount,
            'cod_status' => Package::COD_PENDIENTE,
        ]);
    }

    private function order(array $attributes): PaymentOrder
    {
        return PaymentOrder::query()->forceCreate(array_merge([
            'order_number' => 'PO-'.Str::upper(Str::random(8)),
            'payer_type' => 'user',
            'payer_id' => User::factory()->create()->id,
            'purpose' => PaymentOrder::PURPOSE_OTHER,
            'amount_usd' => 10,
            'payment_method' => PaymentOrder::METHOD_PAGO_MOVIL,
            'status' => PaymentOrder::STATUS_PROCESSING,
            'bank_reference' => 'REF-'.Str::upper(Str::random(10)),
        ], $attributes));
    }

    private function paymentCount(PaymentOrder $order): int
    {
        return AllyFinancialTransaction::query()
            ->where('payment_order_id', $order->id)
            ->where('type', AllyFinancialTransaction::TYPE_PAYMENT)
            ->count();
    }



    public function test_cod_order_is_confirmed_and_package_is_liquidated(): void
    {
        $admin = $this->admin();
        $package = $this->codPackage($this->createAlly(), 25.00);
        $order = $this->order([
            'purpose' => PaymentOrder::PURPOSE_COD,
            'package_id' => $package->id,
            'amount_usd' => 25.00,
        ]);

        $result = app(PaymentReconciliationService::class)
            ->reconcileConfirmedOrder($order, $admin->id);

        $this->assertSame(PaymentOrder::STATUS_CONFIRMED, $result->status);
        $this->assertNotNull($result->confirmed_at);
        $this->assertSame($admin->id, (int) $result->confirmed_by_user_id);

        $package->refresh();
        $this->assertSame(Package::COD_LIQUIDADO, $package->cod_status);
        $this->assertNotNull($package->cod_liquidated_at);
        $this->assertSame($admin->id, (int) $package->cod_collected_by_user_id);
    }

    public function test_cod_order_with_wrong_amount_is_rejected_without_changes(): void
    {
        $package = $this->codPackage($this->createAlly(), 25.00);
        $order = $this->order([
            'purpose' => PaymentOrder::PURPOSE_COD,
            'package_id' => $package->id,
            'amount_usd' => 20.00,
        ]);

        try {
            app(PaymentReconciliationService::class)->reconcileConfirmedOrder($order);
            $this->fail('Se esperaba RuntimeException por monto distinto.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('monto', $e->getMessage());
        }

        $this->assertSame(PaymentOrder::STATUS_PROCESSING, $order->fresh()->status);
        $this->assertSame(Package::COD_PENDIENTE, $package->fresh()->cod_status);
    }

    public function test_cod_order_for_non_cod_package_is_rejected(): void
    {
        $package = $this->createPackage($this->createAlly(), ['is_cod' => false]);
        $order = $this->order([
            'purpose' => PaymentOrder::PURPOSE_COD,
            'package_id' => $package->id,
        ]);

        $this->expectException(RuntimeException::class);

        app(PaymentReconciliationService::class)->reconcileConfirmedOrder($order);
    }

    public function test_cod_already_liquidated_cannot_be_paid_again(): void
    {
        $package = $this->codPackage($this->createAlly(), 25.00);
        $package->forceFill(['cod_status' => Package::COD_LIQUIDADO])->save();

        $order = $this->order([
            'purpose' => PaymentOrder::PURPOSE_COD,
            'package_id' => $package->id,
            'amount_usd' => 25.00,
        ]);

        $this->expectException(RuntimeException::class);

        app(PaymentReconciliationService::class)->reconcileConfirmedOrder($order);
    }

    public function test_order_without_bank_reference_is_rejected(): void
    {
        $order = $this->order(['bank_reference' => null]);

        $this->expectException(RuntimeException::class);

        app(PaymentReconciliationService::class)->reconcileConfirmedOrder($order);
    }

    public function test_closed_orders_cannot_be_reconciled(): void
    {
        foreach ([
            PaymentOrder::STATUS_REJECTED,
            PaymentOrder::STATUS_EXPIRED,
            PaymentOrder::STATUS_REVERSED,
        ] as $status) {
            $order = $this->order(['status' => $status]);

            try {
                app(PaymentReconciliationService::class)->reconcileConfirmedOrder($order);
                $this->fail("Una orden {$status} no debe conciliarse.");
            } catch (RuntimeException) {
                $this->assertSame($status, $order->fresh()->status);
            }
        }
    }

    public function test_ally_debt_payment_credits_ledger_once_and_is_idempotent(): void
    {
        $ally = $this->createAlly();
        $order = $this->order([
            'purpose' => PaymentOrder::PURPOSE_ALLY_DEBT,
            'ally_id' => $ally->id,
            'amount_usd' => 40.00,
        ]);

        $service = app(PaymentReconciliationService::class);
        $balanceBefore = app(AllyFinancialService::class)->getBalance($ally->id);

        $service->reconcileConfirmedOrder($order);
        $service->reconcileConfirmedOrder($order->fresh());

        $this->assertSame(PaymentOrder::STATUS_CONFIRMED, $order->fresh()->status);
        $this->assertSame(1, $this->paymentCount($order));
        $this->assertSame(
            round($balanceBefore + 40.00, 2),
            app(AllyFinancialService::class)->getBalance($ally->id)
        );
    }

    public function test_ally_debt_order_without_ally_is_rejected(): void
    {
        $order = $this->order([
            'purpose' => PaymentOrder::PURPOSE_ALLY_DEBT,
            'ally_id' => null,
        ]);

        $this->expectException(RuntimeException::class);

        app(PaymentReconciliationService::class)->reconcileConfirmedOrder($order);
    }
}
