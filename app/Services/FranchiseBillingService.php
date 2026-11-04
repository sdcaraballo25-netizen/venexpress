<?php

namespace App\Services;

use App\Models\Ally;
use App\Models\AuditLog;
use App\Models\FranchiseInvoice;
use App\Models\FranchisePayment;
use App\Models\Package;
use App\Support\Money;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Cuentas por cobrar de franquicias: única fuente de la regla
 * "cuota mensual = franchise_fee_percentage % de las ventas del mes".
 *
 * Ventas del mes = SUM(total_price_usd) de las guías de la franquicia
 * registradas en ese mes (mismo criterio que Ally\Dashboard y
 * Ally\FranchiseFinance, por fecha de registro).
 *
 * Saldo = pagado - facturado: > 0 a favor del franquiciado, < 0 lo que
 * debe.
 */
class FranchiseBillingService
{
    public function salesBetween(Ally $ally, Carbon $from, Carbon $to): float
    {
        return Money::round(
            (float) Package::query()
                ->where('ally_id', $ally->id)
                ->whereBetween('created_at', [$from, $to])
                ->sum('total_price_usd')
        );
    }

    public function monthlySales(Ally $ally, Carbon $month): float
    {
        return $this->salesBetween(
            $ally,
            $month->copy()->startOfMonth(),
            $month->copy()->endOfMonth()
        );
    }

    public function feeFor(float $sales, float $percentage): float
    {
        return Money::round(Money::mul($sales, Money::div($percentage, 100)));
    }

    public function expectedFee(Ally $ally, Carbon $month): float
    {
        return $this->feeFor(
            $this->monthlySales($ally, $month),
            (float) $ally->franchise_fee_percentage
        );
    }

    public function invoicedTotal(Ally $ally): float
    {
        return Money::round((float) $ally->franchiseInvoices()->sum('invoice_amount_usd'));
    }

    public function paidTotal(Ally $ally): float
    {
        return Money::round((float) $ally->franchisePayments()->sum('amount_paid_usd'));
    }

    /**
     * Saldo global de la franquicia: > 0 a favor, < 0 debe.
     */
    public function balance(Ally $ally): float
    {
        return Money::round($this->paidTotal($ally) - $this->invoicedTotal($ally));
    }

    /**
     * Estado de cuenta mes a mes (del más reciente al más antiguo):
     * ventas, cuota esperada, lo facturado y pagado, y si el mes
     * todavía no se ha facturado.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function statement(Ally $ally, int $months = 12): Collection
    {
        $invoices = $ally->franchiseInvoices()
            ->withSum('payments', 'amount_paid_usd')
            ->get()
            ->groupBy(fn (FranchiseInvoice $invoice) => $invoice->period_month->format('Y-m'));

        $percentage = (float) $ally->franchise_fee_percentage;
        $currentMonth = now()->startOfMonth();

        return collect(range(0, $months - 1))->map(function (int $i) use ($ally, $invoices, $percentage, $currentMonth) {
            $month = $currentMonth->copy()->subMonthsNoOverflow($i);
            $key = $month->format('Y-m');
            $monthInvoices = $invoices->get($key, collect());

            $sales = $this->monthlySales($ally, $month);
            $invoiced = Money::round((float) $monthInvoices->sum('invoice_amount_usd'));
            $paid = Money::round((float) $monthInvoices->sum('payments_sum_amount_paid_usd'));

            return [
                'month' => $month,
                'key' => $key,
                'label' => ucfirst($month->locale('es')->translatedFormat('F Y')),
                'is_current' => $i === 0,
                'sales' => $sales,
                'expected' => $this->feeFor($sales, $percentage),
                'invoice_numbers' => $monthInvoices->pluck('invoice_number')->all(),
                'invoiced' => $invoiced,
                'paid' => $paid,
                'balance' => Money::round($paid - $invoiced),
                // Mes cerrado con cuota > 0 que todavía no se facturó.
                'pending_invoice' => $i > 0
                    && $monthInvoices->isEmpty()
                    && $this->feeFor($sales, $percentage) > 0,
            ];
        });
    }

    public function updateFeePercentage(Ally $ally, float $percentage, ?int $actorUserId): void
    {
        if (! $ally->isFranchise()) {
            throw new InvalidArgumentException('Solo las franquicias tienen cuota mensual.');
        }

        if ($percentage < 0 || $percentage > 100) {
            throw new InvalidArgumentException('El porcentaje debe estar entre 0 y 100.');
        }

        $previous = (float) $ally->franchise_fee_percentage;

        $ally->update(['franchise_fee_percentage' => round($percentage, 2)]);

        AuditLog::create([
            'actor_user_id' => $actorUserId,
            'action' => 'franchise.fee_updated',
            'target_type' => Ally::class,
            'target_id' => $ally->id,
            'description' => "Cambió la cuota de la franquicia {$ally->business_name} de {$previous}% a {$percentage}%.",
            'metadata' => ['previous' => $previous, 'new' => round($percentage, 2)],
            'ip_address' => request()?->ip(),
        ]);
    }

    /**
     * Registra un pago contra una factura de la franquicia.
     *
     * Si el N° de factura ya existe para esta franquicia, el pago se suma
     * a esa factura como un abono (no se vuelve a facturar el monto). Si
     * no existe, se crea la factura con una foto de las ventas del mes,
     * el % vigente y la cuota esperada.
     *
     * @param  array{period: string, invoice_number: string, invoice_amount_usd: float|string|null, amount_paid_usd: float|string, payment_reference: ?string, paid_on: string, notes: ?string}  $data
     */
    public function registerPayment(Ally $ally, array $data, ?int $actorUserId): FranchisePayment
    {
        if (! $ally->isFranchise()) {
            throw new InvalidArgumentException('Solo se registran pagos de franquicias.');
        }

        $period = Carbon::createFromFormat('Y-m', $data['period'])->startOfMonth();
        $invoiceNumber = trim($data['invoice_number']);
        $amountPaid = Money::round((float) $data['amount_paid_usd']);

        if ($amountPaid < 0) {
            throw new InvalidArgumentException('El monto pagado no puede ser negativo.');
        }

        return DB::transaction(function () use ($ally, $data, $period, $invoiceNumber, $amountPaid, $actorUserId) {
            $invoice = FranchiseInvoice::query()
                ->where('ally_id', $ally->id)
                ->where('invoice_number', $invoiceNumber)
                ->lockForUpdate()
                ->first();

            if ($invoice) {
                if ($invoice->period_month->format('Y-m') !== $period->format('Y-m')) {
                    throw new InvalidArgumentException(
                        "La factura {$invoiceNumber} ya existe para "
                        .$invoice->period_month->locale('es')->translatedFormat('F Y')
                        .'. Elige ese mismo período para abonarle.'
                    );
                }
            } else {
                $invoiceAmount = $data['invoice_amount_usd'] ?? null;

                if ($invoiceAmount === null || $invoiceAmount === '' || (float) $invoiceAmount <= 0) {
                    throw new InvalidArgumentException('Indica el monto de la factura.');
                }

                $sales = $this->monthlySales($ally, $period);
                $percentage = (float) $ally->franchise_fee_percentage;

                $invoice = FranchiseInvoice::create([
                    'ally_id' => $ally->id,
                    'period_month' => $period->toDateString(),
                    'invoice_number' => $invoiceNumber,
                    'sales_total_usd' => $sales,
                    'fee_percentage' => $percentage,
                    'expected_amount_usd' => $this->feeFor($sales, $percentage),
                    'invoice_amount_usd' => Money::round((float) $invoiceAmount),
                    'created_by_user_id' => $actorUserId,
                ]);
            }

            $payment = FranchisePayment::create([
                'franchise_invoice_id' => $invoice->id,
                'ally_id' => $ally->id,
                'amount_paid_usd' => $amountPaid,
                'payment_reference' => filled($data['payment_reference'] ?? null) ? trim($data['payment_reference']) : null,
                'paid_on' => $data['paid_on'],
                'notes' => filled($data['notes'] ?? null) ? $data['notes'] : null,
                'recorded_by_user_id' => $actorUserId,
            ]);

            $balance = $invoice->fresh()->balance();

            AuditLog::create([
                'actor_user_id' => $actorUserId,
                'action' => 'franchise.payment_recorded',
                'target_type' => FranchisePayment::class,
                'target_id' => $payment->id,
                'description' => "Registró un pago de \${$amountPaid} de la franquicia {$ally->business_name} "
                    ."(factura {$invoiceNumber}, ref. ".($payment->payment_reference ?? 'sin referencia').').',
                'metadata' => [
                    'ally_id' => $ally->id,
                    'invoice_id' => $invoice->id,
                    'invoice_amount_usd' => (float) $invoice->invoice_amount_usd,
                    'amount_paid_usd' => $amountPaid,
                    'invoice_balance_usd' => $balance,
                ],
                'ip_address' => request()?->ip(),
            ]);

            return $payment;
        });
    }
}
