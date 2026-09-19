<div class="space-y-8">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="font-display text-2xl font-semibold text-slate-900">Resumen de pagos pendientes</h2>
            <p class="mt-1 text-sm text-slate-500">
                Lo que se le debe hoy a Aliados (comisión) y a Repartidores (tarifa fija por entrega), en tablas separadas.
            </p>
        </div>

        <button
            type="button"
            wire:click="sync"
            class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50"
        >
            ⟳ Sincronizar
        </button>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">{{ session('success') }}</div>
    @endif

    @unless ($bcvRate)
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            No hay ninguna tasa BCV registrada todavía — los saldos en Bs no se pueden calcular hasta que se registre una.
        </div>
    @endunless

    {{-- ==========================================================
         ALIADOS
    =========================================================== --}}
    <div>
        <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
            <div>
                <h3 class="font-display text-lg font-semibold text-slate-900">Aliados</h3>
                <p class="text-sm text-slate-500">Comisión por porcentaje de venta.</p>
            </div>

            @if ($allyRows->isNotEmpty())
                <button
                    type="button"
                    wire:click="exportAlliesExcel"
                    class="rounded-xl bg-blue-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-800"
                >
                    ⬇ Exportar Excel
                </button>
            @endif
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 mb-4">
            <div class="rounded-2xl bg-blue-900 px-5 py-4 text-white">
                <p class="text-xs text-blue-200 uppercase tracking-wide">Total a pagar a Aliados</p>
                <p class="font-display text-3xl font-bold mt-1">${{ number_format($allyTotalUsd, 2) }}</p>
                <p class="mt-1 text-sm text-blue-200">
                    {{ $allyTotalVes !== null ? 'Bs. '.number_format($allyTotalVes, 2) : 'Bs. N/A' }}
                </p>
            </div>
            <div class="rounded-2xl bg-slate-50 border border-slate-200 px-5 py-4">
                <p class="text-xs text-slate-500 uppercase tracking-wide">Producido por Aliados (USD)</p>
                <p class="font-display text-3xl font-bold text-[#0F172A] mt-1">${{ number_format($allyTotalProducedUsd, 2) }}</p>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            @if ($allyRows->isEmpty())
                <div class="px-5 py-12 text-center text-sm text-slate-500">No hay comisiones pendientes por pagar.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wide text-slate-400 border-b border-slate-200 bg-slate-50">
                                <th class="py-3 px-4">RIF</th>
                                <th class="py-3 px-4">Nombre</th>
                                <th class="py-3 px-4">Correo</th>
                                <th class="py-3 px-4">Cuenta</th>
                                <th class="py-3 px-4">Cédula titular</th>
                                <th class="py-3 px-4">Paquetes</th>
                                <th class="py-3 px-4">Producido USD</th>
                                <th class="py-3 px-4">Saldo USD</th>
                                <th class="py-3 px-4">Saldo Bs</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($allyRows as $row)
                                <tr wire:key="ally-row-{{ $loop->index }}" class="border-b border-slate-100 last:border-0">
                                    <td class="py-3 px-4 text-slate-600">{{ $row['doc'] ?: '—' }}</td>
                                    <td class="py-3 px-4 font-medium text-[#0F172A]">{{ $row['name'] ?: '—' }}</td>
                                    <td class="py-3 px-4 text-slate-500">{{ $row['email'] ?: '—' }}</td>
                                    <td class="py-3 px-4 text-slate-600">
                                        @if ($row['account_number'])
                                            {{ $row['account_number'] }}
                                        @else
                                            <span class="text-amber-600">Sin registrar</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-slate-600">{{ $row['holder_id'] ?: '—' }}</td>
                                    <td class="py-3 px-4 text-slate-500">{{ $row['packages'] }}</td>
                                    <td class="py-3 px-4 text-slate-600">${{ number_format($row['produced_usd'], 2) }}</td>
                                    <td class="py-3 px-4 font-semibold text-[#0F172A]">${{ number_format($row['balance_usd'], 2) }}</td>
                                    <td class="py-3 px-4 font-semibold text-[#0F172A]">
                                        {{ $row['balance_ves'] !== null ? 'Bs. '.number_format($row['balance_ves'], 2) : 'N/A' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- ==========================================================
         REPARTIDORES
    =========================================================== --}}
    <div>
        <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
            <div>
                <h3 class="font-display text-lg font-semibold text-slate-900">Repartidores</h3>
                <p class="text-sm text-slate-500">Tarifa fija por paquete entregado.</p>
            </div>

            @if ($driverRows->isNotEmpty())
                <button
                    type="button"
                    wire:click="exportDriversExcel"
                    class="rounded-xl bg-blue-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-800"
                >
                    ⬇ Exportar Excel
                </button>
            @endif
        </div>

        <div class="rounded-2xl bg-blue-900 px-5 py-4 text-white mb-4 sm:max-w-xs">
            <p class="text-xs text-blue-200 uppercase tracking-wide">Total a pagar a Repartidores</p>
            <p class="font-display text-3xl font-bold mt-1">${{ number_format($driverTotalUsd, 2) }}</p>
            <p class="mt-1 text-sm text-blue-200">
                {{ $driverTotalVes !== null ? 'Bs. '.number_format($driverTotalVes, 2) : 'Bs. N/A' }}
            </p>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            @if ($driverRows->isEmpty())
                <div class="px-5 py-12 text-center text-sm text-slate-500">No hay remuneraciones pendientes por pagar.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wide text-slate-400 border-b border-slate-200 bg-slate-50">
                                <th class="py-3 px-4">Cédula</th>
                                <th class="py-3 px-4">Nombre</th>
                                <th class="py-3 px-4">Correo</th>
                                <th class="py-3 px-4">Cuenta</th>
                                <th class="py-3 px-4">Cédula titular</th>
                                <th class="py-3 px-4">Paquetes</th>
                                <th class="py-3 px-4">Producido USD</th>
                                <th class="py-3 px-4">Saldo USD</th>
                                <th class="py-3 px-4">Saldo Bs</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($driverRows as $row)
                                <tr wire:key="driver-row-{{ $loop->index }}" class="border-b border-slate-100 last:border-0">
                                    <td class="py-3 px-4 text-slate-600">{{ $row['doc'] ?: '—' }}</td>
                                    <td class="py-3 px-4 font-medium text-[#0F172A]">{{ $row['name'] ?: '—' }}</td>
                                    <td class="py-3 px-4 text-slate-500">{{ $row['email'] ?: '—' }}</td>
                                    <td class="py-3 px-4 text-slate-600">
                                        @if ($row['account_number'])
                                            {{ $row['account_number'] }}
                                        @else
                                            <span class="text-amber-600">Sin registrar</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-slate-600">{{ $row['holder_id'] ?: '—' }}</td>
                                    <td class="py-3 px-4 text-slate-500">{{ $row['packages'] }}</td>
                                    <td class="py-3 px-4 text-slate-600">${{ number_format($row['produced_usd'], 2) }}</td>
                                    <td class="py-3 px-4 font-semibold text-[#0F172A]">${{ number_format($row['balance_usd'], 2) }}</td>
                                    <td class="py-3 px-4 font-semibold text-[#0F172A]">
                                        {{ $row['balance_ves'] !== null ? 'Bs. '.number_format($row['balance_ves'], 2) : 'N/A' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
