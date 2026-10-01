<div class="space-y-6">

    <div>
        <h1 class="font-display text-2xl font-bold text-slate-900">
            Devoluciones
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Envíos registrados en tu agencia que no se pudieron entregar. Cuando el paquete llegue, entrégaselo al remitente verificando su cédula.
        </p>
    </div>

    @if ($message)
        <div class="rounded-xl bg-emerald-50 p-4 text-sm text-emerald-700">
            {{ $message }}
        </div>
    @endif

    @if ($error)
        <div class="rounded-xl bg-red-50 p-4 text-sm text-red-700">
            {{ $error }}
        </div>
    @endif

    @if ($selected)
        <div class="rounded-2xl border border-amber-300 bg-white p-6 shadow-sm">
            <p class="text-xs uppercase tracking-wide text-slate-400">Entregar al remitente</p>
            <p class="mt-1 font-semibold text-slate-800">{{ $selected->tracking_number }}</p>

            <dl class="mt-3 grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                <div>
                    <dt class="text-slate-400">Remitente</dt>
                    <dd class="font-medium text-slate-700">{{ $selected->sender_name }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400">Destino original</dt>
                    <dd class="font-medium text-slate-700">{{ $selected->destination_city }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-slate-400">Motivo de la devolución</dt>
                    <dd class="font-medium text-slate-700">{{ $selected->return_reason }}</dd>
                </div>
            </dl>

            <form wire:submit.prevent="handBack" class="mt-5 space-y-3">
                <div>
                    <label for="return-sender-doc" class="text-sm font-medium text-slate-600">
                        Cédula del remitente
                    </label>
                    <input
                        id="return-sender-doc"
                        wire:model="senderIdDoc"
                        autocomplete="off"
                        placeholder="V-12345678"
                        class="mt-2 w-full rounded-xl border px-4 py-3 outline-none transition focus:border-blue-800 focus:ring-2 focus:ring-blue-100"
                    >
                    @error('senderIdDoc')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col gap-2 sm:flex-row">
                    <button type="submit" class="flex-1 rounded-xl bg-blue-900 px-5 py-3 font-semibold text-white hover:bg-blue-800">
                        Confirmar entrega al remitente
                    </button>
                    <button type="button" wire:click="cancelSelection" class="rounded-xl border px-5 py-3 font-semibold text-slate-600 hover:bg-slate-50">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    @endif

    <div class="rounded-2xl border bg-white shadow-sm">
        <ul class="divide-y divide-slate-100">
            @forelse ($pending as $package)
                <li wire:key="return-{{ $package->id }}" class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <p class="font-semibold text-slate-800">{{ $package->tracking_number }}</p>
                        <p class="text-sm text-slate-500">
                            {{ $package->sender_name }} · destino {{ $package->destination_city }}
                        </p>
                        <p class="mt-1 text-xs text-slate-400">
                            En devolución desde {{ $package->return_requested_at?->format('d/m/Y') }} — {{ \Illuminate\Support\Str::limit($package->return_reason, 90) }}
                        </p>
                    </div>

                    <button
                        type="button"
                        wire:click="select({{ $package->id }})"
                        class="shrink-0 rounded-xl bg-amber-400 px-4 py-2 text-sm font-semibold text-[#111111] hover:bg-amber-300"
                    >
                        Entregar al remitente
                    </button>
                </li>
            @empty
                <li class="p-8 text-center text-sm text-slate-400">
                    No hay devoluciones pendientes en tu agencia.
                </li>
            @endforelse
        </ul>

        @if ($pending->hasPages())
            <div class="border-t px-4 py-3">
                {{ $pending->links() }}
            </div>
        @endif
    </div>
</div>
