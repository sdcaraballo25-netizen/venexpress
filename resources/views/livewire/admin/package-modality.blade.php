<div class="space-y-6">

    <div>
        <h1 class="font-display text-2xl font-bold text-slate-900">Cambiar modalidad</h1>
        <p class="mt-1 text-sm text-slate-500">
            Cambia una guía entre entrega a domicilio y retiro en persona (almacén o agencia) antes de que salga a reparto.
            El total cobrado no cambia.
        </p>
    </div>

    @if ($successMessage)
        <div class="rounded-xl bg-emerald-50 p-4 text-sm text-emerald-700">{{ $successMessage }}</div>
    @endif

    @if ($errorMessage)
        <div class="rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ $errorMessage }}</div>
    @endif

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <form wire:submit.prevent="search" class="flex flex-col gap-3 sm:flex-row">
            <input
                wire:model="trackingNumber"
                placeholder="Número de guía"
                autocomplete="off"
                class="flex-1 rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-900 focus:ring-blue-900"
            >
            <button type="submit" class="rounded-xl bg-blue-900 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-800">
                Buscar
            </button>
        </form>

        @if ($package)
            <div class="mt-5 rounded-xl bg-slate-50 p-4">
                <p class="font-semibold text-slate-800">{{ $package->tracking_number }}</p>
                <dl class="mt-3 grid grid-cols-1 gap-3 text-sm sm:grid-cols-3">
                    <div>
                        <dt class="text-slate-400">Estado</dt>
                        <dd class="font-medium text-slate-700">{{ $package->statusLabel() }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-400">Modalidad actual</dt>
                        <dd class="font-medium text-slate-700">{{ $currentLabel }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-400">Destino</dt>
                        <dd class="font-medium text-slate-700">{{ $package->destination_city }}, {{ $package->destination_state }}</dd>
                    </div>
                </dl>
            </div>

            @if ($blockedReason)
                <p class="mt-5 rounded-xl bg-amber-50 p-4 text-sm text-amber-800">{{ $blockedReason }}</p>
            @else
                <form wire:submit.prevent="save" class="mt-5 space-y-4">
                    <div>
                        <label for="modality" class="text-sm font-medium text-slate-600">Nueva modalidad</label>
                        <select id="modality" wire:model.live="modality"
                                class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
                            <option value="{{ \App\Services\PackageModalityService::MODALITY_DELIVERY }}">Entrega a domicilio</option>
                            <option value="{{ \App\Services\PackageModalityService::MODALITY_HUB }}">Retiro en almacén</option>
                            <option value="{{ \App\Services\PackageModalityService::MODALITY_ALLY }}">Retiro en agencia aliada</option>
                        </select>
                        @error('modality') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    @if ($modality === \App\Services\PackageModalityService::MODALITY_DELIVERY)
                        <div>
                            <label for="deliveryAddress" class="text-sm font-medium text-slate-600">Dirección de entrega</label>
                            <textarea id="deliveryAddress" wire:model="deliveryAddress" rows="2"
                                      class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm"></textarea>
                            @error('deliveryAddress') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="deliverySector" class="text-sm font-medium text-slate-600">Sector (opcional)</label>
                                <input id="deliverySector" wire:model="deliverySector"
                                       class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
                            </div>
                            <div>
                                <label for="deliveryReference" class="text-sm font-medium text-slate-600">Referencia (opcional)</label>
                                <input id="deliveryReference" wire:model="deliveryReference"
                                       class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
                            </div>
                        </div>
                    @elseif ($modality === \App\Services\PackageModalityService::MODALITY_ALLY)
                        <div>
                            <label for="pickupAllyId" class="text-sm font-medium text-slate-600">Agencia de retiro</label>
                            <select id="pickupAllyId" wire:model="pickupAllyId"
                                    class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
                                <option value="">Selecciona una agencia...</option>
                                @foreach ($pickupAllies as $pickupAlly)
                                    <option value="{{ $pickupAlly->id }}">{{ $pickupAlly->business_name }} · {{ $pickupAlly->city }}</option>
                                @endforeach
                            </select>
                            @error('pickupAllyId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            @if ($pickupAllies->isEmpty())
                                <p class="mt-1 text-xs text-slate-500">No hay agencias de retiro verificadas en {{ $package->destination_state }}.</p>
                            @endif
                        </div>
                    @endif

                    <p class="text-xs text-slate-500">
                        Si la guía ya espera en su almacén destino, se vuelve a liberar con la modalidad nueva
                        (lista para retiro, pendiente de entrega o despachada a la agencia).
                    </p>

                    <button
                        type="button"
                        @click.prevent="$store.confirm.open({
                            message: '¿Cambiar la modalidad de la guía {{ $package->tracking_number }}?',
                            confirmText: 'Cambiar modalidad',
                            variant: 'primary',
                            onConfirm: () => $wire.save(),
                        })"
                        class="w-full rounded-xl bg-blue-900 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-800"
                    >
                        Guardar modalidad
                    </button>
                </form>
            @endif
        @endif
    </div>
</div>
