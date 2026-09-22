<?php

use App\Models\Ally;
use App\Models\Driver;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

/**
 * Cuenta bancaria a la que se le paga la comisión (Aliado) o la
 * remuneración (Repartidor). Solo aparece una vez la cuenta está
 * aprobada (Ally::STATUS_ACTIVE / Driver::STATUS_ACTIVE): antes de
 * eso no hay nada que pagar todavía, y mostrar el formulario solo
 * confundiría. Para cualquier otro rol (Admin, Taquilla, Almacén,
 * Cliente) este componente no renderiza nada.
 */
new class extends Component
{
    public bool $isAlly = false;

    public bool $isDriver = false;

    public bool $approved = false;

    public string $cedula = '';

    public string $bank_account_number = '';

    public string $bank_account_holder_name = '';

    public string $bank_account_holder_id = '';

    public function mount(): void
    {
        $user = Auth::user();

        $this->isAlly = $user->isAliado();
        $this->isDriver = $user->isRepartidor();

        if ($this->isAlly) {
            $ally = $user->ally;
            $this->approved = $ally?->status === Ally::STATUS_ACTIVE;
            $this->bank_account_number = (string) $ally?->bank_account_number;
            $this->bank_account_holder_name = (string) $ally?->bank_account_holder_name;
            $this->bank_account_holder_id = (string) $ally?->bank_account_holder_id;
        } elseif ($this->isDriver) {
            $driver = $user->driver;
            $this->approved = $driver?->status === Driver::STATUS_ACTIVE;
            $this->cedula = (string) $driver?->cedula;
            $this->bank_account_number = (string) $driver?->bank_account_number;
            $this->bank_account_holder_name = (string) $driver?->bank_account_holder_name;
            $this->bank_account_holder_id = (string) $driver?->bank_account_holder_id;
        }
    }

    protected function rules(): array
    {
        $rules = [
            'bank_account_number' => ['required', 'string', 'max:40'],
            'bank_account_holder_name' => ['required', 'string', 'max:150'],
            'bank_account_holder_id' => ['required', 'string', 'max:30'],
        ];

        if ($this->isDriver) {
            $rules['cedula'] = ['required', 'string', 'max:30'];
        }

        return $rules;
    }

    public function save(): void
    {
        $user = Auth::user();
        $validated = $this->validate();

        if ($this->isAlly && $this->approved) {
            $user->ally?->update($validated);
        } elseif ($this->isDriver && $this->approved) {
            $user->driver?->update($validated);
        }

        $this->dispatch('payout-account-updated');
    }
}; ?>

<div>
@if (($isAlly || $isDriver) && $approved)
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Cuenta para recibir tus pagos
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                @if ($isAlly)
                    A esta cuenta te pagaremos la comisión de tus guías.
                @else
                    A esta cuenta te pagaremos tu remuneración por entregas.
                @endif
            </p>
        </header>

        <form wire:submit="save" class="mt-6 space-y-6">
            @if ($isDriver)
                <div>
                    <x-input-label for="cedula" value="Cédula" />
                    <x-text-input wire:model="cedula" id="cedula" name="cedula" type="text" class="mt-1 block w-full" placeholder="V-12345678" />
                    <x-input-error class="mt-2" :messages="$errors->get('cedula')" />
                </div>
            @endif

            <div>
                <x-input-label for="bank_account_number" value="Número de cuenta" />
                <x-text-input wire:model="bank_account_number" id="bank_account_number" name="bank_account_number" type="text" class="mt-1 block w-full" />
                <x-input-error class="mt-2" :messages="$errors->get('bank_account_number')" />
            </div>

            <div>
                <x-input-label for="bank_account_holder_name" value="Nombre del titular de la cuenta" />
                <x-text-input wire:model="bank_account_holder_name" id="bank_account_holder_name" name="bank_account_holder_name" type="text" class="mt-1 block w-full" />
                <x-input-error class="mt-2" :messages="$errors->get('bank_account_holder_name')" />
            </div>

            <div>
                <x-input-label for="bank_account_holder_id" value="Cédula del titular de la cuenta" />
                <x-text-input wire:model="bank_account_holder_id" id="bank_account_holder_id" name="bank_account_holder_id" type="text" class="mt-1 block w-full" placeholder="V-12345678" />
                <x-input-error class="mt-2" :messages="$errors->get('bank_account_holder_id')" />
            </div>

            <div class="flex items-center gap-4">
                <x-primary-button>{{ __('Save') }}</x-primary-button>

                <x-action-message class="me-3" on="payout-account-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>
    </section>
@endif
</div>
