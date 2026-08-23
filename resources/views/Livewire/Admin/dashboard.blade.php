@php
    $statusStyles = [
        'PENDIENTE' => [
            'dot' => 'bg-amber-400',
            'text' => 'text-amber-600',
            'bg' => 'bg-amber-50',
        ],
        'ACTIVO' => [
            'dot' => 'bg-blue-600',
            'text' => 'text-blue-700',
            'bg' => 'bg-blue-50',
        ],
        'RECHAZADO' => [
            'dot' => 'bg-red-500',
            'text' => 'text-red-600',
            'bg' => 'bg-red-50',
        ],
        'SUSPENDIDO' => [
            'dot' => 'bg-slate-500',
            'text' => 'text-slate-600',
            'bg' => 'bg-slate-50',
        ],
    ];
@endphp

<div class="min-h-screen bg-white flex font-sans">

    <main class="flex-1 p-8 lg:p-10 overflow-y-auto">

        {{-- =========================================================
             HEADER
        ========================================================== --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">

            <div>
                <h1 class="font-display text-3xl font-bold tracking-tight text-[#0F172A]">
                    ¡Hola, Admin! 👋
                </h1>

                <p class="text-sm text-[#64748B] mt-1">
                    {{ now()->translatedFormat('l, d \d\e F \d\e Y') }}
                </p>
            </div>

            <span class="px-3 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full text-xs font-medium flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                Sistema Operativo
            </span>

        </div>


        {{-- =========================================================
             BANNER
        ========================================================== --}}
        <div class="bg-blue-900 rounded-3xl p-8 mb-8 text-white shadow-sm flex flex-col md:flex-row justify-between items-center gap-6">

            <div>
                <h2 class="text-xl font-semibold mb-2">
                    Panel de Control General
                </h2>

                <p class="text-blue-200 text-sm max-w-xl">
                    Administra aliados, repartidores, clientes y paquetes
                    desde un solo lugar.
                </p>
            </div>

            <button
                type="button"
                class="bg-blue-600 hover:bg-blue-500 px-6 py-3 rounded-xl font-medium transition-colors whitespace-nowrap shadow-sm"
            >
                Generar Reporte Global
            </button>

        </div>


        {{-- =========================================================
             MÉTRICAS
        ========================================================== --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">

            {{-- COMERCIOS --}}
            <div class="bg-white rounded-2xl border border-[#E2E8F0] p-6 shadow-sm">

                <div class="flex justify-between items-start">

                    <p class="text-xs font-bold text-[#64748B] uppercase tracking-wider">
                        Comercios Asociados
                    </p>

                    <div class="p-2 bg-blue-50 rounded-lg text-blue-700">
                        🏢
                    </div>

                </div>

                <p class="font-display text-3xl font-bold mt-4 text-[#0F172A]">
                    {{ number_format($alliesCount) }}
                </p>

                <p class="text-xs text-[#64748B] mt-2">
                    Aliados aprobados
                </p>

            </div>


            {{-- REPARTIDORES --}}
            <div class="bg-white rounded-2xl border border-[#E2E8F0] p-6 shadow-sm">

                <div class="flex justify-between items-start">

                    <p class="text-xs font-bold text-[#64748B] uppercase tracking-wider">
                        Repartidores
                    </p>

                    <div class="p-2 bg-blue-50 rounded-lg text-blue-700">
                        🚚
                    </div>

                </div>

                <p class="font-display text-3xl font-bold mt-4 text-[#0F172A]">
                    {{ number_format($driversCount) }}
                </p>

                <p class="text-xs text-[#64748B] mt-2">
                    Registrados en el sistema
                </p>

            </div>


            {{-- POSTULACIONES --}}
            <div class="bg-white rounded-2xl border border-[#E2E8F0] p-6 shadow-sm">

                <div class="flex justify-between items-start">

                    <p class="text-xs font-bold text-[#64748B] uppercase tracking-wider">
                        Nuevas Postulaciones
                    </p>

                    <div class="p-2 bg-amber-50 rounded-lg text-amber-600">
                        📋
                    </div>

                </div>

                <p class="font-display text-3xl font-bold mt-4 text-[#0F172A]">
                    {{ number_format($postulacionesCount) }}
                </p>

                <p class="text-xs text-[#64748B] mt-2">
                    Pendientes de revisión
                </p>

            </div>


            {{-- CLIENTES --}}
            <div class="bg-white rounded-2xl border border-[#E2E8F0] p-6 shadow-sm">

                <div class="flex justify-between items-start">

                    <p class="text-xs font-bold text-[#64748B] uppercase tracking-wider">
                        Clientes
                    </p>

                    <div class="p-2 bg-blue-50 rounded-lg text-blue-700">
                        👤
                    </div>

                </div>

                <p class="font-display text-3xl font-bold mt-4 text-[#0F172A]">
                    {{ number_format($clientsCount) }}
                </p>

                <p class="text-xs text-[#64748B] mt-2">
                    Usuarios registrados
                </p>

            </div>

        </div>


        {{-- =========================================================
             CONTENIDO PRINCIPAL
        ========================================================== --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">


            {{-- =====================================================
                 ÚLTIMOS ALIADOS
            ====================================================== --}}
            <div class="bg-white rounded-2xl border border-[#E2E8F0] p-6 shadow-sm">

                <div class="flex justify-between items-center mb-5">

                    <div>
                        <h2 class="font-display text-lg font-bold text-[#0F172A]">
                            Últimos Aliados
                        </h2>

                        <p class="text-xs text-[#64748B] mt-1">
                            Comercios registrados recientemente
                        </p>
                    </div>

                    {{-- ENLACE A GESTIÓN DE ALIADOS --}}
                    <a
                        href="{{ route('admin.allies') }}"
                        class="text-sm font-medium text-blue-700 hover:text-blue-900 transition-colors"
                    >
                        Ver todos →
                    </a>

                </div>


                {{-- LISTADO DE ALIADOS --}}
                @if($recentAllies->count())

                    <div class="overflow-x-auto">

                        <table class="w-full text-sm">

                            <thead>
                                <tr class="text-left text-[#94A3B8] text-xs uppercase tracking-wider border-b border-[#E2E8F0]">

                                    <th class="pb-3 font-semibold">
                                        Aliado / Empresa
                                    </th>

                                    <th class="pb-3 font-semibold text-center">
                                        Estado
                                    </th>

                                    <th class="pb-3 font-semibold text-right">
                                        Fecha
                                    </th>

                                </tr>
                            </thead>


                            <tbody>

                                @foreach($recentAllies as $ally)

                                    @php
                                        $style = $statusStyles[$ally->status] ?? [
                                            'dot' => 'bg-slate-400',
                                            'text' => 'text-slate-600',
                                            'bg' => 'bg-slate-50',
                                        ];
                                    @endphp

                                    <tr class="border-b border-[#F1F5F9] last:border-0">

                                        {{-- EMPRESA --}}
                                        <td class="py-4">

                                            <p class="font-medium text-[#0F172A]">
                                                {{ $ally->business_name }}
                                            </p>

                                            <p class="text-xs text-[#64748B]">
                                                {{ $ally->city }}
                                            </p>

                                        </td>


                                        {{-- ESTADO --}}
                                        <td class="py-4 text-center">

                                            <span class="inline-flex items-center gap-1.5 {{ $style['bg'] }} {{ $style['text'] }} px-2 py-1 rounded-md text-xs font-semibold">

                                                <span class="h-1.5 w-1.5 rounded-full {{ $style['dot'] }}"></span>

                                                {{ ucfirst(strtolower($ally->status)) }}

                                            </span>

                                        </td>


                                        {{-- FECHA --}}
                                        <td class="py-4 text-right">

                                            <span class="text-xs text-[#64748B]">
                                                {{ $ally->created_at?->diffForHumans() }}
                                            </span>

                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                @else

                    {{-- SIN ALIADOS --}}
                    <div class="py-10 text-center">

                        <div class="text-4xl mb-3">
                            🏢
                        </div>

                        <p class="font-medium text-[#0F172A]">
                            No hay aliados registrados
                        </p>

                        <p class="text-sm text-[#64748B] mt-1">
                            Los nuevos aliados aparecerán aquí.
                        </p>

                    </div>

                @endif

            </div>


            {{-- =====================================================
                 RESUMEN DEL SISTEMA
            ====================================================== --}}
            <div class="flex flex-col gap-5">


                {{-- PAQUETES --}}
                <div class="bg-white rounded-2xl border border-[#E2E8F0] p-6 shadow-sm">

                    <div class="flex items-center justify-between">

                        <div class="flex items-center gap-4">

                            <div class="w-12 h-12 bg-blue-50 text-blue-700 rounded-xl flex items-center justify-center text-xl">
                                📦
                            </div>

                            <div>

                                <h3 class="font-display font-bold text-[#0F172A]">
                                    Paquetes
                                </h3>

                                <p class="text-sm text-[#64748B]">
                                    Total registrados
                                </p>

                            </div>

                        </div>

                        <span class="text-2xl font-bold text-[#0F172A]">
                            {{ number_format($totalPackages) }}
                        </span>

                    </div>

                </div>


                {{-- TASA BCV --}}
                <div class="bg-white rounded-2xl border border-[#E2E8F0] p-6 shadow-sm">

                    <div class="flex items-center justify-between">

                        <div class="flex items-center gap-4">

                            <div class="w-12 h-12 bg-emerald-50 text-emerald-700 rounded-xl flex items-center justify-center text-xl">
                                $
                            </div>

                            <div>

                                <h3 class="font-display font-bold text-[#0F172A]">
                                    Tasa BCV
                                </h3>

                                <p class="text-sm text-[#64748B]">
                                    Tasa actual del sistema
                                </p>

                            </div>

                        </div>

                        <span class="text-xl font-bold text-[#0F172A]">

                            @if($currentRate)
                                {{ number_format($currentRate->rate, 2, ',', '.') }}
                            @else
                                —
                            @endif

                        </span>

                    </div>

                </div>


                {{-- ESTADOS DE PAQUETES --}}
                <div class="bg-white rounded-2xl border border-[#E2E8F0] p-6 shadow-sm">

                    <div class="flex items-center justify-between mb-4">

                        <h3 class="font-display font-bold text-[#0F172A]">
                            Estado de paquetes
                        </h3>

                        <span class="text-xs text-[#64748B]">
                            {{ number_format($totalPackages) }} total
                        </span>

                    </div>


                    <div class="space-y-3">

                        @foreach($statuses as $status)

                            @php
                                $count = $statusCounts[$status] ?? 0;
                            @endphp

                            <div class="flex items-center justify-between">

                                <div class="flex items-center gap-2">

                                    <span class="w-2 h-2 rounded-full bg-blue-600"></span>

                                    <span class="text-sm text-[#475569]">
                                        {{ ucfirst(strtolower(str_replace('_', ' ', $status))) }}
                                    </span>

                                </div>

                                <span class="text-sm font-semibold text-[#0F172A]">
                                    {{ number_format($count) }}
                                </span>

                            </div>

                        @endforeach

                    </div>

                </div>

            </div>

        </div>

    </main>

</div>