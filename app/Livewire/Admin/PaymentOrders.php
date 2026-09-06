<?php

namespace App\Livewire\Admin;

use App\Models\PaymentOrder;
use App\Services\PaymentReconciliationService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use RuntimeException;

#[Layout('layouts.admin')]
#[Title('Órdenes de pago')]
class PaymentOrders extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = '';

    public string $purpose = '';

    public ?int $selectedOrderId = null;

    public string $bankReference = '';

    public string $bankCode = '';

    public string $bankName = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingPurpose(): void
    {
        $this->resetPage();
    }

    public function selectOrder(int $orderId): void
    {
        PaymentOrder::query()->findOrFail($orderId);

        $this->selectedOrderId = $orderId;

        $this->resetConfirmationForm();
    }

    public function confirmPayment(
        PaymentReconciliationService $reconciliationService
    ): void {
        $this->validate([
            'selectedOrderId' => [
                'required',
                'integer',
                'exists:payment_orders,id',
            ],

            'bankReference' => [
                'required',
                'string',
                'max:100',
            ],

            'bankCode' => [
                'nullable',
                'string',
                'max:20',
            ],

            'bankName' => [
                'nullable',
                'string',
                'max:100',
            ],
        ]);

        $order = PaymentOrder::query()
            ->findOrFail($this->selectedOrderId);

        try {
            /*
             * La referencia se guarda antes de enviar la orden
             * al servicio de conciliación.
             *
             * Esta acción es únicamente para pruebas internas.
             * En producción debe existir validación bancaria.
             */
            $order->update([
                'bank_reference' => $this->bankReference,

                'bank_code' => $this->bankCode !== ''
                    ? $this->bankCode
                    : null,

                'bank_name' => $this->bankName !== ''
                    ? $this->bankName
                    : null,

                'status' => PaymentOrder::STATUS_PROCESSING,
            ]);

            $reconciliationService->reconcileConfirmedOrder(
                $order,
                (int) auth()->id()
            );

            session()->flash(
                'success',
                'El pago fue conciliado correctamente.'
            );

            $this->resetConfirmationForm();

        } catch (RuntimeException $e) {
            session()->flash(
                'error',
                $e->getMessage()
            );
        }
    }

    public function resetConfirmationForm(): void
    {
        $this->selectedOrderId = null;
        $this->bankReference = '';
        $this->bankCode = '';
        $this->bankName = '';

        $this->resetValidation();
    }

    public function render(): View
    {
        $orders = PaymentOrder::query()
            ->with([
                'ally',
                'package',
                'createdBy',
                'confirmedBy',
            ])
            ->when(
                $this->search !== '',
                function ($query) {
                    $search = '%' . $this->search . '%';

                    $query->where(function ($query) use ($search) {
                        $query
                            ->where(
                                'order_number',
                                'like',
                                $search
                            )
                            ->orWhere(
                                'bank_reference',
                                'like',
                                $search
                            );
                    });
                }
            )
            ->when(
                $this->status !== '',
                fn ($query) => $query->where(
                    'status',
                    $this->status
                )
            )
            ->when(
                $this->purpose !== '',
                fn ($query) => $query->where(
                    'purpose',
                    $this->purpose
                )
            )
            ->latest()
            ->paginate(15);

        return view('livewire.admin.payment-orders', [
            'orders' => $orders,
        ]);
    }
}
