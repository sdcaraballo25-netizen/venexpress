<?php

namespace Tests\Feature\Admin;

use App\Exports\SimpleArrayExport;
use App\Livewire\Admin\FranchiseReceivables;
use App\Livewire\Ally\FranchiseFinance;
use App\Models\Ally;
use App\Models\FranchiseInvoice;
use App\Models\FranchisePayment;
use App\Models\User;
use App\Services\FranchiseBillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Cuentas por cobrar de franquicias: cuota mensual = % de las ventas
 * del mes que fija el Admin; pagos registrados manualmente con N° de
 * factura y voucher; saldo a favor / pendiente; reporte en Excel
 * general o por franquicia.
 */
class FranchiseReceivablesTest extends TestCase
{
    use CreatesTestPackages;
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN_PRINCIPAL, 'status' => User::STATUS_ACTIVE]);
    }

    private function franchise(float $fee = 10, string $name = 'Franquicia Centro'): Ally
    {
        $ally = $this->createAlly([
            'business_name' => $name,
            'commission_percentage' => 0,
            'franchise_fee_percentage' => $fee,
        ]);
        $ally->user->update(['role' => User::ROLE_FRANQUICIADO, 'account_verified_at' => now()]);

        return $ally->fresh();
    }

    /** Ventas de la franquicia en el mes pasado por $total. */
    private function salesLastMonth(Ally $ally, float $total): void
    {
        $package = $this->createPackage($ally, ['total_price_usd' => $total]);
        $package->forceFill(['created_at' => now()->subMonthNoOverflow()->startOfMonth()->addDays(3)])->save();
    }

    public function test_only_admins_can_open_the_receivables_page(): void
    {
        $this->actingAs($this->admin())->get(route('admin.franchise-receivables'))->assertOk();

        $franchise = $this->franchise();
        $this->actingAs($franchise->user)->get(route('admin.franchise-receivables'))->assertForbidden();
    }

    public function test_expected_fee_is_the_percentage_of_the_monthly_sales(): void
    {
        $franchise = $this->franchise(fee: 10);
        $this->salesLastMonth($franchise, 500);
        $this->salesLastMonth($franchise, 300);

        // Venta de este mes: no entra en el mes pasado.
        $this->createPackage($franchise, ['total_price_usd' => 999]);

        $billing = app(FranchiseBillingService::class);
        $lastMonth = now()->subMonthNoOverflow();

        $this->assertSame(800.0, $billing->monthlySales($franchise, $lastMonth));
        $this->assertSame(80.0, $billing->expectedFee($franchise, $lastMonth));

        $row = $billing->statement($franchise)->firstWhere('key', $lastMonth->format('Y-m'));
        $this->assertTrue($row['pending_invoice']);
        $this->assertSame(80.0, $row['expected']);
    }

    public function test_choosing_the_period_proposes_the_calculated_fee_as_invoice_amount(): void
    {
        $franchise = $this->franchise(fee: 10);
        $this->salesLastMonth($franchise, 800);

        Livewire::actingAs($this->admin())
            ->test(FranchiseReceivables::class)
            ->call('selectAlly', $franchise->id)
            ->set('period', now()->subMonthNoOverflow()->format('Y-m'))
            ->assertSet('invoiceAmount', '80.00');
    }

    public function test_admin_registers_a_partial_payment_and_the_franchise_owes_the_rest(): void
    {
        $franchise = $this->franchise(fee: 10);
        $this->salesLastMonth($franchise, 800);
        $period = now()->subMonthNoOverflow()->format('Y-m');

        Livewire::actingAs($this->admin())
            ->test(FranchiseReceivables::class)
            ->call('selectAlly', $franchise->id)
            ->set('period', $period)
            ->set('invoiceNumber', 'F-0001')
            ->set('invoiceAmount', '80')
            ->set('amountPaid', '50')
            ->set('paymentReference', 'VOUCHER-123')
            ->set('paidOn', now()->toDateString())
            ->call('registerPayment')
            ->assertHasNoErrors();

        $invoice = FranchiseInvoice::where('invoice_number', 'F-0001')->firstOrFail();

        $this->assertSame(80.0, (float) $invoice->invoice_amount_usd);
        $this->assertSame(800.0, (float) $invoice->sales_total_usd);
        $this->assertSame(10.0, (float) $invoice->fee_percentage);
        $this->assertSame(80.0, (float) $invoice->expected_amount_usd);
        $this->assertSame(-30.0, $invoice->balance());
        $this->assertDatabaseHas('franchise_payments', [
            'franchise_invoice_id' => $invoice->id,
            'payment_reference' => 'VOUCHER-123',
            'amount_paid_usd' => 50,
        ]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'franchise.payment_recorded']);

        $this->assertSame(-30.0, app(FranchiseBillingService::class)->balance($franchise));
    }

    public function test_a_second_payment_with_the_same_invoice_number_is_an_installment_and_can_leave_credit(): void
    {
        $franchise = $this->franchise(fee: 10);
        $period = now()->subMonthNoOverflow()->format('Y-m');
        $billing = app(FranchiseBillingService::class);

        $base = ['period' => $period, 'invoice_number' => 'F-0002', 'notes' => null, 'paid_on' => now()->toDateString()];

        $billing->registerPayment($franchise, $base + ['invoice_amount_usd' => 100, 'amount_paid_usd' => 60, 'payment_reference' => 'V-1'], null);
        // El monto de factura del segundo pago se ignora: es un abono.
        $billing->registerPayment($franchise, $base + ['invoice_amount_usd' => 999, 'amount_paid_usd' => 50, 'payment_reference' => 'V-2'], null);

        $this->assertSame(1, FranchiseInvoice::count());
        $this->assertSame(2, FranchisePayment::count());

        $invoice = FranchiseInvoice::firstOrFail();
        $this->assertSame(100.0, (float) $invoice->invoice_amount_usd);
        $this->assertSame(110.0, $invoice->paidTotal());
        // Pagó $10 de más: quedan a favor del franquiciado.
        $this->assertSame(10.0, $invoice->balance());
        $this->assertSame(10.0, $billing->balance($franchise));
    }

    public function test_an_existing_invoice_number_cannot_be_reused_for_another_month(): void
    {
        $franchise = $this->franchise();
        $billing = app(FranchiseBillingService::class);

        $billing->registerPayment($franchise, [
            'period' => now()->subMonthsNoOverflow(2)->format('Y-m'), 'invoice_number' => 'F-9',
            'invoice_amount_usd' => 50, 'amount_paid_usd' => 50, 'payment_reference' => 'V',
            'paid_on' => now()->toDateString(), 'notes' => null,
        ], null);

        Livewire::actingAs($this->admin())
            ->test(FranchiseReceivables::class)
            ->call('selectAlly', $franchise->id)
            ->set('invoiceNumber', 'F-9')
            ->set('period', now()->subMonthNoOverflow()->format('Y-m'))
            ->set('amountPaid', '10')
            ->set('paymentReference', 'V-OTRO')
            ->call('registerPayment')
            ->assertHasErrors('invoiceNumber');

        $this->assertSame(1, FranchisePayment::count());
    }

    public function test_payment_requires_invoice_number_reference_and_invoice_amount(): void
    {
        $franchise = $this->franchise();

        Livewire::actingAs($this->admin())
            ->test(FranchiseReceivables::class)
            ->call('selectAlly', $franchise->id)
            ->set('amountPaid', '10')
            ->call('registerPayment')
            ->assertHasErrors(['invoiceNumber', 'invoiceAmount', 'paymentReference']);

        $this->assertSame(0, FranchisePayment::count());
    }

    public function test_regular_allies_are_not_listed_and_cannot_be_selected(): void
    {
        $franchise = $this->franchise(name: 'Franquicia Listada');
        $ally = $this->createAlly(['business_name' => 'Aliado No Listado']);

        $component = Livewire::actingAs($this->admin())
            ->test(FranchiseReceivables::class)
            ->assertSee('Franquicia Listada')
            ->assertDontSee('Aliado No Listado');

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $component->call('selectAlly', $ally->id);
    }

    public function test_admin_updates_the_fee_percentage_with_audit(): void
    {
        $franchise = $this->franchise(fee: 10);

        Livewire::actingAs($this->admin())
            ->test(FranchiseReceivables::class)
            ->call('selectAlly', $franchise->id)
            ->set('feePercentage', '12.5')
            ->call('saveFee')
            ->assertHasNoErrors();

        $this->assertSame(12.5, (float) $franchise->fresh()->franchise_fee_percentage);
        $this->assertDatabaseHas('audit_logs', ['action' => 'franchise.fee_updated', 'target_id' => $franchise->id]);
    }

    public function test_export_includes_all_franchises_or_only_the_selected_one(): void
    {
        Excel::fake();

        $a = $this->franchise(name: 'Franquicia A');
        $b = $this->franchise(name: 'Franquicia B');
        $billing = app(FranchiseBillingService::class);
        $period = now()->subMonthNoOverflow()->format('Y-m');

        $billing->registerPayment($a, [
            'period' => $period, 'invoice_number' => 'A-1', 'invoice_amount_usd' => 100,
            'amount_paid_usd' => 70, 'payment_reference' => 'VA', 'paid_on' => now()->toDateString(), 'notes' => null,
        ], null);
        $billing->registerPayment($b, [
            'period' => $period, 'invoice_number' => 'B-1', 'invoice_amount_usd' => 40,
            'amount_paid_usd' => 50, 'payment_reference' => 'VB', 'paid_on' => now()->toDateString(), 'notes' => null,
        ], null);

        $date = now()->format('Y-m-d');

        Livewire::actingAs($this->admin())
            ->test(FranchiseReceivables::class)
            ->call('exportPayments');

        Excel::assertDownloaded("pagos-franquicias-todas-las-franquicias-{$date}.xlsx", function (SimpleArrayExport $export) {
            $rows = collect(iterator_to_array($export->generator(), false));

            self::assertContains('N° voucher / referencia', $export->headings());
            self::assertCount(2, $rows);

            $rowA = $rows->firstWhere(4, 'A-1');
            self::assertSame('100.00', $rowA[7]);   // monto factura
            self::assertSame('70.00', $rowA[8]);    // pagado
            self::assertSame('VA', $rowA[9]);       // referencia
            self::assertSame('0.00', $rowA[11]);    // a favor
            self::assertSame('30.00', $rowA[12]);   // debe

            $rowB = $rows->firstWhere(4, 'B-1');
            self::assertSame('10.00', $rowB[11]);   // a favor
            self::assertSame('0.00', $rowB[12]);

            return true;
        });

        Livewire::actingAs($this->admin())
            ->test(FranchiseReceivables::class)
            ->call('selectAlly', $a->id)
            ->call('exportPayments');

        Excel::assertDownloaded("pagos-franquicias-franquicia-a-{$date}.xlsx", function (SimpleArrayExport $export) {
            $rows = iterator_to_array($export->generator(), false);
            self::assertCount(1, $rows);
            self::assertSame('Franquicia A', $rows[0][1]);

            return true;
        });
    }

    public function test_franchise_sees_its_fee_and_balance_and_it_reduces_net_profit(): void
    {
        $franchise = $this->franchise(fee: 10);
        $this->createPackage($franchise, ['registered_by_user_id' => $franchise->user_id, 'total_price_usd' => 200]);

        app(FranchiseBillingService::class)->registerPayment($franchise, [
            'period' => now()->subMonthNoOverflow()->format('Y-m'), 'invoice_number' => 'F-77',
            'invoice_amount_usd' => 30, 'amount_paid_usd' => 20, 'payment_reference' => 'VOU-77',
            'paid_on' => now()->toDateString(), 'notes' => null,
        ], null);

        Livewire::actingAs($franchise->user)
            ->test(FranchiseFinance::class)
            ->assertViewHas('feeAccrued', 20.0)
            ->assertViewHas('netProfit', 180.0)
            ->assertViewHas('feeBalance', -10.0)
            ->assertSee('Pendiente por pagar $10.00')
            ->assertSee('VOU-77');
    }
}
