<?php

namespace App\Livewire\Ally;

use App\Models\AllySettlement;
use App\Services\AllyFinancialService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.ally')]
class DailyCashCut extends Component
{
    use WithPagination;

    public string $amountUsd = '';
    public string $paymentMethod = '';
    public string $paymentReference = '';
    public string $notes = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->resolveAlly(), 403);
    }

    public function requestSettlement(AllyFinancialService $financialService): void
    {
        $this->validate([
            'amountUsd' => ['required', 'numeric', 'min:0.01'],
            'paymentMethod' => ['nullable', Rule::in(AllySettlement::PAYMENT_METHODS)],
            'paymentReference' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $ally = auth()->user()->resolveAlly();

        try {
            $financialService->createSettlement(
                $ally->id,
                (float) $this->amountUsd,
                $this->paymentMethod !== '' ? $this->paymentMethod : null,
                $this->paymentReference !== '' ? $this->paymentReference : null,
                $this->notes !== '' ? $this->notes : null,
                auth()->id(),
            );

            $this->reset(['amountUsd', 'paymentMethod', 'paymentReference', 'notes']);
            session()->flash('success', 'Solicitud de liquidación registrada correctamente.');
        } catch (\Throwable $exception) {
            $this->addError('amountUsd', $exception->getMessage());
        }
    }

    public function render(AllyFinancialService $financialService)
    {
        $ally = auth()->user()->resolveAlly();

        return view('livewire.ally.daily-cash-cut', [
            'ally' => $ally,
            'balance' => $financialService->getBalance($ally->id),
            'generated' => $financialService->getGeneratedCommission($ally->id),
            'paid' => $financialService->getPaidAmount($ally->id),
            'transactions' => $financialService->history($ally->id, 15),
            'settlements' => AllySettlement::query()
                ->where('ally_id', $ally->id)
                ->latest('created_at')
                ->paginate(10, ['*'], 'settlementsPage'),
        ]);
    }
}
