@php
    // Color por etapa del ciclo de vida del paquete — de neutral (recién recibido) a completo (entregado).
    $statusStyles = [
        'RECIBIDO_AGENCIA'        => ['dot' => 'bg-slate-400',   'text' => 'text-slate-600',   'bg' => 'bg-slate-50'],
        'RECOLECTADO_VENEXPRESS'  => ['dot' => 'bg-blue-400',    'text' => 'text-blue-600',    'bg' => 'bg-blue-50'],
        'EN_HUB'                  => ['dot' => 'bg-amber-400',   'text' => 'text-amber-600',   'bg' => 'bg-amber-50'],
        'EN_TRANSITO_NACIONAL'    => ['dot' => 'bg-[#FF6A1A]',   'text' => 'text-[#C2410C]',   'bg' => 'bg-orange-50'],
        'LISTO_RETIRO'            => ['dot' => 'bg-teal-400',    'text' => 'text-teal-600',    'bg' => 'bg-teal-50'],
        'ENTREGADO'                => ['dot' => 'bg-emerald-400', 'text' => 'text-emerald-600', 'bg' => 'bg-emerald-50'],
    ];
@endphp

<div>
    <div class="mb-8">
        <h1 class="font-display text-2xl font-semibold tracking-tight">Dashboard</h1>
        <p class="text-sm text-[#64748B] mt-1">Resumen operativo de Venexpress.</p>
    </div>

    {{-- Tarjetas resumen --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-[#E2E8F0] p-5">
            <p class="text-xs font-medium text-[#64748B] uppercase tracking-wide">Paquetes totales</p>
            <p class="font-display text-3xl font-semibold mt-2">{{ $totalPackages }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-[#E2E8F0] p-5">
            <p class="text-xs font-medium text-[#64748B] uppercase tracking-wide">Tasa BCV vigente</p>
            @if ($currentRate)
                <p class="font-display text-3xl font-semibold mt-2">{{ number_format($currentRate->rate, 2) }}</p>
                <p class="text-xs text-[#94A3B8] mt-1">
                    Actualizada {{ \Illuminate\Support\Carbon::parse($currentRate->effective_date)->format('d/m/Y') }}
                </p>
            @else
                <p class="text-base font-semibold text-[#C2410C] mt-2">Sin tasa registrada</p>
            @endif
        </div>

        <div class="bg-white rounded-2xl border border-[#E2E8F0] p-5">
            <p class="text-xs font-medium text-[#64748B] uppercase tracking-wide">Aliados</p>
            <p class="font-display text-3xl font-semibold mt-2">{{ $alliesCount }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-[#E2E8F0] p-5">
            <p class="text-xs font-medium text-[#64748B] uppercase tracking-wide">Choferes</p>
            <p class="font-display text-3xl font-semibold mt-2">{{ $driversCount }}</p>
        </div>
    </div>

    {{-- Paquetes por estado --}}
    <div class="bg-white rounded-2xl border border-[#E2E8F0] p-5 mb-6">
        <h2 class="font-display text-base font-semibold mb-1">Paquetes por estado</h2>
        <p class="text-xs text-[#94A3B8] mb-4">Progresión del ciclo de vida, de recibido a entregado.</p>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            @foreach ($statuses as $status)
                @php($style = $statusStyles[$status] ?? ['dot' => 'bg-slate-300', 'text' => 'text-slate-500', 'bg' => 'bg-slate-50'])
                <div class="rounded-xl {{ $style['bg'] }} p-3 text-center">
                    <span class="inline-block h-1.5 w-1.5 rounded-full {{ $style['dot'] }} mb-2"></span>
                    <p class="font-display text-xl font-semibold {{ $style['text'] }}">{{ $statusCounts[$status] ?? 0 }}</p>
                    <p class="text-[11px] text-[#64748B] mt-0.5 leading-tight">
                        {{ ucwords(strtolower(str_replace('_', ' ', $status))) }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Últimos paquetes --}}
    <div class="bg-white rounded-2xl border border-[#E2E8F0] p-5">
        <h2 class="font-display text-base font-semibold mb-4">Últimos paquetes</h2>

        @if ($recentPackages->isEmpty())
            <div class="text-center py-10">
                <p class="text-sm text-[#64748B]">Todavía no hay paquetes registrados.</p>
                <p class="text-xs text-[#94A3B8] mt-1">En cuanto se cree el primero en la Taquilla Aliada, aparecerá aquí.</p>
            </div>
        @else
            <div class="overflow-x-auto -mx-5">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-[#94A3B8] text-xs uppercase tracking-wide border-b border-[#E2E8F0]">
                            <th class="py-2 px-5 font-medium">Tracking</th>
                            <th class="py-2 px-5 font-medium">Estado</th>
                            <th class="py-2 px-5 font-medium">Creado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentPackages as $package)
                            @php($style = $statusStyles[$package->current_status] ?? ['dot' => 'bg-slate-300', 'text' => 'text-slate-500', 'bg' => 'bg-slate-50'])
                            <tr class="border-b border-[#F1F5F9] last:border-0">
                                <td class="py-3 px-5">
                                    <span class="font-tracking text-xs tracking-wide border border-dashed border-[#CBD5E1] rounded px-2 py-1 text-[#334155]">
                                        {{ $package->tracking_number }}
                                    </span>
                                </td>
                                <td class="py-3 px-5">
                                    <span class="inline-flex items-center gap-1.5 {{ $style['text'] }} text-xs font-medium">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $style['dot'] }}"></span>
                                        {{ ucwords(strtolower(str_replace('_', ' ', $package->current_status))) }}
                                    </span>
                                </td>
                                <td class="py-3 px-5 text-[#64748B]">
                                    {{ $package->created_at->format('d/m/Y H:i') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
