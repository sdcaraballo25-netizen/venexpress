<div class="space-y-6">

    {{-- Encabezado --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="font-display text-2xl font-semibold text-[#0F172A]">
                Mis paquetes
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Guías asignadas actualmente a ti
            </p>
        </div>

        <a
            href="{{ route('repartidor.scanner') }}"
            class="inline-flex items-center justify-center rounded-xl bg-blue-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-blue-800"
        >
            Escanear guía
        </a>
    </div>

    {{-- Buscador --}}
    <div class="rounded-2xl border border-[#E2E8F0] bg-white p-4">
        <input
            type="search"
            wire:model.live.debounce.300ms="search"
            placeholder="Buscar guía, destinatario o ciudad..."
            class="w-full rounded-xl border border-[#E2E8F0] px-4 py-3 text-sm text-[#0F172A] outline-none transition focus:border-blue-900 focus:ring-2 focus:ring-blue-900/10"
        >
    </div>

    {{-- Filtros --}}
    <div class="flex flex-wrap gap-2">
        @php
            $filters = [
                'all' => 'Todos',
                'pending' => 'Pendientes',
                'in_progress' => 'En proceso',
                'delivered' => 'Entregados',
                'incidents' => 'Incidencias',
            ];
        @endphp

        @foreach ($filters as $key => $label)
            <button
                type="button"
                wire:click="setStatus('{{ $key }}')"
                class="rounded-xl px-4 py-2 text-sm font-medium transition
                    {{ $status === $key
                        ? 'bg-blue-900 text-white'
                        : 'border border-[#E2E8F0] bg-white text-slate-600 hover:bg-slate-50' }}"
            >
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Lista --}}
    <div class="overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white">

        @if ($packages->isEmpty())

            <div class="px-5 py-12 text-center">
                <p class="text-sm font-medium text-slate-500">
                    No hay paquetes para mostrar.
                </p>

                <p class="mt-1 text-sm text-slate-400">
                    Las guías asignadas a ti aparecerán aquí.
                </p>
            </div>

        @else

            <div class="divide-y divide-[#E2E8F0]">

                @foreach ($packages as $package)

                    <div class="flex flex-col gap-4 px-5 py-5 lg:flex-row lg:items-center lg:justify-between">

                        <div class="min-w-0">

                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-sm font-semibold text-[#0F172A]">
                                    {{ $package->tracking_number }}
                                </p>

                                <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                                    {{ $package->statusLabel() }}
                                </span>

                                @if ($package->incidents_count > 0)
                                    <span class="inline-flex rounded-full bg-red-50 px-3 py-1 text-xs font-medium text-red-700">
                                        {{ $package->incidents_count }}
                                        {{ $package->incidents_count === 1 ? 'incidencia' : 'incidencias' }}
                                    </span>
                                @endif
                            </div>

                            <p class="mt-2 text-sm text-slate-700">
                                {{ $package->recipient_name }}
                            </p>

                            <p class="mt-1 text-xs text-slate-400">
                                {{ $package->destination_city ?? 'Destino no disponible' }}
                                @if ($package->destination_state)
                                    · {{ $package->destination_state }}
                                @endif
                            </p>

                            @if ($package->requires_delivery && $package->delivery_address)
                                <p class="mt-2 text-xs text-slate-500">
                                    {{ $package->delivery_address }}
                                </p>
                            @endif

                        </div>

                        <div class="flex items-center justify-between gap-4 sm:justify-end">

                            <div class="text-right">
                                <p class="text-xs text-slate-400">
                                    Actualizado
                                </p>

                                <p class="mt-1 text-xs font-medium text-slate-600">
                                    {{ $package->updated_at?->format('d/m/Y H:i') }}
                                </p>
                            </div>

                            <a
                                href="#"
                                class="rounded-xl border border-[#E2E8F0] px-4 py-2 text-xs font-medium text-[#0F172A] transition hover:bg-slate-50"
                            >
                                Ver
                            </a>

                        </div>

                    </div>

                @endforeach

            </div>

            <div class="border-t border-[#E2E8F0] px-5 py-4">
                {{ $packages->links() }}
            </div>

        @endif

    </div>

</div>