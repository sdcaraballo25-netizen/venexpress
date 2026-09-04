<div class="mx-auto max-w-3xl px-4 py-12">
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <div class="mb-6">
            <p class="text-sm font-semibold uppercase tracking-wide text-blue-600">Venexpress</p>
            <h1 class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">Rastrea tu envío</h1>
            <p class="mt-2 text-slate-600 dark:text-slate-300">Introduce el número de guía para consultar el estado de tu paquete.</p>
        </div>
        <form wire:submit="search" class="flex flex-col gap-3 sm:flex-row">
            <input wire:model.defer="guia" type="text" placeholder="Número de guía" class="w-full rounded-xl border-slate-300 px-4 py-3 dark:border-slate-600 dark:bg-slate-800" />
            <button type="submit" class="rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white hover:bg-blue-700">Consultar</button>
        </form>
        @error('guia') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        @if ($message) <div class="mt-5 rounded-xl bg-amber-50 p-4 text-amber-800">{{ $message }}</div> @endif
        @if ($package)
            <div class="mt-6 rounded-2xl bg-slate-50 p-5 dark:bg-slate-800">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div><p class="text-sm text-slate-500">Guía</p><p class="text-xl font-bold">{{ $package->tracking_number }}</p></div>
                    <span class="rounded-full bg-blue-100 px-3 py-1 text-sm font-semibold text-blue-700">{{ \App\Models\Package::STATUS_LABELS[$package->current_status] ?? $package->current_status }}</span>
                </div>
                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    <p><strong>Origen:</strong> {{ $package->origin_city }}, {{ $package->origin_state }}</p>
                    <p><strong>Destino:</strong> {{ $package->destination_city }}, {{ $package->destination_state }}</p>
                </div>
            </div>
        @endif
    </div>
</div>
