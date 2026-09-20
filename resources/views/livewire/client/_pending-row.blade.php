@php
    $isActionable = $package->requires_delivery
        && $package->delivery_status === \App\Models\Package::DELIVERY_PENDING
        && $package->current_status === \App\Models\Package::STATUS_LISTO_RETIRO;
@endphp

<div x-data="{ open: false }" wire:key="{{ $rowKeyPrefix }}-{{ $package->id }}">

    <button
        type="button"
        @click="open = !open"
        :aria-expanded="open"
        class="flex w-full items-center gap-3 px-4 py-3 text-left hover:bg-slate-50 sm:px-5"
    >
        <span class="h-2 w-2 shrink-0 rounded-full {{ $statusDotColor($package->current_status) }}"></span>

        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-baseline gap-x-2">
                <span class="font-tracking text-sm font-semibold text-slate-900">
                    {{ $package->tracking_number }}
                </span>

                <span class="truncate text-xs text-slate-400">
                    {{ $package->origin_city }} → {{ $package->destination_city }}
                </span>
            </div>
        </div>

        <div class="flex shrink-0 items-center gap-3">
            @if ($isActionable)
                <span class="hidden text-xs font-semibold text-amber-600 sm:inline">
                    Acción requerida
                </span>
            @endif

            <span class="text-xs font-medium text-slate-500">
                {{ $package->statusLabel() }}
            </span>

            <svg
                class="h-4 w-4 shrink-0 text-slate-400 transition-transform"
                :class="{ 'rotate-180': open }"
                fill="none" stroke="currentColor" viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
    </button>

    <div x-show="open" x-cloak class="border-t border-slate-100 bg-slate-50/60 px-4 py-4 sm:px-5">

        <p class="text-xs font-medium text-slate-500">
            @if ($package->client_role === 'sender')
                Enviado por ti
            @else
                Para ti
            @endif
        </p>

        {{-- Entrega a domicilio --}}
        @if ($package->requires_delivery)
            <div class="mt-3 rounded-xl border border-blue-100 bg-blue-50 p-4">
                <p class="text-sm font-semibold text-blue-900">
                    Entrega a domicilio
                </p>

                @if ($package->delivery_address)
                    <p class="mt-1 text-sm text-blue-800">
                        {{ $package->delivery_address }}
                    </p>
                @endif

                @if ($package->delivery_status === \App\Models\Package::DELIVERY_PENDING)

                    @if ($isActionable)
                        <div class="mt-4">
                            <button
                                type="button"
                                wire:click="acceptDelivery({{ $package->id }})"
                                wire:loading.attr="disabled"
                                class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-60"
                            >
                                Confirmar recepción a domicilio
                            </button>
                        </div>
                    @else
                        <div class="mt-3 rounded-lg bg-blue-100 p-3 text-sm text-blue-800">
                            Podrás confirmar la recepción cuando tu paquete esté Listo para Retiro.
                        </div>
                    @endif

                @elseif ($package->delivery_status === \App\Models\Package::DELIVERY_ACCEPTED)
                    <div class="mt-3 rounded-lg bg-emerald-100 p-3 text-sm text-emerald-700">
                        Confirmaste la recepción a domicilio. Un repartidor se pondrá en camino.
                    </div>

                @elseif ($package->delivery_status === \App\Models\Package::DELIVERY_REJECTED)
                    <div class="mt-3 rounded-lg bg-red-100 p-3 text-sm text-red-700">
                        Se rechazó esta entrega a domicilio.
                    </div>
                @endif
            </div>
        @else
            {{-- Retiro en agencia (requires_delivery = false) --}}
            @if ($package->current_status === \App\Models\Package::STATUS_LISTO_RETIRO)
                <div class="mt-3 rounded-xl border border-amber-100 bg-amber-50 p-4">
                    <p class="text-sm font-semibold text-amber-800">
                        Listo para retiro en agencia
                    </p>
                    <p class="mt-1 text-sm text-amber-700">
                        Pasa por la agencia de destino con tu cédula para retirarlo.
                    </p>
                </div>
            @endif
        @endif

        {{-- Historial --}}
        @if ($package->histories->isNotEmpty())
            <div class="mt-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                    Últimos movimientos
                </p>

                <div class="mt-2 space-y-1.5">
                    @foreach ($package->histories->sortByDesc('created_at')->take(4) as $history)
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span class="text-slate-600">
                                {{ $history->eventTypeLabel() }}
                            </span>

                            <span class="text-xs text-slate-400">
                                {{ $history->created_at?->format('d/m/Y H:i') }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</div>
