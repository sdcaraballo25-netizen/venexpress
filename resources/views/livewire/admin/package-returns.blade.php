<div class="space-y-6">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="font-display text-2xl font-semibold text-[#111111]">
                Devoluciones al remitente
            </h2>
            <p class="text-sm text-slate-500">
                Inicia la devolución de un envío que no se pudo entregar. La agencia de origen la cierra al entregárselo al remitente.
            </p>
        </div>

        <a
            href="{{ route('admin.incidents') }}"
            wire:navigate
            class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
        >
            Ver incidencias
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

        {{-- Buscar --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="font-display text-lg font-semibold text-slate-900">
                Buscar guía
            </h3>

            <form wire:submit.prevent="search" class="mt-5">
                <label for="return-tracking" class="text-sm font-medium text-slate-600">
                    Número de guía
                </label>

                <div class="mt-2 flex gap-2">
                    <input
                        id="return-tracking"
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
                    <p class="text-xs uppercase tracking-wide text-slate-400">Guía</p>
                    <p class="mt-1 font-semibold text-slate-800">{{ $package->tracking_number }}</p>

                    <dl class="mt-3 grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="text-slate-400">Estado</dt>
                            <dd class="font-medium text-slate-700">{{ $package->statusLabel() }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-400">Agencia de origen</dt>
                            <dd class="font-medium text-slate-700">{{ $package->ally?->business_name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-400">Remitente</dt>
                            <dd class="font-medium text-slate-700">{{ $package->sender_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-400">Destinatario</dt>
                            <dd class="font-medium text-slate-700">{{ $package->recipient_name }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-slate-400">Ruta</dt>
                            <dd class="font-medium text-slate-700">{{ $package->origin_city }} → {{ $package->destination_city }}</dd>
                        </div>
                        @if ($package->is_cod)
                            <div class="sm:col-span-2">
                                <dt class="text-slate-400">Cobro contra entrega</dt>
                                <dd class="font-medium text-slate-700">
                                    ${{ number_format((float) $package->cod_amount_usd, 2) }}
                                    @if ($package->cod_status === \App\Models\Package::COD_CANCELADO)
                                        <span class="text-slate-500">(cancelado por devolución)</span>
                                    @elseif ($package->isReturnable())
                                        <span class="text-amber-700">(se cancelará al iniciar la devolución)</span>
                                    @endif
                                </dd>
                            </div>
                        @endif
                        @if ($package->return_reason)
                            <div class="sm:col-span-2">
                                <dt class="text-slate-400">Motivo de la devolución</dt>
                                <dd class="font-medium text-slate-700">{{ $package->return_reason }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            @endif
        </div>

        {{-- Iniciar devolución --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="font-display text-lg font-semibold text-slate-900">
                Iniciar devolución
            </h3>

            @if (! $package)
                <p class="mt-5 text-sm text-slate-500">
                    Busca primero la guía que quieres devolver.
                </p>
            @elseif (! $package->isReturnable())
                <p class="mt-5 rounded-xl bg-slate-50 p-4 text-sm text-slate-600">
                    @switch($package->current_status)
                        @case(\App\Models\Package::STATUS_RECIBIDO_AGENCIA)
                            Esta guía todavía no ha salido de la agencia de origen: no hay nada que devolver por la red.
                            @break
                        @case(\App\Models\Package::STATUS_ENTREGADO)
                            Esta guía ya fue entregada al destinatario.
                            @break
                        @case(\App\Models\Package::STATUS_EN_DEVOLUCION)
                            Esta guía ya está en devolución. La agencia de origen la cerrará al entregarla al remitente.
                            @break
                        @case(\App\Models\Package::STATUS_DEVUELTO)
                            Esta guía ya fue devuelta al remitente.
                            @break
                        @default
                            Esta guía no se puede devolver en su estado actual.
                    @endswitch
                </p>
            @else
                <div class="mt-5 space-y-4">
                    <div>
                        <label for="return-reason" class="text-sm font-medium text-slate-600">
                            Motivo
                        </label>
                        <textarea
                            id="return-reason"
                            wire:model="returnReason"
                            rows="4"
                            placeholder="Ej. Destinatario ausente en tres intentos; no retiró en el plazo."
                            class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-900 focus:ring-blue-900"
                        ></textarea>
                        @error('returnReason')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <p class="text-xs text-slate-500">
                        Sin reembolso ni cargo extra: el costo del envío y la comisión de la agencia se mantienen.
                        El traslado de regreso a la agencia de origen lo coordina operaciones.
                    </p>

                    <button
                        type="button"
                        @click.prevent="$store.confirm.open({
                            message: '¿Iniciar la devolución al remitente de la guía {{ $package->tracking_number }}?',
                            confirmText: 'Iniciar devolución',
                            variant: 'warning',
                            onConfirm: () => $wire.startReturn(),
                        })"
                        class="w-full rounded-xl bg-red-600 px-5 py-3 text-sm font-semibold text-white hover:bg-red-700"
                    >
                        Iniciar devolución
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- En devolución --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-6 py-4">
            <h3 class="font-display text-lg font-semibold text-slate-900">En devolución</h3>
            <p class="text-sm text-slate-500">Pendientes de que la agencia de origen las entregue al remitente.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-6 py-3">Guía</th>
                        <th class="px-6 py-3">Agencia de origen</th>
                        <th class="px-6 py-3">Remitente</th>
                        <th class="px-6 py-3">Motivo</th>
                        <th class="px-6 py-3">Desde</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($inReturn as $returning)
                        <tr wire:key="in-return-{{ $returning->id }}">
                            <td class="whitespace-nowrap px-6 py-3 font-medium text-slate-800">{{ $returning->tracking_number }}</td>
                            <td class="px-6 py-3 text-slate-600">{{ $returning->ally?->business_name ?? '—' }}</td>
                            <td class="px-6 py-3 text-slate-600">{{ $returning->sender_name }}</td>
                            <td class="px-6 py-3 text-slate-600">{{ \Illuminate\Support\Str::limit($returning->return_reason, 80) }}</td>
                            <td class="whitespace-nowrap px-6 py-3 text-slate-500">{{ $returning->return_requested_at?->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-400">No hay devoluciones en curso.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($inReturn->hasPages())
            <div class="border-t border-slate-200 px-6 py-3">
                {{ $inReturn->links() }}
            </div>
        @endif
    </div>

    {{-- Devueltas --}}
    @if ($recentlyReturned->isNotEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-4">
                <h3 class="font-display text-lg font-semibold text-slate-900">Devueltas recientemente</h3>
            </div>

            <ul class="divide-y divide-slate-100 text-sm">
                @foreach ($recentlyReturned as $returned)
                    <li wire:key="returned-{{ $returned->id }}" class="flex flex-col gap-1 px-6 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <span class="font-medium text-slate-800">{{ $returned->tracking_number }}</span>
                        <span class="text-slate-500">
                            {{ $returned->ally?->business_name ?? '—' }} · {{ $returned->returned_at?->format('d/m/Y H:i') }}
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
