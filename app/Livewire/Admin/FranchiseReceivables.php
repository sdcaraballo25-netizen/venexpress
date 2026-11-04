<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\ExportsSpreadsheet;
use App\Models\Ally;
use App\Models\FranchiseInvoice;
use App\Models\FranchisePayment;
use App\Services\FranchiseBillingService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Cuentas por cobrar de franquicias.
 *
 * Cada franquicia paga a Venexpress una cuota mensual = un % de sus
 * ventas del mes que fija el Admin. El Admin registra manualmente cada
 * pago (monto de la factura, monto pagado, N° de factura y N° de
 * voucher/referencia), y ve lo que queda a favor o debiendo. Exporta el
 * reporte de pagos de todas las franquicias o de una en específico.
 *
 * La regla de cálculo vive en FranchiseBillingService.
 */
#[Layout('layouts.admin')]
#[Title('Cuentas por cobrar — Franquicias')]
class FranchiseReceivables extends Component
{
    use ExportsSpreadsheet;
    use WithPagination;

    public string $search = '';

    public ?int $selectedAllyId = null;

    public string $feePercentage = '';

    // Formulario de pago.
    public string $period = '';

    public string $invoiceNumber = '';

    public string $invoiceAmount = '';

    public string $amountPaid = '';

    public string $paymentReference = '';

    public string $paidOn = '';

    public string $notes = '';

    // Filtros del historial / exportación.
    public string $filterFrom = '';

    public string $filterTo = '';

    public function mount(): void
    {
        $this->resetPaymentForm();
    }

    protected function franchises(): Builder
    {
        return Ally::query()->franchises();
    }

    protected function selectedAlly(): ?Ally
    {
        return $this->selectedAllyId
            ? $this->franchises()->find($this->selectedAllyId)
            : null;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterFrom(): void
    {
        $this->resetPage('paymentsPage');
    }

    public function updatedFilterTo(): void
    {
        $this->resetPage('paymentsPage');
    }

    public function selectAlly(?int $allyId): void
    {
        if ($allyId === null) {
            $this->selectedAllyId = null;
            $this->resetPage('paymentsPage');

            return;
        }

        $ally = $this->franchises()->findOrFail($allyId);

        $this->selectedAllyId = $ally->id;
        $this->feePercentage = number_format((float) $ally->franchise_fee_percentage, 2, '.', '');
        $this->resetPaymentForm();
        $this->resetValidation();
        $this->resetPage('paymentsPage');
    }

    /**
     * Al elegir el período, propone como monto de la factura la cuota
     * calculada (el Admin puede cambiarlo antes de guardar).
     */
    public function updatedPeriod(FranchiseBillingService $billing): void
    {
        $ally = $this->selectedAlly();

        if (! $ally || ! preg_match('/^\d{4}-\d{2}$/', $this->period)) {
            return;
        }

        if ($this->existingInvoice()) {
            return;
        }

        $expected = $billing->expectedFee($ally, \Illuminate\Support\Carbon::createFromFormat('Y-m', $this->period));
        $this->invoiceAmount = $expected > 0 ? number_format($expected, 2, '.', '') : '';
    }

    public function updatedInvoiceNumber(): void
    {
        if ($invoice = $this->existingInvoice()) {
            $this->period = $invoice->period_month->format('Y-m');
            $this->invoiceAmount = number_format((float) $invoice->invoice_amount_usd, 2, '.', '');
        }
    }

    protected function existingInvoice(): ?FranchiseInvoice
    {
        if (! $this->selectedAllyId || trim($this->invoiceNumber) === '') {
            return null;
        }

        return FranchiseInvoice::query()
            ->where('ally_id', $this->selectedAllyId)
            ->where('invoice_number', trim($this->invoiceNumber))
            ->first();
    }

    public function saveFee(FranchiseBillingService $billing): void
    {
        $this->validate([
            'feePercentage' => ['required', 'numeric', 'min:0', 'max:100'],
        ], [
            'feePercentage.required' => 'Indica el porcentaje.',
            'feePercentage.max' => 'El porcentaje no puede superar 100.',
        ]);

        $ally = $this->selectedAlly();
        abort_unless($ally, 404);

        $billing->updateFeePercentage($ally, (float) $this->feePercentage, Auth::id());

        session()->flash('success', "Cuota de {$ally->business_name} actualizada a {$this->feePercentage}%.");
    }

    public function registerPayment(FranchiseBillingService $billing): void
    {
        $ally = $this->selectedAlly();
        abort_unless($ally, 404);

        $isNewInvoice = ! $this->existingInvoice();

        $this->validate([
            'period' => ['required', 'date_format:Y-m'],
            'invoiceNumber' => ['required', 'string', 'max:60'],
            'invoiceAmount' => $isNewInvoice
                ? ['required', 'numeric', 'min:0.01', 'max:9999999999']
                : ['nullable'],
            'amountPaid' => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'paymentReference' => ['required', 'string', 'max:120'],
            'paidOn' => ['required', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'period.required' => 'Elige el mes que se factura.',
            'invoiceNumber.required' => 'Indica el número de factura.',
            'invoiceAmount.required' => 'Indica el monto de la factura.',
            'invoiceAmount.min' => 'El monto de la factura debe ser mayor que cero.',
            'amountPaid.required' => 'Indica cuánto se pagó.',
            'paymentReference.required' => 'Indica el número de voucher o referencia.',
            'paidOn.before_or_equal' => 'La fecha del pago no puede ser futura.',
        ]);

        try {
            $payment = $billing->registerPayment($ally, [
                'period' => $this->period,
                'invoice_number' => $this->invoiceNumber,
                'invoice_amount_usd' => $this->invoiceAmount,
                'amount_paid_usd' => $this->amountPaid,
                'payment_reference' => $this->paymentReference,
                'paid_on' => $this->paidOn,
                'notes' => $this->notes,
            ], Auth::id());
        } catch (InvalidArgumentException $e) {
            $this->addError('invoiceNumber', $e->getMessage());

            return;
        }

        $balance = $payment->invoice->balance();

        $message = match (true) {
            $balance > 0 => 'Pago registrado. Quedan $'.number_format($balance, 2).' a favor del franquiciado.',
            $balance < 0 => 'Pago registrado. El franquiciado queda debiendo $'.number_format(abs($balance), 2).'.',
            default => 'Pago registrado. La factura quedó saldada.',
        };

        $this->resetPaymentForm();
        session()->flash('success', $message);
    }

    protected function resetPaymentForm(): void
    {
        $this->period = now()->subMonthNoOverflow()->format('Y-m');
        $this->invoiceNumber = '';
        $this->invoiceAmount = '';
        $this->amountPaid = '';
        $this->paymentReference = '';
        $this->paidOn = now()->toDateString();
        $this->notes = '';
    }

    /*
    |--------------------------------------------------------------------------
    | Reporte de pagos
    |--------------------------------------------------------------------------
    */

    /**
     * Pagos con los filtros actuales: todas las franquicias o la
     * seleccionada, y rango de fechas de pago opcional.
     */
    protected function paymentsQuery(): Builder
    {
        return FranchisePayment::query()
            ->with(['ally:id,business_name,rif', 'invoice', 'recordedBy:id,name'])
            ->when($this->selectedAllyId, fn ($q) => $q->where('ally_id', $this->selectedAllyId))
            ->when($this->filterFrom !== '', fn ($q) => $q->whereDate('paid_on', '>=', $this->filterFrom))
            ->when($this->filterTo !== '', fn ($q) => $q->whereDate('paid_on', '<=', $this->filterTo))
            ->orderByDesc('paid_on')
            ->orderByDesc('id');
    }

    public function exportPayments(): BinaryFileResponse
    {
        $ally = $this->selectedAlly();
        $payments = $this->paymentsQuery()->get();

        // Total pagado por factura, para mostrar el saldo de cada una.
        $paidByInvoice = FranchisePayment::query()
            ->whereIn('franchise_invoice_id', $payments->pluck('franchise_invoice_id')->unique())
            ->selectRaw('franchise_invoice_id, SUM(amount_paid_usd) as total')
            ->groupBy('franchise_invoice_id')
            ->pluck('total', 'franchise_invoice_id');

        $rows = $payments->map(function (FranchisePayment $payment) use ($paidByInvoice) {
            $invoice = $payment->invoice;
            $invoicePaid = (float) ($paidByInvoice[$invoice->id] ?? 0);
            $balance = round($invoicePaid - (float) $invoice->invoice_amount_usd, 2);

            return [
                $payment->paid_on?->format('d/m/Y'),
                $payment->ally?->business_name,
                $payment->ally?->rif,
                ucfirst($invoice->period_month->locale('es')->translatedFormat('F Y')),
                $invoice->invoice_number,
                number_format((float) $invoice->sales_total_usd, 2, '.', ''),
                number_format((float) $invoice->fee_percentage, 2, '.', ''),
                number_format((float) $invoice->invoice_amount_usd, 2, '.', ''),
                number_format((float) $payment->amount_paid_usd, 2, '.', ''),
                $payment->payment_reference,
                number_format($invoicePaid, 2, '.', ''),
                $balance > 0 ? number_format($balance, 2, '.', '') : '0.00',
                $balance < 0 ? number_format(abs($balance), 2, '.', '') : '0.00',
                $payment->recordedBy?->name,
                $payment->notes,
            ];
        });

        $name = $ally ? Str::slug($ally->business_name) : 'todas-las-franquicias';

        return $this->excelDownload(
            "pagos-franquicias-{$name}-".now()->format('Y-m-d').'.xlsx',
            [
                'Fecha de pago', 'Franquicia', 'RIF', 'Período', 'N° factura', 'Ventas del mes USD', 'Cuota %',
                'Monto factura USD', 'Monto pagado USD', 'N° voucher / referencia', 'Total pagado de la factura USD',
                'A favor del franquiciado USD', 'Debe USD', 'Registrado por', 'Notas',
            ],
            $rows,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    public function render(FranchiseBillingService $billing)
    {
        $franchises = $this->franchises()
            ->when($this->search !== '', function ($query) {
                $term = '%'.$this->search.'%';
                $query->where(fn ($q) => $q
                    ->where('business_name', 'like', $term)
                    ->orWhere('rif', 'like', $term)
                    ->orWhere('city', 'like', $term));
            })
            ->withSum('franchiseInvoices', 'invoice_amount_usd')
            ->withSum('franchisePayments', 'amount_paid_usd')
            ->orderBy('business_name')
            ->paginate(15);

        $currentMonth = now()->startOfMonth();

        $franchiseRows = $franchises->getCollection()->map(function (Ally $ally) use ($billing, $currentMonth) {
            $invoiced = round((float) $ally->franchise_invoices_sum_invoice_amount_usd, 2);
            $paid = round((float) $ally->franchise_payments_sum_amount_paid_usd, 2);
            $statement = $billing->statement($ally, 12);

            return [
                'ally' => $ally,
                'current_sales' => $billing->monthlySales($ally, $currentMonth),
                'current_fee' => $billing->expectedFee($ally, $currentMonth),
                'invoiced' => $invoiced,
                'paid' => $paid,
                'balance' => round($paid - $invoiced, 2),
                'pending_months' => $statement->where('pending_invoice', true)->count(),
                'pending_amount' => round((float) $statement->where('pending_invoice', true)->sum('expected'), 2),
            ];
        });

        $selectedAlly = $this->selectedAlly();

        $totals = [
            'invoiced' => round((float) FranchiseInvoice::query()->sum('invoice_amount_usd'), 2),
            'paid' => round((float) FranchisePayment::query()->sum('amount_paid_usd'), 2),
        ];
        $totals['balance'] = round($totals['paid'] - $totals['invoiced'], 2);

        return view('livewire.admin.franchise-receivables', [
            'franchises' => $franchises,
            'franchiseRows' => $franchiseRows,
            'totals' => $totals,
            'selectedAlly' => $selectedAlly,
            'statement' => $selectedAlly ? $billing->statement($selectedAlly, 12) : collect(),
            'selectedBalance' => $selectedAlly ? $billing->balance($selectedAlly) : 0.0,
            'existingInvoice' => $this->existingInvoice(),
            'payments' => $this->paymentsQuery()->paginate(15, ['*'], 'paymentsPage'),
        ]);
    }
}
