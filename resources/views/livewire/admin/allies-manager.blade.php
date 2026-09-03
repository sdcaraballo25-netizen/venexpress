@php
    use App\Models\Ally;
@endphp

<div class="min-h-screen">

    {{-- =========================================================
         ENCABEZADO
    ========================================================== --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">

        <div>
            <h1 class="font-display text-3xl font-bold text-[#0F172A]">
                Gestión de Aliados
            </h1>

            <p class="text-sm text-[#64748B] mt-1">
                Administra las taquillas aliadas de Venexpress.
            </p>
        </div>

    </div>


    {{-- =========================================================
         MENSAJE DE ÉXITO
    ========================================================== --}}
    @if (session()->has('success'))

        <div
            class="mb-6 flex items-center gap-3 rounded-xl border border-emerald-200
                   bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
        >

            <div class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100">
                ✓
            </div>

            <span>
                {{ session('success') }}
            </span>

        </div>

    @endif


    {{-- =========================================================
         BUSCADOR
    ========================================================== --}}
    <div class="bg-white border border-[#E2E8F0] rounded-2xl p-5 shadow-sm mb-6">

        <div class="relative max-w-md">

            <input
                type="text"
                wire:model.live="search"
                placeholder="Buscar por empresa, RIF o ciudad..."
                class="w-full rounded-xl border border-[#E2E8F0]
                       px-4 py-3 text-sm
                       text-[#0F172A]
                       placeholder:text-[#94A3B8]
                       focus:border-blue-500
                       focus:ring-blue-500"
            >

        </div>

    </div>


    {{-- =========================================================
         TABLA
    ========================================================== --}}
    <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-sm overflow-hidden">

        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                {{-- CABECERA --}}
                <thead>

                    <tr class="bg-slate-50 border-b border-[#E2E8F0]">

                        <th class="px-6 py-4 text-left text-xs font-semibold text-[#64748B] uppercase">
                            Aliado / Empresa
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold text-[#64748B] uppercase">
                            RIF
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold text-[#64748B] uppercase">
                            Ciudad
                        </th>

                        <th class="px-6 py-4 text-center text-xs font-semibold text-[#64748B] uppercase">
                            Estado
                        </th>

                        <th class="px-6 py-4 text-right text-xs font-semibold text-[#64748B] uppercase">
                            Acción
                        </th>

                    </tr>

                </thead>


                {{-- CUERPO --}}
                <tbody>

                    @forelse($allies as $ally)

                        @php

                            $statusStyles = [

                                'PENDIENTE' => [
                                    'bg' => 'bg-amber-50',
                                    'text' => 'text-amber-700',
                                    'dot' => 'bg-amber-500',
                                ],

                                'ACTIVO' => [
                                    'bg' => 'bg-blue-50',
                                    'text' => 'text-blue-700',
                                    'dot' => 'bg-blue-600',
                                ],

                                'RECHAZADO' => [
                                    'bg' => 'bg-red-50',
                                    'text' => 'text-red-700',
                                    'dot' => 'bg-red-500',
                                ],

                                'SUSPENDIDO' => [
                                    'bg' => 'bg-slate-100',
                                    'text' => 'text-slate-700',
                                    'dot' => 'bg-slate-500',
                                ],

                            ];

                            $style = $statusStyles[$ally->status] ?? [
                                'bg' => 'bg-slate-50',
                                'text' => 'text-slate-600',
                                'dot' => 'bg-slate-400',
                            ];

                        @endphp


                        <tr class="border-b border-[#F1F5F9] last:border-0 hover:bg-slate-50 transition">


                            {{-- =================================================
                                 EMPRESA
                            ================================================== --}}
                            <td class="px-6 py-4">

                                <div>

                                    <p class="font-semibold text-[#0F172A]">
                                        {{ $ally->business_name }}
                                    </p>

                                    <p class="text-xs text-[#64748B] mt-1">
                                        {{ $ally->user?->email }}
                                    </p>

                                </div>

                            </td>


                            {{-- =================================================
                                 RIF
                            ================================================== --}}
                            <td class="px-6 py-4 text-[#475569]">

                                {{ $ally->rif }}

                            </td>


                            {{-- =================================================
                                 CIUDAD
                            ================================================== --}}
                            <td class="px-6 py-4 text-[#475569]">

                                {{ $ally->city }}

                            </td>


                            {{-- =================================================
                                 ESTADO
                            ================================================== --}}
                            <td class="px-6 py-4 text-center">

                                <span
                                    class="inline-flex items-center gap-2
                                           px-3 py-1.5 rounded-lg
                                           text-xs font-semibold
                                           {{ $style['bg'] }}
                                           {{ $style['text'] }}"
                                >

                                    <span
                                        class="w-1.5 h-1.5 rounded-full {{ $style['dot'] }}"
                                    ></span>

                                    {{ str_replace('_', ' ', $ally->status) }}

                                </span>

                            </td>


                            {{-- =================================================
                                 ACCIONES
                            ================================================== --}}
                            <td class="px-6 py-4">

                                <div class="flex justify-end items-center gap-2">

                                    {{-- =========================================
                                         UBICACIÓN (para el localizador público)
                                    ========================================== --}}
                                    @if($ally->status !== Ally::STATUS_REJECTED)
                                        <button
                                            wire:click="editLocation({{ $ally->id }})"
                                            class="px-3 py-2 rounded-lg
                                                   bg-slate-50 text-slate-600
                                                   hover:bg-slate-100
                                                   text-xs font-semibold
                                                   transition inline-flex items-center gap-1.5"
                                        >
                                            <i class="fa-solid fa-location-dot"></i>
                                            {{ $ally->latitude ? 'Editar ubicación' : 'Fijar ubicación' }}
                                        </button>
                                    @endif


                                    {{-- =========================================
                                         PENDIENTE
                                    ========================================== --}}
                                    @if($ally->status === Ally::STATUS_PENDING)

                                        {{-- Aprobar --}}
                                        <button
                                            wire:click="approve({{ $ally->id }})"
                                            wire:confirm="¿Estás seguro de que deseas aprobar este aliado?"
                                            class="px-3 py-2 rounded-lg
                                                   bg-blue-600 text-white
                                                   hover:bg-blue-700
                                                   text-xs font-semibold
                                                   transition"
                                        >
                                            Aprobar
                                        </button>


                                        {{-- Rechazar --}}
                                        <button
                                            wire:click="reject({{ $ally->id }})"
                                            wire:confirm="¿Estás seguro de que deseas rechazar este aliado?"
                                            class="px-3 py-2 rounded-lg
                                                   bg-red-50 text-red-700
                                                   hover:bg-red-100
                                                   text-xs font-semibold
                                                   transition"
                                        >
                                            Rechazar
                                        </button>


                                    {{-- =========================================
                                         ACTIVO
                                    ========================================== --}}
                                    @elseif($ally->status === Ally::STATUS_ACTIVE)

                                        <button
                                            wire:click="suspend({{ $ally->id }})"
                                            wire:confirm="¿Estás seguro de que deseas suspender este aliado?"
                                            class="px-3 py-2 rounded-lg
                                                   bg-amber-50 text-amber-700
                                                   hover:bg-amber-100
                                                   text-xs font-semibold
                                                   transition"
                                        >
                                            Suspender
                                        </button>


                                    {{-- =========================================
                                         SUSPENDIDO
                                    ========================================== --}}
                                    @elseif($ally->status === Ally::STATUS_SUSPENDED)

                                        <button
                                            wire:click="activate({{ $ally->id }})"
                                            wire:confirm="¿Deseas activar nuevamente este aliado?"
                                            class="px-3 py-2 rounded-lg
                                                   bg-blue-50 text-blue-700
                                                   hover:bg-blue-100
                                                   text-xs font-semibold
                                                   transition"
                                        >
                                            Activar
                                        </button>


                                    {{-- =========================================
                                         RECHAZADO
                                    ========================================== --}}
                                    @elseif($ally->status === Ally::STATUS_REJECTED)

                                        <span class="text-xs text-[#94A3B8]">
                                            Sin acciones
                                        </span>

                                    @endif

                                </div>

                            </td>

                        </tr>


                    @empty

                        {{-- SIN RESULTADOS --}}
                        <tr>

                            <td colspan="5" class="px-6 py-12 text-center">

                                <div class="flex flex-col items-center">

                                    <div
                                        class="w-12 h-12 rounded-full
                                               bg-slate-100
                                               flex items-center justify-center
                                               text-slate-400 mb-3"
                                    >
                                        👥
                                    </div>

                                    <p class="font-semibold text-[#0F172A]">
                                        No hay aliados registrados
                                    </p>

                                    <p class="text-sm text-[#64748B] mt-1">
                                        Los aliados aparecerán aquí cuando sean registrados.
                                    </p>

                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- =========================================================
             PAGINACIÓN
        ========================================================== --}}
        @if($allies->hasPages())

            <div class="px-6 py-4 border-t border-[#E2E8F0]">

                {{ $allies->links() }}

            </div>

        @endif

    </div>


    {{-- =========================================================
         MODAL: UBICACIÓN DE LA AGENCIA (para el localizador público)
    ========================================================== --}}
    @once
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
    @endonce

    @if($showLocationModal)

        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4"
            wire:key="location-modal-{{ $editingAllyId }}"
        >

            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6">

                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-display text-lg font-bold text-[#0F172A]">
                        Ubicación de la agencia
                    </h3>
                    <button wire:click="$set('showLocationModal', false)" class="text-slate-400 hover:text-slate-600">
                        ✕
                    </button>
                </div>

                <p class="text-xs text-[#64748B] mb-3">
                    Haz clic en el mapa sobre la ubicación exacta de la agencia. Estas coordenadas
                    son las que se muestran a los clientes en el localizador público de oficinas.
                </p>

                <div
                    x-data="allyLocationMap({
                        lat: @js($location_latitude ?? 10.4806),
                        lng: @js($location_longitude ?? -66.9036),
                        hasPoint: @js((bool) $location_latitude),
                    })"
                    x-init="init($el)"
                    wire:ignore
                    class="rounded-xl overflow-hidden border border-[#E2E8F0] mb-4"
                    style="height: 280px;"
                ></div>

                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div>
                        <label class="block text-xs font-medium text-[#64748B] mb-1">Estado</label>
                        <select wire:model="location_state" class="w-full rounded-lg border-[#E2E8F0] text-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Selecciona...</option>
                            @foreach (array_keys(config('venezuela.states', [])) as $state)
                                <option value="{{ $state }}">{{ $state }}</option>
                            @endforeach
                        </select>
                        @error('location_state') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-medium text-[#64748B] mb-1">Latitud</label>
                            <input type="text" wire:model="location_latitude" readonly
                                class="w-full rounded-lg border-[#E2E8F0] text-sm bg-slate-50 text-slate-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-[#64748B] mb-1">Longitud</label>
                            <input type="text" wire:model="location_longitude" readonly
                                class="w-full rounded-lg border-[#E2E8F0] text-sm bg-slate-50 text-slate-500">
                        </div>
                    </div>
                </div>
                @error('location_latitude') <p class="text-xs text-red-600 -mt-2 mb-3">{{ $message }}</p> @enderror

                <div class="flex justify-end gap-2">
                    <button wire:click="$set('showLocationModal', false)"
                        class="px-4 py-2 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-50">
                        Cancelar
                    </button>
                    <button wire:click="saveLocation"
                        class="px-4 py-2 rounded-lg text-sm font-semibold bg-blue-600 text-white hover:bg-blue-700">
                        Guardar ubicación
                    </button>
                </div>

            </div>

        </div>

        <script>
            function allyLocationMap({ lat, lng, hasPoint }) {
                return {
                    map: null,
                    marker: null,
                    init(el) {
                        this.map = L.map(el).setView([lat, lng], hasPoint ? 14 : 6);

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; OpenStreetMap contributors',
                        }).addTo(this.map);

                        if (hasPoint) {
                            this.marker = L.marker([lat, lng]).addTo(this.map);
                        }

                        this.map.on('click', (e) => {
                            const { lat, lng } = e.latlng;

                            if (this.marker) {
                                this.marker.setLatLng([lat, lng]);
                            } else {
                                this.marker = L.marker([lat, lng]).addTo(this.map);
                            }

                            @this.call('setLocationFromMap', lat, lng);
                        });

                        setTimeout(() => this.map.invalidateSize(), 150);
                    },
                }
            }
        </script>

    @endif

</div>