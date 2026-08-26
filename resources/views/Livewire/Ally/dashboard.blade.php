<div class="space-y-6">

    {{-- Encabezado + selector de período --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="font-display text-2xl font-semibold text-[#0F172A]">
                {{ $ally->business_name }}
            </h2>

            <p class="text-sm text-slate-500">
                Resumen operativo y financiero
            </p>
        </div>

        <div class="inline-flex rounded-2xl border border-[#E2E8F0] bg-white p-1">
            <button
                wire:click="setPeriod('today')"
                class="rounded-xl px-4 py-1.5 text-sm font-medium transition
                {{ $period === 'today'
                    ? 'bg-blue-900 text-white'
                    : 'text-slate-500 hover:text-[#0F172A]' }}"
            >
                Hoy
            </button>

            <button
                wire:click="setPeriod('week')"
                class="rounded-xl px-4 py-1.5 text-sm font-medium transition
                {{ $period === 'week'
                    ? 'bg-blue-900 text-white'
                    : 'text-slate-500 hover:text-[#0F172A]' }}"
            >
                Esta semana
            </button>

            <button
                wire:click="setPeriod('month')"
                class="rounded-xl px-4 py-1.5 text-sm font-medium transition
                {{ $period === 'month'
                    ? 'bg-blue-900 text-white'
                    : 'text-slate-500 hover:text-[#0F172A]' }}"
            >
                Este mes
            </button>
        </div>
    </div>


    {{-- Tarjetas de resumen --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5">
            <p class="text-sm text-slate-500">
                Total facturado
            </p>

            <p class="mt-2 font-display text-2xl font-semibold text-[#0F172A]">
                ${{ number_format($totalBilledUsd, 2) }}
            </p>
        </div>


        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5">
            <p class="text-sm text-slate-500">
                Guías procesadas
            </p>

            <p class="mt-2 font-display text-2xl font-semibold text-[#0F172A]">
                {{ $processedCount }}
            </p>
        </div>


        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5">
            <p class="text-sm text-slate-500">
                Comisión del período
            </p>

            <p class="mt-2 font-display text-2xl font-semibold text-[#0F172A]">
                ${{ number_format($commissionBalanceUsd, 2) }}
            </p>
        </div>


        <div class="rounded-2xl border border-[#E2E8F0] bg-blue-900 p-5">
            <p class="text-sm text-blue-200">
                Saldo total por comisiones
            </p>

            <p class="mt-2 font-display text-2xl font-semibold text-white">
                ${{ number_format($totalCommissionBalanceUsd, 2) }}
            </p>
        </div>

    </div>


    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

        {{-- Cuadre de caja --}}
        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5">

            <h3 class="mb-4 font-display text-lg font-semibold text-[#0F172A]">
                Cuadre de caja
            </h3>

            @php
                $paymentLabels = [
                    'efectivo_usd' => 'Efectivo USD',
                    'efectivo_ves' => 'Efectivo VES',
                    'pago_movil' => 'Pago móvil',
                    'transferencia' => 'Transferencia',
                    'zelle' => 'Zelle',
                ];
            @endphp

            @if ($byPaymentMethod->isEmpty())

                <p class="text-sm text-slate-400">
                    Sin guías registradas en este período.
                </p>

            @else

                <div class="space-y-3">

                    @foreach ($byPaymentMethod as $row)

                        <div class="flex items-center justify-between text-sm">

                            <span class="text-slate-600">
                                {{ $paymentLabels[$row->payment_method] ?? $row->payment_method }}

                                <span class="text-slate-400">
                                    ({{ $row->guides }})
                                </span>
                            </span>

                            <span class="font-medium text-[#0F172A]">
                                ${{ number_format($row->total, 2) }}
                            </span>

                        </div>

                    @endforeach

                </div>

            @endif

        </div>


        {{-- Cobro en destino --}}
        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5">

            <h3 class="mb-4 font-display text-lg font-semibold text-[#0F172A]">
                Cobro en destino (COD)
            </h3>

            <div class="space-y-3 text-sm">

                <div class="flex items-center justify-between">

                    <span class="text-slate-600">
                        Pendiente de liquidar
                    </span>

                    <span class="font-medium text-amber-600">
                        ${{ number_format($codPendingUsd, 2) }}
                    </span>

                </div>

                <div class="flex items-center justify-between">

                    <span class="text-slate-600">
                        Liquidado
                    </span>

                    <span class="font-medium text-emerald-600">
                        ${{ number_format($codLiquidatedUsd, 2) }}
                    </span>

                </div>

            </div>

        </div>

    </div>

</div>
