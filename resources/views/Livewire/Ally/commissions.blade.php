<div class="space-y-6">

    <div>
        <h2 class="font-display text-2xl font-semibold text-[#0F172A]">
            Comisiones y saldo
        </h2>
        <p class="text-sm text-slate-500">
            Comisión configurada: {{ number_format($ally->commission_percentage, 2) }}% por guía registrada
        </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Comisión acumulada (histórico)</p>
            <p class="mt-1 font-display text-2xl font-semibold text-blue-900">
                ${{ number_format($totalCommissionUsd, 2) }}
            </p>
            <p class="text-xs text-slate-400 mt-1">{{ $totalPackages }} guías registradas</p>
        </div>

        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Comisión este mes</p>
            <p class="mt-1 font-display text-2xl font-semibold text-[#0F172A]">
                ${{ number_format($monthCommissionUsd, 2) }}
            </p>
            <p class="text-xs text-slate-400 mt-1">{{ $monthPackages }} guías este mes</p>
        </div>

        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Promedio por guía</p>
            <p class="mt-1 font-display text-2xl font-semibold text-[#0F172A]">
                ${{ number_format($averageCommissionUsd, 2) }}
            </p>
            <p class="text-xs text-slate-400 mt-1">sobre el histórico total</p>
        </div>

        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">% de comisión actual</p>
            <p class="mt-1 font-display text-2xl font-semibold text-[#0F172A]">
                {{ number_format($ally->commission_percentage, 2) }}%
            </p>
            <p class="text-xs text-slate-400 mt-1">definido por Venexpress</p>
        </div>
    </div>

    <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
        <h3 class="font-display text-lg font-semibold text-[#0F172A] mb-4">
            Últimos 6 meses
        </h3>

        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-slate-500 border-b border-slate-200">
                    <th class="py-2 font-medium">Mes</th>
                    <th class="py-2 font-medium">Guías</th>
                    <th class="py-2 font-medium text-right">Comisión (USD)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($monthlyBreakdown as $month)
                    <tr class="border-b border-slate-100 last:border-0">
                        <td class="py-2 capitalize text-[#0F172A]">{{ $month['label'] }}</td>
                        <td class="py-2 text-slate-600">{{ $month['packages'] }}</td>
                        <td class="py-2 text-right font-medium text-[#0F172A]">
                            ${{ number_format($month['commission_usd'], 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">
        Esta sección es solo informativa. La liquidación de comisiones a agencias aliadas se gestionará próximamente desde el panel de administración.
    </div>
</div>
