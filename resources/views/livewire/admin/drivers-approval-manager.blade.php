@php
    use App\Models\Driver;
@endphp

<div class="min-h-screen">

    {{-- =========================================================
         ENCABEZADO
    ========================================================== --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">

        <div>
            <h1 class="font-display text-3xl font-bold text-[#0F172A]">
                Aprobación de Repartidores
            </h1>

            <p class="text-sm text-[#64748B] mt-1">
                Revisa y aprueba a los repartidores que se registran en Venexpress.
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
                placeholder="Buscar por nombre, correo o placa..."
                class="w-full rounded-xl border border-[#E2E8F0]
                       px-4 py-3 text-sm
                       text-[#0F172A]
                       placeholder:text-[#94A3B8]
                       focus:border-black
                       focus:ring-black"
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
                            Repartidor
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold text-[#64748B] uppercase">
                            Vehículo
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold text-[#64748B] uppercase">
                            Tipo
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold text-[#64748B] uppercase">
                            Documentos
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

                    @forelse($drivers as $driver)

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

                            $style = $statusStyles[$driver->status] ?? [
                                'bg' => 'bg-slate-50',
                                'text' => 'text-slate-600',
                                'dot' => 'bg-slate-400',
                            ];

                        @endphp


                        <tr class="border-b border-[#F1F5F9] last:border-0 hover:bg-slate-50 transition">


                            {{-- =================================================
                                 REPARTIDOR
                            ================================================== --}}
                            <td class="px-6 py-4">

                                <div>

                                    <p class="font-semibold text-[#0F172A]">
                                        {{ $driver->user?->name }}
                                    </p>

                                    <p class="text-xs text-[#64748B] mt-1">
                                        {{ $driver->user?->email }}
                                    </p>

                                </div>

                            </td>


                            {{-- =================================================
                                 VEHÍCULO
                            ================================================== --}}
                            <td class="px-6 py-4 text-[#475569]">

                                {{ $driver->vehicle_plate }} · {{ $driver->vehicle_type }}

                            </td>


                            {{-- =================================================
                                 TIPO (delivery / hub)
                            ================================================== --}}
                            <td class="px-6 py-4 text-[#475569]">

                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold
                                    {{ $driver->driver_type === Driver::TYPE_HUB ? 'bg-purple-50 text-purple-700' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $driver->driver_type === Driver::TYPE_HUB ? 'Hub' : 'Delivery' }}
                                </span>

                            </td>


                            {{-- =================================================
                                 DOCUMENTOS
                            ================================================== --}}
                            <td class="px-6 py-4">

                                <div class="flex items-center gap-2">

                                    @if ($driver->license_photo_path)
                                        <a href="{{ route('drivers.documents.license', $driver) }}" target="_blank" rel="noopener noreferrer"
                                            class="px-2.5 py-1.5 rounded-lg bg-slate-50 text-slate-600 hover:bg-slate-100 text-xs font-semibold transition"
                                            title="Ver licencia">
                                            Licencia
                                        </a>
                                    @endif

                                    @if ($driver->id_photo_path)
                                        <a href="{{ route('drivers.documents.id', $driver) }}" target="_blank" rel="noopener noreferrer"
                                            class="px-2.5 py-1.5 rounded-lg bg-slate-50 text-slate-600 hover:bg-slate-100 text-xs font-semibold transition"
                                            title="Ver cédula">
                                            Cédula
                                        </a>
                                    @endif

                                    @if ($driver->vehicle_registration_photo_path)
                                        <a href="{{ route('drivers.documents.vehicle-registration', $driver) }}" target="_blank" rel="noopener noreferrer"
                                            class="px-2.5 py-1.5 rounded-lg bg-slate-50 text-slate-600 hover:bg-slate-100 text-xs font-semibold transition"
                                            title="Ver carnet de circulación">
                                            Carnet
                                        </a>
                                    @endif

                                    @if (! $driver->license_photo_path && ! $driver->id_photo_path && ! $driver->vehicle_registration_photo_path)
                                        <span class="text-xs text-[#94A3B8]">Sin documentos</span>
                                    @endif

                                </div>

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

                                    {{ str_replace('_', ' ', $driver->status) }}

                                </span>

                            </td>


                            {{-- =================================================
                                 ACCIONES
                            ================================================== --}}
                            <td class="px-6 py-4">

                                <div class="flex justify-end items-center gap-2">

                                    {{-- =========================================
                                         PENDIENTE
                                    ========================================== --}}
                                    @if($driver->status === Driver::STATUS_PENDING)

                                        {{-- Aprobar --}}
                                        <button
                                            wire:click="approve({{ $driver->id }})"
                                            wire:confirm="¿Estás seguro de que deseas aprobar a este repartidor?"
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
                                            wire:click="reject({{ $driver->id }})"
                                            wire:confirm="¿Estás seguro de que deseas rechazar a este repartidor?"
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
                                    @elseif($driver->status === Driver::STATUS_ACTIVE)

                                        <button
                                            wire:click="suspend({{ $driver->id }})"
                                            wire:confirm="¿Estás seguro de que deseas suspender a este repartidor?"
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
                                    @elseif($driver->status === Driver::STATUS_SUSPENDED)

                                        <button
                                            wire:click="activate({{ $driver->id }})"
                                            wire:confirm="¿Deseas activar nuevamente a este repartidor?"
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
                                    @elseif($driver->status === Driver::STATUS_REJECTED)

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

                            <td colspan="6" class="px-6 py-12 text-center">

                                <div class="flex flex-col items-center">

                                    <div
                                        class="w-12 h-12 rounded-full
                                               bg-slate-100
                                               flex items-center justify-center
                                               text-slate-400 mb-3"
                                    >
                                        🚚
                                    </div>

                                    <p class="font-semibold text-[#0F172A]">
                                        No hay repartidores registrados
                                    </p>

                                    <p class="text-sm text-[#64748B] mt-1">
                                        Los repartidores aparecerán aquí cuando se registren.
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
        @if($drivers->hasPages())

            <div class="px-6 py-4 border-t border-[#E2E8F0]">

                {{ $drivers->links() }}

            </div>

        @endif

    </div>

</div>
