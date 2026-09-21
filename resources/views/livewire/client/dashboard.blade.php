@php
    /**
     * Color del punto de estado en la lista compacta. Puramente de
     * presentación (no vive en el modelo porque, por ahora, solo esta
     * vista lo necesita).
     */
    $statusDotColor = fn (string $status) => match ($status) {
        \App\Models\Package::STATUS_LISTO_RETIRO => 'bg-amber-500',
        \App\Models\Package::STATUS_EN_TRANSITO_NACIONAL => 'bg-blue-500',
        \App\Models\Package::STATUS_EN_HUB => 'bg-indigo-500',
        \App\Models\Package::STATUS_ENTREGADO => 'bg-emerald-500',
        default => 'bg-slate-400',
    };
@endphp

<div class="space-y-6">

    {{-- Mensajes --}}
    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    {{-- Encabezado --}}
    <div>
        <h1 class="font-display text-2xl font-bold text-slate-900">
            Hola, {{ auth()->user()->name }}
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Toca un pedido para ver su detalle completo.
        </p>
    </div>

    {{-- Pestañas --}}
    <div class="flex gap-2 border-b border-slate-200">
        <button
            type="button"
            wire:click="showPending"
            @class([
                'px-4 py-2.5 text-sm font-semibold border-b-2 -mb-px transition',
                'border-blue-900 text-blue-900' => $activeTab === 'pending',
                'border-transparent text-slate-500 hover:text-slate-700' => $activeTab !== 'pending',
            ])
        >
            Pendientes
        </button>

        <button
            type="button"
            wire:click="showHistory"
            @class([
                'px-4 py-2.5 text-sm font-semibold border-b-2 -mb-px transition',
                'border-blue-900 text-blue-900' => $activeTab === 'history',
                'border-transparent text-slate-500 hover:text-slate-700' => $activeTab !== 'history',
            ])
        >
            Historial de entregas
        </button>
    </div>

    @if ($activeTab === 'pending')

        {{-- =====================================================
             PENDIENTES — lista compacta, expandible por fila
        ====================================================== --}}
        @if ($packages->isEmpty())
            <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center">
                <p class="text-sm text-slate-500">
                    No tienes paquetes pendientes en este momento.
                </p>
            </div>
        @else
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                <div class="divide-y divide-slate-100">
                    @foreach ($packages as $package)
                        @php
                            $isActionable = $package->requires_delivery
                                && $package->delivery_status === \App\Models\Package::DELIVERY_PENDING
                                && $package->current_status === \App\Models\Package::STATUS_LISTO_RETIRO;
                        @endphp

                        <div x-data="{ open: false }" wire:key="pending-{{ $package->id }}">

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
                    @endforeach
                </div>
            </div>
        @endif

    @else

        {{-- =====================================================
             HISTORIAL — misma lista compacta, sin acciones
        ====================================================== --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">

            <div class="flex flex-wrap items-end gap-3 border-b border-slate-100 px-4 py-3 sm:px-5">
                <div>
                    <label class="block text-xs font-medium text-slate-500">
                        Desde
                    </label>
                    <input
                        type="date"
                        wire:model.live="historyFrom"
                        class="mt-1 rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-500">
                        Hasta
                    </label>
                    <input
                        type="date"
                        wire:model.live="historyTo"
                        class="mt-1 rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                </div>

                @if ($historyFrom !== '' || $historyTo !== '')
                    <button
                        type="button"
                        wire:click="clearHistoryFilters"
                        class="text-xs font-semibold text-blue-700 hover:text-blue-900"
                    >
                        Limpiar filtro
                    </button>
                @endif
            </div>

            @if ($historyPackages->isEmpty())
                <div class="p-8 text-center">
                    <p class="text-sm text-slate-500">
                        No hay entregas en tu historial{{ ($historyFrom !== '' || $historyTo !== '') ? ' para el rango de fechas elegido' : '' }}.
                    </p>
                </div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach ($historyPackages as $package)
                        <div x-data="{ open: false }" wire:key="history-{{ $package->id }}">

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
                                    <span class="text-xs font-medium text-emerald-600">
                                        Entregado
                                        @if ($package->delivery_completed_at)
                                            · {{ $package->delivery_completed_at->format('d/m/Y') }}
                                        @endif
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

                                @if ($package->receiver_name)
                                    <p class="mt-2 text-sm text-slate-600">
                                        Recibido por
                                        <span class="font-medium text-slate-800">{{ $package->receiver_name }}</span>
                                    </p>
                                @endif

                                @if ($package->histories->isNotEmpty())
                                    <div class="mt-3">
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
                    @endforeach
                </div>
            @endif
        </div>

        @if (! $historyPackages->isEmpty())
            <div class="pt-2">
                {{ $historyPackages->links() }}
            </div>
        @endif

    @endif

</div>
