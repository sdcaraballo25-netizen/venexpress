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

</div>