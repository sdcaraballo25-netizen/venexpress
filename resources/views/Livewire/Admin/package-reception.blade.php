<div class="space-y-6">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="font-display text-2xl font-semibold text-[#0F172A]">
                Recepción de paquetes
            </h2>
            <p class="text-sm text-slate-500">
                Registra físicamente la llegada de una guía al Hub o punto de destino.
            </p>
        </div>

        <a
            href="{{ route('admin.dashboard') }}"
            class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
        >
            ← Volver
        </a>
    </div>

    @if ($successMessage)
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ $successMessage }}
        </div>
    @endif

    @if ($errorMessage)
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
            {{ $errorMessage }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="font-display text-lg font-semibold text-slate-900">
                Localizar guía
            </h3>

            <form wire:submit.prevent="search" class="mt-5 space-y-4">
                <div>
                    <label class="text-sm font-medium text-slate-600">
                        Número de guía
                    </label>

                    <div class="mt-2 flex gap-2">
                        <input
                            type="text"
                            wire:model="trackingNumber"
                            placeholder="VEN-..."
                            autocomplete="off"
                            class="min-w-0 flex-1 rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-900 focus:ring-blue-900"
                        >

                        <button
                            type="submit"
                            class="rounded-xl bg-blue-900 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-800"
                        >
                            Buscar
                        </button>
                    </div>
                </div>

                @if ($package)
                    <div class="rounded-xl bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">
                            Guía localizada
                        </p>

                        <p class="mt-1 font-semibold text-slate-800">
                            {{ $package->tracking_number }}
                        </p>

                        <p class="mt-2 text-sm text-slate-600">
                            Estado: {{ $package->statusLabel() }}
                        </p>

                        <p class="mt-1 text-sm text-slate-600">
                            Destinatario: {{ $package->recipient_name }}
                        </p>
                    </div>
                @endif
            </form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="font-display text-lg font-semibold text-slate-900">
                Confirmar recepción
            </h3>

            <p class="mt-1 text-sm text-slate-500">
                La recepción queda registrada en el historial inmutable.
            </p>

            <form wire:submit.prevent="receive" class="mt-5 space-y-4">

                <div>
                    <label class="text-sm font-medium text-slate-600">
                        Lugar de recepción
                    </label>

                    <input
                        type="text"
                        wire:model="destinationLocation"
                        placeholder="Ej. Hub Caracas"
                        class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-900 focus:ring-blue-900"
                    >

                    @error('destinationLocation')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="w-full rounded-xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50"
                >
                    Registrar recepción
                </button>

                @if ($package)
                    <button
                        type="button"
                        wire:click="clear"
                        class="w-full rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50"
                    >
                        Limpiar
                    </button>
                @endif
            </form>
        </div>
    </div>

    @if ($package && $package->histories->count())
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="font-display text-lg font-semibold text-slate-900">
                Historial de la guía
            </h3>

            <div class="mt-5 divide-y divide-slate-100">
                @foreach ($package->histories->sortByDesc('created_at') as $history)
                    <div class="py-4 first:pt-0">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="text-sm font-semibold text-slate-800">
                                {{ $history->eventTypeLabel() }}
                            </span>

                            <span class="text-xs text-slate-400">
                                {{ $history->created_at?->format('d/m/Y H:i') }}
                            </span>
                        </div>

                        <p class="mt-1 text-xs text-slate-500">
                            {{ $history->location_description }}
                        </p>

                        @if ($history->origin_location || $history->destination_location)
                            <p class="mt-1 text-xs text-slate-500">
                                {{ $history->origin_location ?: '—' }}
                                →
                                {{ $history->destination_location ?: '—' }}
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
