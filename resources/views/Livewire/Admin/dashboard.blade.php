@php
    // Paleta de estados adaptada a tonos más sobrios y azules donde aplica
    $statusStyles = [
        'POSTULADO'          => ['dot' => 'bg-slate-400',   'text' => 'text-slate-600',   'bg' => 'bg-slate-50'],
        'ACTIVO'             => ['dot' => 'bg-blue-600',    'text' => 'text-blue-700',    'bg' => 'bg-blue-50'],
        'EN_REVISION'        => ['dot' => 'bg-amber-400',   'text' => 'text-amber-600',   'bg' => 'bg-amber-50'],
        'SUSPENDIDO'         => ['dot' => 'bg-red-500',     'text' => 'text-red-600',     'bg' => 'bg-red-50'],
    ];
@endphp

<div class="min-h-screen bg-white flex font-sans">

    {{-- ================= CONTENIDO PRINCIPAL ================= --}}
    <main class="flex-1 p-8 lg:p-10 overflow-y-auto">

        {{-- Cabecera --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h1 class="font-display text-3xl font-bold tracking-tight text-[#0F172A]">¡Hola, Admin! 👋</h1>
                <p class="text-sm text-[#64748B] mt-1">{{ now()->translatedFormat('l, d \d\e F \d\e Y') }}</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="px-3 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full text-xs font-medium flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                    Sistema Operativo
                </span>
            </div>
        </div>

        {{-- Banner Principal (Azul oscuro / Institucional) --}}
        <div class="bg-blue-900 rounded-3xl p-8 mb-8 text-white shadow-sm flex flex-col md:flex-row justify-between items-center gap-6">
            <div>
                <h2 class="text-xl font-semibold mb-2">Panel de Control General</h2>
                <p class="text-blue-200 text-sm max-w-xl">
                    Desde aquí puedes visualizar el rendimiento de los comercios, aprobar postulaciones de aliados, modificar las tarifas logísticas y administrar los accesos al sistema.
                </p>
            </div>
            <button class="bg-blue-600 hover:bg-blue-500 px-6 py-3 rounded-xl font-medium transition-colors whitespace-nowrap shadow-sm">
                Generar Reporte Global
            </button>
        </div>

        {{-- Tarjetas de Resumen (Métricas) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
            <div class="bg-white rounded-2xl border border-[#E2E8F0] p-6 shadow-sm">
                <div class="flex justify-between items-start">
                    <p class="text-xs font-bold text-[#64748B] uppercase tracking-wider">Comercios Asociados</p>
                    <div class="p-2 bg-blue-50 rounded-lg text-blue-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                </div>
                <p class="font-display text-3xl font-bold mt-4 text-[#0F172A]">{{ $comerciosCount ?? '124' }}</p>
            </div>

            <div class="bg-white rounded-2xl border border-[#E2E8F0] p-6 shadow-sm">
                <div class="flex justify-between items-start">
                    <p class="text-xs font-bold text-[#64748B] uppercase tracking-wider">Aliados Activos</p>
                    <div class="p-2 bg-blue-50 rounded-lg text-blue-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                </div>
                <p class="font-display text-3xl font-bold mt-4 text-[#0F172A]">{{ $aliadosActivosCount ?? '45' }}</p>
            </div>

            <div class="bg-white rounded-2xl border border-[#E2E8F0] p-6 shadow-sm">
                <div class="flex justify-between items-start">
                    <p class="text-xs font-bold text-[#64748B] uppercase tracking-wider">Nuevas Postulaciones</p>
                    <div class="p-2 bg-amber-50 rounded-lg text-amber-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                </div>
                <p class="font-display text-3xl font-bold mt-4 text-[#0F172A]">{{ $postulacionesCount ?? '12' }}</p>
            </div>

            <div class="bg-white rounded-2xl border border-[#E2E8F0] p-6 shadow-sm">
                <div class="flex justify-between items-start">
                    <p class="text-xs font-bold text-[#64748B] uppercase tracking-wider">Reportes Generados</p>
                    <div class="p-2 bg-blue-50 rounded-lg text-blue-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    </div>
                </div>
                <p class="font-display text-3xl font-bold mt-4 text-[#0F172A]">{{ $reportesCount ?? '830' }}</p>
            </div>
        </div>

        {{-- Grid de 2 columnas para Tablas Rápidas --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

            {{-- Últimos Aliados / Postulaciones --}}
            <div class="bg-white rounded-2xl border border-[#E2E8F0] p-6 shadow-sm">
                <div class="flex justify-between items-center mb-5">
                    <h2 class="font-display text-lg font-bold text-[#0F172A]">Últimos Aliados</h2>
                    <a href="#" class="text-sm font-medium text-blue-700 hover:text-blue-800">Ver todos &rarr;</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-[#94A3B8] text-xs uppercase tracking-wider border-b border-[#E2E8F0]">
                                <th class="pb-3 font-semibold">Aliado / Empresa</th>
                                <th class="pb-3 font-semibold text-center">Estado</th>
                                <th class="pb-3 font-semibold text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Ejemplo de fila (Reemplazar con tu @foreach) --}}
                            <tr class="border-b border-[#F1F5F9] last:border-0">
                                <td class="py-4">
                                    <p class="font-medium text-[#0F172A]">Logística Central C.A.</p>
                                    <p class="text-xs text-[#64748B]">Hace 2 horas</p>
                                </td>
                                <td class="py-4 text-center">
                                    <span class="inline-flex items-center gap-1.5 bg-amber-50 text-amber-600 px-2 py-1 rounded-md text-xs font-semibold">
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span> Postulado
                                    </span>
                                </td>
                                <td class="py-4 text-right">
                                    <button class="text-blue-700 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg font-medium transition">Revisar</button>
                                </td>
                            </tr>
                            <tr class="border-b border-[#F1F5F9] last:border-0">
                                <td class="py-4">
                                    <p class="font-medium text-[#0F172A]">Transportes El Veloz</p>
                                    <p class="text-xs text-[#64748B]">Ayer</p>
                                </td>
                                <td class="py-4 text-center">
                                    <span class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-700 px-2 py-1 rounded-md text-xs font-semibold">
                                        <span class="h-1.5 w-1.5 rounded-full bg-blue-600"></span> Activo
                                    </span>
                                </td>
                                <td class="py-4 text-right">
                                    <button class="text-[#64748B] hover:text-[#0F172A] font-medium transition">Ver perfil</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Accesos Rápidos a Configuraciones Clave --}}
            <div class="flex flex-col gap-5">

                {{-- Módulo: Precios de Envío --}}
                <div class="bg-white rounded-2xl border border-[#E2E8F0] p-6 shadow-sm flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-blue-50 text-blue-700 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-display font-bold text-[#0F172A]">Precios de Envío</h3>
                            <p class="text-sm text-[#64748B]">Configura tarifas por volumen y peso.</p>
                        </div>
                    </div>
                    <button class="bg-[#0F172A] hover:bg-slate-800 text-white px-4 py-2 rounded-xl text-sm font-medium transition-colors">
                        Modificar
                    </button>
                </div>

                {{-- Módulo: Generar Usuarios --}}
                <div class="bg-white rounded-2xl border border-[#E2E8F0] p-6 shadow-sm flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-blue-50 text-blue-700 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-display font-bold text-[#0F172A]">Generar Usuarios</h3>
                            <p class="text-sm text-[#64748B]">Añade administradores o personal.</p>
                        </div>
                    </div>
                    <button class="bg-[#0F172A] hover:bg-slate-800 text-white px-4 py-2 rounded-xl text-sm font-medium transition-colors">
                        Añadir
                    </button>
                </div>

            </div>
        </div>
    </main>
</div>
