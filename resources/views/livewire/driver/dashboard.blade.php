<div class="space-y-6">

    {{-- Encabezado --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="font-display text-2xl font-semibold text-[#0F172A]">
                Hola, {{ auth()->user()->name }}
            </h2>

            <p class="text-sm text-slate-500">
                Resumen de tus entregas
            </p>
        </div>

        <div class="inline-flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-2">
            <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>

            <span class="text-sm font-medium text-emerald-700">
                Disponible
            </span>
        </div>
    </div>


    {{-- Resumen --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

        {{-- Asignados --}}
        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5">
            <p class="text-sm text-slate-500">
                Paquetes asignados
            </p>

            <div class="mt-2 flex items-end justify-between">
                <p class="font-display text-3xl font-semibold text-[#0F172A]">
                    {{ $assignedCount ?? 0 }}
                </p>

                <span class="text-xs text-slate-400">
                    Total
                </span>
            </div>
        </div>

        {{-- Pendientes --}}
        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5">
            <p class="text-sm text-slate-500">
                Pendientes
            </p>

            <div class="mt-2 flex items-end justify-between">
                <p class="font-display text-3xl font-semibold text-amber-600">
                    {{ $pendingCount ?? 0 }}
                </p>

                <span class="text-xs text-slate-400">
                    Por entregar
                </span>
            </div>
        </div>

        {{-- Entregados --}}
        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5">
            <p class="text-sm text-slate-500">
                Entregados
            </p>

            <div class="mt-2 flex items-end justify-between">
                <p class="font-display text-3xl font-semibold text-emerald-600">
                    {{ $deliveredCount ?? 0 }}
                </p>

                <span class="text-xs text-slate-400">
                    Completados
                </span>
            </div>
        </div>

    </div>


    {{-- Contenido principal --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

        {{-- Mis entregas --}}
        <div class="rounded-2xl border border-[#E2E8F0] bg-white lg:col-span-2">

            <div class="flex items-center justify-between border-b border-[#E2E8F0] px-5 py-4">
                <div>
                    <h3 class="font-display text-lg font-semibold text-[#0F172A]">
                        Mis entregas
                    </h3>

                    <p class="mt-1 text-sm text-slate-500">
                        Paquetes asignados para tu jornada
                    </p>
                </div>

                <a
                    href="{{ route('repartidor.packages') }}"
                    class="text-sm font-medium text-blue-900 hover:underline"
                >
                    Ver todos
                </a>
            </div>


            <div class="divide-y divide-[#E2E8F0]">

                @if (($pendingPackages ?? collect())->isEmpty())

                    <div class="px-5 py-10 text-center">
                        <p class="text-sm font-medium text-slate-500">
                            No tienes paquetes pendientes.
                        </p>

                        <p class="mt-1 text-sm text-slate-400">
                            Los paquetes asignados aparecerán aquí.
                        </p>
                    </div>

                @else

                    @foreach ($pendingPackages as $package)

                        <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">

                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-[#0F172A]">
                                    {{ $package->tracking_number }}
                                </p>

                                <p class="mt-1 text-sm text-slate-600">
                                    {{ $package->recipient_name }}
                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    {{ $package->destination_city ?? 'Destino no disponible' }}
                                </p>
                            </div>

                            <div class="flex items-center gap-3">

                                <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-700">
                                    {{ $package->statusLabel() }}
                                </span>

                                <a
                                    href="{{ route('repartidor.package-detail', $package->id) }}"
                                    class="rounded-xl border border-[#E2E8F0] px-3 py-2 text-xs font-medium text-[#0F172A] transition hover:bg-slate-50"
                                >
                                    Ver
                                </a>

                            </div>

                        </div>

                    @endforeach

                @endif

            </div>

        </div>


        {{-- Panel lateral --}}
        <div class="space-y-4">

            {{-- Próxima entrega --}}
            <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5">

                <h3 class="font-display text-lg font-semibold text-[#0F172A]">
                    Próxima entrega
                </h3>

                @if (($pendingPackages ?? collect())->isNotEmpty())

                    @php
                        $nextPackage = $pendingPackages[0];
                    @endphp

                    <div class="mt-4 rounded-2xl bg-slate-50 p-4">

                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                            {{ $nextPackage->tracking_number }}
                        </p>

                        <p class="mt-2 text-sm font-semibold text-[#0F172A]">
                            {{ $nextPackage->recipient_name }}
                        </p>

                        <p class="mt-1 text-sm text-slate-500">
                            {{ $nextPackage->destination_city ?? 'Destino no disponible' }}
                        </p>

                        <a
                            href="{{ route('repartidor.package-detail', $nextPackage->id) }}"
                            wire:navigate
                            class="mt-4 block w-full rounded-xl bg-blue-900 px-4 py-2.5 text-center text-sm font-medium text-white transition hover:bg-blue-800"
                        >
                            Iniciar entrega
                        </a>

                    </div>

                @else

                    <div class="mt-4 rounded-2xl bg-slate-50 p-4">
                        <p class="text-sm text-slate-500">
                            No hay una entrega pendiente.
                        </p>
                    </div>

                @endif

            </div>


            {{-- Acciones --}}
            <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5">

                <h3 class="font-display text-lg font-semibold text-[#0F172A]">
                    Acciones rápidas
                </h3>

                <div class="mt-4 grid grid-cols-2 gap-3">

                    <a
                        href="{{ route('repartidor.scanner') }}"
                        class="rounded-xl border border-[#E2E8F0] p-4 transition hover:bg-slate-50"
                    >
                        <p class="text-sm font-semibold text-[#0F172A]">
                            Escanear
                        </p>

                        <p class="mt-1 text-xs text-slate-400">
                            Leer guía
                        </p>
                    </a>

                    <a
                        href="{{ route('repartidor.packages') }}"
                        class="rounded-xl border border-[#E2E8F0] p-4 transition hover:bg-slate-50"
                    >
                        <p class="text-sm font-semibold text-[#0F172A]">
                            Historial
                        </p>

                        <p class="mt-1 text-xs text-slate-400">
                            Ver entregas
                        </p>
                    </a>

                </div>

            </div>

        </div>

    </div>


    {{-- Entregas recientes --}}
    <div class="rounded-2xl border border-[#E2E8F0] bg-white">

        <div class="flex items-center justify-between border-b border-[#E2E8F0] px-5 py-4">

            <div>
                <h3 class="font-display text-lg font-semibold text-[#0F172A]">
                    Entregas recientes
                </h3>

                <p class="mt-1 text-sm text-slate-500">
                    Últimas entregas completadas
                </p>
            </div>

            <a
                href="{{ route('repartidor.packages') }}"
                class="text-sm font-medium text-blue-900 hover:underline"
            >
                Ver historial
            </a>

        </div>


        @if (($recentDeliveries ?? collect())->isEmpty())

            <div class="px-5 py-8 text-center">

                <p class="text-sm text-slate-500">
                    Aún no tienes entregas registradas.
                </p>

            </div>

        @else

            <div class="divide-y divide-[#E2E8F0]">

                @foreach ($recentDeliveries as $delivery)

                    <div class="flex items-center justify-between px-5 py-4">

                        <div>
                            <p class="text-sm font-semibold text-[#0F172A]">
                                {{ $delivery->tracking_number }}
                            </p>

                            <p class="mt-1 text-xs text-slate-500">
                                {{ $delivery->recipient_name }}
                            </p>
                        </div>

                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">
                            Entregado
                        </span>

                    </div>

                @endforeach

            </div>

        @endif

    </div>

</div>
