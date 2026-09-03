<?php

namespace App\Livewire\Admin;

use App\Models\Ally;
use App\Models\AllyFinancialTransaction;
use App\Models\AllySettlement;
use App\Services\AllyFinancialService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use RuntimeException;

#[Layout('layouts.admin')]
#[Title('Finanzas de Aliados')]
class AllyFinance extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $selectedAllyId = null;

    public string $settlementAmount = '';

    public string $paymentMethod = '';

    public string $paymentReference = '';

    public string $settlementNotes = '';

    public string $adjustmentAmount = '';

    public string $adjustmentDirection =
        AllyFinancialTransaction::DIRECTION_CREDIT;

    public string $adjustmentDescription = '';

    public string $adjustmentReference = '';

    public ?int $reversingSettlementId = null;

    public string $reversalReason = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function selectAlly(
        int $allyId
    ): void {
        Ally::findOrFail(
            $allyId
        );

        $this->selectedAllyId =
            $allyId;

        $this->resetSettlementForm();
        $this->resetAdjustmentForm();
    }

    public function createSettlement(
        AllyFinancialService $financialService
    ): void {
        $this->validate([
            'selectedAllyId' => [
                'required',
                'integer',
                'exists:allies,id',
            ],

            'settlementAmount' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'paymentMethod' => [
                'nullable',
                Rule::in(
                    AllySettlement::PAYMENT_METHODS
                ),
            ],

            'paymentReference' => [
                'nullable',
                'string',
                'max:150',
            ],

            'settlementNotes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        try {
            $financialService->createSettlement(
                allyId:
                    (int) $this->selectedAllyId,

                amountUsd:
                    (float) $this->settlementAmount,

                paymentMethod:
                    $this->paymentMethod !== ''
                        ? $this->paymentMethod
                        : null,

                reference:
                    $this->paymentReference !== ''
                        ? $this->paymentReference
                        : null,

                notes:
                    $this->settlementNotes !== ''
                        ? $this->settlementNotes
                        : null,

                userId:
                    (int) auth()->id()
            );

            session()->flash(
                'success',
                'La liquidación fue creada correctamente.'
            );

            $this->resetSettlementForm();
        } catch (RuntimeException $e) {
            $this->addError(
                'settlementAmount',
                $e->getMessage()
            );
        }
    }

    public function markPaid(
        int $settlementId,
        AllyFinancialService $financialService
    ): void {
        try {
            $financialService->markSettlementPaid(
                settlementId:
                    $settlementId,

                adminUserId:
                    (int) auth()->id(),

                paymentMethod:
                    $this->paymentMethod !== ''
                        ? $this->paymentMethod
                        : null,

                paymentReference:
                    $this->paymentReference !== ''
                        ? $this->paymentReference
                        : null,
            );

            session()->flash(
                'success',
                'La liquidación fue marcada como pagada y el saldo fue descontado.'
            );

            $this->resetSettlementForm();
        } catch (RuntimeException $e) {
            session()->flash(
                'error',
                $e->getMessage()
            );
        }
    }

    public function cancelSettlement(
        int $settlementId,
        AllyFinancialService $financialService
    ): void {
        try {
            $financialService->cancelSettlement(
                settlementId:
                    $settlementId,

                adminUserId:
                    (int) auth()->id()
            );

            session()->flash(
                'success',
                'La liquidación fue cancelada.'
            );
        } catch (RuntimeException $e) {
            session()->flash(
                'error',
                $e->getMessage()
            );
        }
    }

    public function openReversal(
        int $settlementId
    ): void {
        $settlement =
            AllySettlement::findOrFail(
                $settlementId
            );

        if (! $settlement->isPaid()) {
            session()->flash(
                'error',
                'Solo puedes revertir una liquidación pagada.'
            );

            return;
        }

        $this->reversingSettlementId =
            $settlementId;

        $this->reversalReason = '';
    }

    public function reverseSettlement(
        AllyFinancialService $financialService
    ): void {
        $this->validate([
            'reversingSettlementId' => [
                'required',
                'integer',
                'exists:ally_settlements,id',
            ],

            'reversalReason' => [
                'required',
                'string',
                'min:5',
                'max:1000',
            ],
        ]);

        try {
            $financialService->reverseSettlement(
                settlementId:
                    (int) $this->reversingSettlementId,

                adminUserId:
                    (int) auth()->id(),

                reason:
                    $this->reversalReason
            );

            session()->flash(
                'success',
                'La liquidación fue revertida y el saldo fue restaurado.'
            );

            $this->reversingSettlementId = null;
            $this->reversalReason = '';
        } catch (RuntimeException $e) {
            session()->flash(
                'error',
                $e->getMessage()
            );
        }
    }

    public function createAdjustment(
        AllyFinancialService $financialService
    ): void {
        $this->validate([
            'selectedAllyId' => [
                'required',
                'integer',
                'exists:allies,id',
            ],

            'adjustmentAmount' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'adjustmentDirection' => [
                'required',
                Rule::in([
                    AllyFinancialTransaction::DIRECTION_CREDIT,
                    AllyFinancialTransaction::DIRECTION_DEBIT,
                ]),
            ],

            'adjustmentDescription' => [
                'required',
                'string',
                'min:5',
                'max:500',
            ],

            'adjustmentReference' => [
                'nullable',
                'string',
                'max:100',
            ],
        ]);

        try {
            $financialService->createAdjustment(
                allyId:
                    (int) $this->selectedAllyId,

                amountUsd:
                    (float) $this->adjustmentAmount,

                direction:
                    $this->adjustmentDirection,

                description:
                    $this->adjustmentDescription,

                adminUserId:
                    (int) auth()->id(),

                reference:
                    $this->adjustmentReference !== ''
                        ? $this->adjustmentReference
                        : null
            );

            session()->flash(
                'success',
                'El ajuste financiero fue registrado.'
            );

            $this->resetAdjustmentForm();
        } catch (RuntimeException $e) {
            $this->addError(
                'adjustmentAmount',
                $e->getMessage()
            );
        }
    }

    protected function resetSettlementForm(): void
    {
        $this->settlementAmount = '';
        $this->paymentMethod = '';
        $this->paymentReference = '';
        $this->settlementNotes = '';
    }

    protected function resetAdjustmentForm(): void
    {
        $this->adjustmentAmount = '';

        $this->adjustmentDirection =
            AllyFinancialTransaction::DIRECTION_CREDIT;

        $this->adjustmentDescription = '';
        $this->adjustmentReference = '';
    }

    public function render(
        AllyFinancialService $financialService
    ) {
        $allies =
            Ally::query()
                ->when(
                    $this->search !== '',
                    function ($query) {
                        $query->where(
                            function ($q) {
                                $q
                                    ->where(
                                        'business_name',
                                        'like',
                                        '%' . $this->search . '%'
                                    )
                                    ->orWhere(
                                        'rif',
                                        'like',
                                        '%' . $this->search . '%'
                                    )
                                    ->orWhere(
                                        'city',
                                        'like',
                                        '%' . $this->search . '%'
                                    );
                            }
                        );
                    }
                )
                ->orderBy(
                    'business_name'
                )
                ->paginate(15);

        $selectedAlly = null;
        $balance = 0.0;
        $generated = 0.0;
        $paid = 0.0;

        $settlements = collect();
        $transactions = collect();

        if ($this->selectedAllyId) {
            $selectedAlly =
                Ally::find(
                    $this->selectedAllyId
                );

            if ($selectedAlly) {
                $balance =
                    $financialService
                        ->getBalance(
                            $selectedAlly->id
                        );

                $generated =
                    $financialService
                        ->getGeneratedCommission(
                            $selectedAlly->id
                        );

                $paid =
                    $financialService
                        ->getPaidAmount(
                            $selectedAlly->id
                        );

                $settlements =
                    AllySettlement::query()
                        ->where(
                            'ally_id',
                            $selectedAlly->id
                        )
                        ->latest()
                        ->limit(20)
                        ->get();

                $transactions =
                    AllyFinancialTransaction::query()
                        ->where(
                            'ally_id',
                            $selectedAlly->id
                        )
                        ->with(
                            'createdBy:id,name'
                        )
                        ->latest('created_at')
                        ->latest('id')
                        ->limit(30)
                        ->get();
            }
        }

        return view(
            'livewire.admin.ally-finance',
            [
                'allies' =>
                    $allies,

                'selectedAlly' =>
                    $selectedAlly,

                'balance' =>
                    $balance,

                'generated' =>
                    $generated,

                'paid' =>
                    $paid,

                'settlements' =>
                    $settlements,

                'transactions' =>
                    $transactions,
            ]
        );
    }
}
