@php
    use App\Models\Emprendedor;
@endphp

<div class="min-h-screen">

    {{-- =========================================================
         ENCABEZADO
    ========================================================== --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">

        <div>
            <h1 class="font-display text-3xl font-bold text-[#111111]">
                Aprobación de Emprendedores
            </h1>

            <p class="text-sm text-[#6B6B66] mt-1">
                Revisa y aprueba a los emprendedores que se registran en el marketplace.
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
    <div class="bg-white border border-[#E5E5E0] rounded-2xl p-5 shadow-sm mb-6">

        <div class="relative max-w-md">

            <input
                type="text"
                wire:model.live="search"
                placeholder="Buscar por negocio, nombre o correo..."
                class="w-full rounded-xl border border-[#E5E5E0]
                       px-4 py-3 text-sm
                       text-[#111111]
                       placeholder:text-[#B8B8B2]
                       focus:border-blue-500
                       focus:ring-blue-500"
            >

        </div>

    </div>


    {{-- =========================================================
         TABLA
    ========================================================== --}}
    <div class="bg-white border border-[#E5E5E0] rounded-2xl shadow-sm overflow-hidden">

        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                <thead>

                    <tr class="bg-slate-50 border-b border-[#E5E5E0]">

                        <th class="px-6 py-4 text-left text-xs font-semibold text-[#6B6B66] uppercase">
                            Emprendedor
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold text-[#6B6B66] uppercase">
                            Documento
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold text-[#6B6B66] uppercase">
                            Agencia de retiro
                        </th>

                        <th class="px-6 py-4 text-center text-xs font-semibold text-[#6B6B66] uppercase">
                            Estado
                        </th>

                        <th class="px-6 py-4 text-right text-xs font-semibold text-[#6B6B66] uppercase">
                            Acción
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($emprendedores as $emprendedor)

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

                            $style = $statusStyles[$emprendedor->status] ?? [
                                'bg' => 'bg-slate-50',
                                'text' => 'text-slate-600',
                                'dot' => 'bg-slate-400',
                            ];

                        @endphp


                        <tr class="border-b border-[#F0F0EC] last:border-0 hover:bg-slate-50 transition">

                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-semibold text-[#111111]">
                                        {{ $emprendedor->business_name }}
                                    </p>
                                    <p class="text-xs text-[#6B6B66] mt-1">
                                        {{ $emprendedor->user?->name }} · {{ $emprendedor->user?->email }}
                                    </p>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-[#4A4A45]">
                                {{ $emprendedor->document_id }}
                            </td>

                            <td class="px-6 py-4 text-[#4A4A45]">
                                {{ $emprendedor->pickupAlly?->business_name ?? 'Sin asignar' }}
                            </td>

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

                                    {{ str_replace('_', ' ', $emprendedor->status) }}

                                </span>

                            </td>

                            <td class="px-6 py-4">

                                <div class="flex justify-end items-center gap-2">

                                    @if($emprendedor->status === Emprendedor::STATUS_PENDING)

                                        <button
                                            @click.prevent="$store.confirm.open({
                                                message: '¿Estás seguro de que deseas aprobar a este emprendedor?',
                                                confirmText: 'Aprobar',
                                                variant: 'primary',
                                                onConfirm: () => $wire.approve({{ $emprendedor->id }}),
                                            })"
                                            class="px-3 py-2 rounded-lg
                                                   bg-blue-600 text-white
                                                   hover:bg-blue-700
                                                   text-xs font-semibold
                                                   transition"
                                        >
                                            Aprobar
                                        </button>

                                        <button
                                            @click.prevent="$store.confirm.open({
                                                message: '¿Estás seguro de que deseas rechazar a este emprendedor?',
                                                confirmText: 'Rechazar',
                                                variant: 'danger',
                                                onConfirm: () => $wire.reject({{ $emprendedor->id }}),
                                            })"
                                            class="px-3 py-2 rounded-lg
                                                   bg-red-50 text-red-700
                                                   hover:bg-red-100
                                                   text-xs font-semibold
                                                   transition"
                                        >
                                            Rechazar
                                        </button>

                                    @elseif($emprendedor->status === Emprendedor::STATUS_ACTIVE)

                                        <button
                                            @click.prevent="$store.confirm.open({
                                                message: '¿Estás seguro de que deseas suspender a este emprendedor?',
                                                confirmText: 'Suspender',
                                                variant: 'warning',
                                                onConfirm: () => $wire.suspend({{ $emprendedor->id }}),
                                            })"
                                            class="px-3 py-2 rounded-lg
                                                   bg-amber-50 text-amber-700
                                                   hover:bg-amber-100
                                                   text-xs font-semibold
                                                   transition"
                                        >
                                            Suspender
                                        </button>

                                    @elseif($emprendedor->status === Emprendedor::STATUS_SUSPENDED)

                                        <button
                                            @click.prevent="$store.confirm.open({
                                                message: '¿Deseas activar nuevamente a este emprendedor?',
                                                confirmText: 'Activar',
                                                variant: 'primary',
                                                onConfirm: () => $wire.activate({{ $emprendedor->id }}),
                                            })"
                                            class="px-3 py-2 rounded-lg
                                                   bg-blue-50 text-blue-700
                                                   hover:bg-blue-100
                                                   text-xs font-semibold
                                                   transition"
                                        >
                                            Activar
                                        </button>

                                    @elseif($emprendedor->status === Emprendedor::STATUS_REJECTED)

                                        <span class="text-xs text-[#B8B8B2]">
                                            Sin acciones
                                        </span>

                                    @endif

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                                        🏪
                                    </div>
                                    <p class="font-semibold text-[#111111]">
                                        No hay emprendedores registrados
                                    </p>
                                    <p class="text-sm text-[#6B6B66] mt-1">
                                        Aparecerán aquí cuando se registren en el marketplace.
                                    </p>
                                </div>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        @if($emprendedores->hasPages())
            <div class="px-6 py-4 border-t border-[#E5E5E0]">
                {{ $emprendedores->links() }}
            </div>
        @endif

    </div>

</div>
