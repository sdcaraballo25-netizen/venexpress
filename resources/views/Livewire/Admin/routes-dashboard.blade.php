<div class="min-h-screen bg-white flex font-sans">

    <main class="flex-1 p-8 lg:p-10 overflow-y-auto">

        {{-- =========================================================
             HEADER
        ========================================================== --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h1 class="font-display text-3xl font-bold tracking-tight text-[#0F172A]">
                    Dashboard de Rutas
                </h1>
                <p class="text-sm text-[#64748B] mt-1">
                    Estado en vivo de las recolecciones de hoy.
                </p>
            </div>

            <a href="{{ route('admin.routes') }}"
               class="bg-[#0F172A] hover:bg-slate-800 text-white px-4 py-2 rounded-xl text-sm font-medium transition-colors">
                Ir a Gestión de Rutas
            </a>
        </div>

        {{-- =========================================================
             MÉTRICAS
        ========================================================== --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">

            @php
                $cards = [
                    ['label' => 'Rutas activas', 'value' => $metrics['active_routes'], 'icon' => '🚚', 'bg' => 'bg-blue-50', 'text' => 'text-blue-700'],
                    ['label' => 'Completadas hoy', 'value' => $metrics['completed_routes_today'], 'icon' => '✅', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-700'],
                    ['label' => 'Rutas pendientes', 'value' => $metrics['pending_routes'], 'icon' => '🕓', 'bg' => 'bg-amber-50', 'text' => 'text-amber-700'],
                    ['label' => 'Repartidores en ruta', 'value' => $metrics['drivers_on_route'], 'icon' => '🧑‍✈️', 'bg' => 'bg-slate-50', 'text' => 'text-slate-700'],
                    ['label' => 'Recolecciones hoy', 'value' => $metrics['collections_completed_today'], 'icon' => '📦', 'bg' => 'bg-blue-50', 'text' => 'text-blue-700'],
                    ['label' => 'Recolecciones pendientes', 'value' => $metrics['collections_pending'], 'icon' => '📭', 'bg' => 'bg-amber-50', 'text' => 'text-amber-700'],
                    ['label' => 'Agencias visitadas (activas)', 'value' => $metrics['allies_visited'], 'icon' => '🏢', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-700'],
                    ['label' => 'Agencias pendientes (activas)', 'value' => $metrics['allies_pending'], 'icon' => '📍', 'bg' => 'bg-red-50', 'text' => 'text-red-700'],
                ];
            @endphp

            @foreach ($cards as $card)
                <div class="bg-white rounded-2xl border border-[#E2E8F0] p-6 shadow-sm">
                    <div class="flex justify-between items-start">
                        <p class="text-xs font-bold text-[#64748B] uppercase tracking-wider">
                            {{ $card['label'] }}
                        </p>
                        <div class="p-2 {{ $card['bg'] }} rounded-lg {{ $card['text'] }}">
                            {{ $card['icon'] }}
                        </div>
                    </div>

                    <p class="font-display text-3xl font-bold mt-4 text-[#0F172A]">
                        {{ number_format($card['value']) }}
                    </p>
                </div>
            @endforeach
        </div>

        {{-- =========================================================
             INDICADORES ADICIONALES
        ========================================================== --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-8">

            <div class="bg-white rounded-2xl border border-[#E2E8F0] p-6 shadow-sm">
                <p class="text-xs font-bold text-[#64748B] uppercase tracking-wider">
                    % de cumplimiento (últimas 10 rutas completadas)
                </p>
                <p class="font-display text-3xl font-bold mt-4 text-[#0F172A]">
                    {{ $completionRate !== null ? $completionRate . '%' : '—' }}
                </p>
                @if ($completionRate === null)
                    <p class="text-xs text-[#64748B] mt-2">Todavía no hay rutas completadas.</p>
                @endif
            </div>

            <div class="bg-white rounded-2xl border border-[#E2E8F0] p-6 shadow-sm">
                <p class="text-xs font-bold text-[#64748B] uppercase tracking-wider mb-3">
                    Rutas posiblemente estancadas
                </p>

                @if ($stalledRoutes->isEmpty())
                    <p class="text-sm text-[#64748B]">Ninguna ruta activa lleva más de 30 min sin actividad.</p>
                @else
                    <ul class="space-y-2">
                        @foreach ($stalledRoutes as $route)
                            <li class="flex items-center gap-2 text-sm text-amber-700">
                                <span class="w-1.5 h-1.5 bg-amber-500 rounded-full"></span>
                                {{ $route->name }} — {{ $route->city }}
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        {{-- =========================================================
             RUTAS EN CURSO
        ========================================================== --}}
        <div class="bg-white rounded-2xl border border-[#E2E8F0] p-6 shadow-sm">
            <p class="text-xs font-bold text-[#64748B] uppercase tracking-wider mb-4">
                Rutas en curso
            </p>

            @if ($activeRoutes->isEmpty())
                <p class="text-sm text-[#64748B]">No hay rutas en curso en este momento.</p>
            @else
                <div class="space-y-2">
                    @foreach ($activeRoutes as $route)
                        <div class="flex items-center justify-between rounded-xl border border-[#E2E8F0] px-4 py-3">
                            <div>
                                <p class="text-sm font-medium text-[#0F172A]">{{ $route->name }}</p>
                                <p class="text-xs text-[#64748B]">{{ $route->city }}</p>
                            </div>
                            <span class="text-xs font-medium text-[#64748B]">
                                {{ $route->visitedStopsCount() }}/{{ $route->stops->count() }} agencias
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </main>
</div>
