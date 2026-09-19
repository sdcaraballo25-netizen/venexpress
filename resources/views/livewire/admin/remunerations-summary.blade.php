<div class="space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="font-display text-2xl font-semibold text-slate-900">Resumen de pagos pendientes</h2>
            <p class="mt-1 text-sm text-slate-500">
                Todo lo que se le debe hoy a Aliados (comisión) y Repartidores (remuneración), en una sola tabla.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button
                type="button"
                wire:click="sync"
                class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50"
            >
                ⟳ Sincronizar
            </button>

            @if ($rows->isNotEmpty())
                <button
                    type="button"
                    wire:click="exportExcel"
                    class="rounded-xl bg-blue-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-800"
                >
                    ⬇ Exportar Excel
                </button>
            @endif
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">{{ session('success') }}</div>
    @endif

    @unless ($bcvRate)
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            No hay ninguna tasa BCV registrada todavía — los saldos en Bs no se pueden calcular hasta que se registre una.
        </div>
    @endunless

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="rounded-2xl bg-blue-900 px-5 py-4 text-white">
            <p class="text-xs text-blue-200 uppercase tracking-wide">Total a pagar</p>
            <p class="font-display text-3xl font-bold mt-1">${{ number_format($totalUsd, 2) }}</p>
            <p class="mt-1 text-sm text-blue-200">
                {{ $totalVes !== null ? 'Bs. '.number_format($totalVes, 2) : 'Bs. N/A' }}
            </p>
        </div>
        <div class="rounded-2xl bg-slate-50 border border-slate-200 px-5 py-4">
            <p class="text-xs text-slate-500 uppercase tracking-wide">Producido por Aliados (USD)</p>
            <p class="font-display text-3xl font-bold text-[#0F172A] mt-1">${{ number_format($totalProducedByAllies, 2) }}</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        @if ($rows->isEmpty())
            <div class="px-5 py-12 text-center text-sm text-slate-500">No hay pagos pendientes por realizar.</div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-slate-400 border-b border-slate-200 bg-slate-50">
                            <th class="py-3 px-4">Rol</th>
                            <th class="py-3 px-4">RIF / Cédula</th>
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
                        @foreach ($rows as $row)
                            <tr wire:key="remuneration-row-{{ $row['role'] }}-{{ $loop->index }}" class="border-b border-slate-100 last:border-0">
                                <td class="py-3 px-4">
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-medium
                                        {{ $row['role'] === 'Aliado' ? 'bg-blue-50 text-blue-900' : 'bg-emerald-50 text-emerald-700' }}">
                                        {{ $row['role'] }}
                                    </span>
                                </td>
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
