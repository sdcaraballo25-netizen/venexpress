<div class="space-y-6">

    {{-- Encabezado --}}
    <div>
        <a
            href="{{ route('repartidor.dashboard') }}"
            class="text-sm font-medium text-black hover:text-gray-700"
        >
            ← Resumen
        </a>

        <h1 class="mt-2 font-display text-2xl font-bold text-[#0F172A]">
            Historial de rutas
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Rutas que has tomado, en curso, finalizadas o canceladas.
        </p>
    </div>

    {{-- Listado --}}
    <div class="space-y-3">

        @forelse ($routes as $route)

            <div
                wire:key="route-history-{{ $route->id }}"
                class="rounded-2xl border border-[#E2E8F0] bg-white p-5"
            >

                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">

                    <div>
                        <p class="font-display text-base font-bold text-[#0F172A]">
                            Ruta #{{ $route->id }} · {{ $route->name }}
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            {{ $route->created_at->format('d/m/Y H:i') }}
                        </p>

                        <p class="mt-2 text-sm text-slate-600">

                            @if ($route->route_type === \App\Models\Route::TYPE_HUB_TRANSFER)
                                Traslado a hub
                            @elseif ($route->route_type === \App\Models\Route::TYPE_HUB_DISTRIBUTION)
                                Distribución a almacén
                            @elseif ($route->route_type === \App\Models\Route::TYPE_DELIVERY)
                                Entregas
                            @else
                                {{ $route->route_type }}
                            @endif

                        </p>

                        <p class="mt-1 text-xs text-slate-500">

                            @if ($route->originWarehouse)
                                <span class="font-semibold text-slate-600">Origen:</span>
                                {{ $route->originWarehouse->name }}
                            @endif

                            @if ($route->originWarehouse && $route->returnWarehouse)
                                &middot;
                            @endif

                            @if ($route->returnWarehouse)
                                <span class="font-semibold text-slate-600">Destino:</span>
                                {{ $route->returnWarehouse->name }}
                            @endif

                            @if (! $route->originWarehouse && ! $route->returnWarehouse)
                                <span class="font-semibold text-slate-600">Recorrido:</span>
                                {{ $route->stops->count() }}
                                {{ $route->stops->count() === 1 ? 'parada' : 'paradas' }}
                            @endif

                        </p>
                    </div>

                    <div class="flex flex-col items-start gap-2 sm:items-end">

                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold
                            @if ($route->status === \App\Models\Route::STATUS_IN_PROGRESS)
                                bg-blue-50 text-blue-700
                            @elseif ($route->status === \App\Models\Route::STATUS_ASSIGNED)
                                bg-violet-50 text-violet-700
                            @elseif ($route->status === \App\Models\Route::STATUS_COMPLETED)
                                bg-emerald-50 text-emerald-700
                            @elseif ($route->status === \App\Models\Route::STATUS_CANCELLED)
                                bg-red-50 text-red-700
                            @else
                                bg-amber-50 text-amber-700
                            @endif">

                            @if ($route->status === \App\Models\Route::STATUS_ASSIGNED)
                                Asignada
                            @elseif ($route->status === \App\Models\Route::STATUS_IN_PROGRESS)
                                En curso
                            @elseif ($route->status === \App\Models\Route::STATUS_COMPLETED)
                                Completada
                            @elseif ($route->status === \App\Models\Route::STATUS_CANCELLED)
                                Cancelada
                            @else
                                {{ $route->status }}
                            @endif

                        </span>

                        <a
                            href="{{ route('repartidor.route-detail', $route->id) }}"
                            wire:navigate
                            class="text-sm font-semibold text-black hover:text-gray-700"
                        >
                            Ver detalle →
                        </a>

                    </div>

                </div>

            </div>

        @empty

            <div class="rounded-2xl border border-[#E2E8F0] bg-white p-8 text-center">
                <p class="text-sm text-slate-500">
                    Todavía no has tomado ninguna ruta.
                </p>
            </div>

        @endforelse

    </div>

    @if ($routes->hasPages())
        <div>
            {{ $routes->links() }}
        </div>
    @endif

</div>
