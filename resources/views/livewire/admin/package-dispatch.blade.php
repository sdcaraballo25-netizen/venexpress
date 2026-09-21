<div class="space-y-6">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="font-display text-2xl font-semibold text-[#0F172A]">
                Despacho a tránsito
            </h2>
            <p class="text-sm text-slate-500">
                Registra la salida de un paquete desde el Hub hacia su destino.
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
                Buscar paquete
            </h3>

            <form wire:submit.prevent="search" class="mt-5">
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
            </form>

            @if ($package)
                <div class="mt-5 rounded-xl bg-slate-50 p-4">
                    <p class="text-xs uppercase tracking-wide text-slate-400">
                        Guía
                    </p>
                    <p class="mt-1 font-semibold text-slate-800">
                        {{ $package->tracking_number }}
                    </p>

                    <div class="mt-3 grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <span class="text-slate-400">Estado</span>
                            <p class="font-medium text-slate-700">
                                {{ $package->statusLabel() }}
                            </p>
                        </div>
                        <div>
                            <span class="text-slate-400">Destinatario</span>
                            <p class="font-medium text-slate-700">
                                {{ $package->recipient_name }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="font-display text-lg font-semibold text-slate-900">
                Confirmar despacho
            </h3>

            <form wire:submit.prevent="dispatchPackage" class="mt-5 space-y-4">

                <div>
                    <label class="text-sm font-medium text-slate-600">
                        Hub de origen
                    </label>
                    <input
                        type="text"
                        wire:model="originLocation"
                        placeholder="Ej. Hub Caracas"
                        class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-900 focus:ring-blue-900"
                    >
                    @error('originLocation')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-600">
                        Destino
                    </label>
                    <input
                        type="text"
                        wire:model="destinationLocation"
                        placeholder="Ej. Agencia Aliada Valencia"
                        class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-900 focus:ring-blue-900"
                    >
                    @error('destinationLocation')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="w-full rounded-xl bg-blue-900 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-800 disabled:opacity-50"
                >
                    Registrar despacho
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
                Últimos movimientos
            </h3>

            <div class="mt-4 divide-y divide-slate-100">
                @foreach ($package->histories->sortByDesc('created_at')->take(8) as $history)
                    <div class="py-3 first:pt-0">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-sm font-semibold text-slate-700">
                                {{ $history->eventTypeLabel() }}
                            </span>
                            <span class="text-xs text-slate-400">
                                {{ $history->created_at?->format('d/m/Y H:i') }}
                            </span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">
                            {{ $history->origin_location ?: '—' }}
                            →
                            {{ $history->destination_location ?: '—' }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
