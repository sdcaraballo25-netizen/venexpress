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
         MENSAJE DE ERROR
    ========================================================== --}}
    @if (session()->has('error'))

        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
            {{ session('error') }}
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
                                <button
                                    type="button"
                                    wire:click="viewDetails({{ $emprendedor->id }})"
                                    class="text-left"
                                    title="Ver detalles del emprendedor"
                                >
                                    <p class="font-semibold text-[#111111] hover:underline">
                                        {{ $emprendedor->business_name }}
                                    </p>
                                    <p class="text-xs text-[#6B6B66] mt-1">
                                        {{ $emprendedor->user?->name }} · {{ $emprendedor->user?->email }}
                                    </p>
                                </button>
                            </td>

                            <td class="px-6 py-4 text-[#4A4A45]">
                                {{ $emprendedor->document_id }}
                            </td>

                            <td class="px-6 py-4 text-[#4A4A45]">
                                {{ $emprendedor->pickupAlly?->business_name ?? 'Sin asignar' }}
                            </td>

                            <td class="px-6 py-4 text-center">

                                <div class="flex flex-col items-center gap-1">

                                    <span
                                        class="inline-flex items-center gap-2
                                               px-3 py-1.5 rounded-lg
                                               text-xs font-semibold
                                               {{ $style['bg'] }}
                                               {{ $style['text'] }}"
                                        title="Estado operativo"
                                    >

                                        <span
                                            class="w-1.5 h-1.5 rounded-full {{ $style['dot'] }}"
                                        ></span>

                                        {{ str_replace('_', ' ', $emprendedor->status) }}

                                    </span>

                                    <x-verification-status-badge :status="$emprendedor->verification_status" small title="Verificación de identidad" />

                                    @if($emprendedor->verification_status === Emprendedor::VERIFICATION_REJECTED && $emprendedor->verification_rejection_reason)
                                        <p class="text-[10px] text-red-600 max-w-[10rem] truncate" title="{{ $emprendedor->verification_rejection_reason }}">
                                            {{ $emprendedor->verification_rejection_reason }}
                                        </p>
                                    @endif

                                </div>

                            </td>

                            <td class="px-6 py-4">

                                <div class="flex flex-wrap justify-end items-center gap-2">

                                    {{-- Ver detalles: siempre disponible --}}
                                    <button
                                        wire:click="viewDetails({{ $emprendedor->id }})"
                                        class="px-3 py-2 rounded-lg
                                               bg-slate-50 text-slate-600
                                               hover:bg-slate-100
                                               text-xs font-semibold
                                               transition inline-flex items-center gap-1.5"
                                    >
                                        <i class="fa-solid fa-eye"></i>
                                        Ver detalles
                                    </button>

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
                                            wire:click="openReject({{ $emprendedor->id }})"
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

    {{-- =========================================================
         MODAL: DETALLE DEL EMPRENDEDOR (datos + documentos)
    ========================================================== --}}
    @if($showDetailsModal && $viewingEmprendedor)

        @php
            $statusStylesModal = [
                'PENDIENTE' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'dot' => 'bg-amber-500'],
                'ACTIVO' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'dot' => 'bg-blue-600'],
                'RECHAZADO' => ['bg' => 'bg-red-50', 'text' => 'text-red-700', 'dot' => 'bg-red-500'],
                'SUSPENDIDO' => ['bg' => 'bg-slate-100', 'text' => 'text-slate-700', 'dot' => 'bg-slate-500'],
            ];
            $modalStyle = $statusStylesModal[$viewingEmprendedor->status] ?? [
                'bg' => 'bg-slate-50', 'text' => 'text-slate-600', 'dot' => 'bg-slate-400',
            ];
        @endphp

        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4"
            wire:key="emprendedor-details-modal-{{ $viewingEmprendedor->id }}"
        >

            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">

                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-display text-lg font-bold text-[#111111]">
                        Detalle del emprendedor
                    </h3>
                    <button wire:click="closeDetails" class="text-slate-400 hover:text-slate-600">
                        ✕
                    </button>
                </div>

                <div class="flex items-center justify-between mb-4">
                    <p class="font-display text-xl font-bold text-[#111111]">
                        {{ $viewingEmprendedor->business_name }}
                    </p>

                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold {{ $modalStyle['bg'] }} {{ $modalStyle['text'] }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $modalStyle['dot'] }}"></span>
                        {{ str_replace('_', ' ', $viewingEmprendedor->status) }}
                    </span>
                </div>

                <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm mb-2">

                    <div class="col-span-2">
                        <dt class="text-xs font-medium text-[#6B6B66]">Responsable</dt>
                        <dd class="text-[#111111]">{{ $viewingEmprendedor->user?->name }}</dd>
                    </div>

                    <div class="col-span-2">
                        <dt class="text-xs font-medium text-[#6B6B66]">Correo</dt>
                        <dd class="text-[#111111]">{{ $viewingEmprendedor->user?->email }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium text-[#6B6B66]">Teléfono</dt>
                        <dd class="text-[#111111]">{{ $viewingEmprendedor->user?->phone ?? '—' }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium text-[#6B6B66]">Cédula</dt>
                        <dd class="text-[#111111]">{{ $viewingEmprendedor->cedula ?? '—' }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium text-[#6B6B66]">Ciudad</dt>
                        <dd class="text-[#111111]">{{ $viewingEmprendedor->city ?? '—' }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium text-[#6B6B66]">Estado (región)</dt>
                        <dd class="text-[#111111]">{{ $viewingEmprendedor->state ?? '—' }}</dd>
                    </div>

                    <div class="col-span-2">
                        <dt class="text-xs font-medium text-[#6B6B66]">RIF</dt>
                        <dd class="text-[#111111]">{{ $viewingEmprendedor->document_id }}</dd>
                    </div>

                    <div class="col-span-2">
                        <dt class="text-xs font-medium text-[#6B6B66]">Descripción del emprendimiento</dt>
                        <dd class="text-[#111111]">{{ $viewingEmprendedor->descripcion ?? '—' }}</dd>
                    </div>

                    <div class="col-span-2">
                        <dt class="text-xs font-medium text-[#6B6B66]">Agencia de retiro</dt>
                        <dd class="text-[#111111]">{{ $viewingEmprendedor->pickupAlly?->business_name ?? 'Sin asignar' }}</dd>
                    </div>

                    <div class="col-span-2">
                        <dt class="text-xs font-medium text-[#6B6B66]">Postulado el</dt>
                        <dd class="text-[#111111]">{{ $viewingEmprendedor->created_at?->format('d/m/Y h:i A') }}</dd>
                    </div>

                </dl>

                <div class="border-t border-[#E5E5E0] mt-4 pt-4">
                    <p class="text-xs font-medium text-[#6B6B66] mb-2">Verificación de identidad</p>

                    <x-verification-status-badge :status="$viewingEmprendedor->verification_status" />

                    @if($viewingEmprendedor->verification_reviewed_at)
                        <p class="text-xs text-[#6B6B66] mt-1.5">
                            Revisado el {{ $viewingEmprendedor->verification_reviewed_at->format('d/m/Y h:i A') }}
                        </p>
                    @endif

                    @if($viewingEmprendedor->verification_status === Emprendedor::VERIFICATION_REJECTED && $viewingEmprendedor->verification_rejection_reason)
                        <p class="text-xs text-red-700 bg-red-50 border border-red-200 rounded-lg px-3 py-2 mt-2">
                            <strong>Motivo del rechazo:</strong> {{ $viewingEmprendedor->verification_rejection_reason }}
                        </p>
                    @endif
                </div>

                @php
                    $emprendedorDocuments = [
                        'cedula-front' => ['path' => $viewingEmprendedor->cedula_front_photo_path, 'route' => 'emprendedores.documents.cedula-front', 'label' => 'Cédula (frente)'],
                        'cedula-back' => ['path' => $viewingEmprendedor->cedula_back_photo_path, 'route' => 'emprendedores.documents.cedula-back', 'label' => 'Cédula (reverso)'],
                        'rif' => ['path' => $viewingEmprendedor->rif_document_path, 'route' => 'emprendedores.documents.rif', 'label' => 'RIF'],
                        'product-or-workspace' => ['path' => $viewingEmprendedor->product_or_workspace_photo_path, 'route' => 'emprendedores.documents.product-or-workspace', 'label' => 'Foto de productos / espacio de trabajo'],
                    ];
                @endphp

                @if(collect($emprendedorDocuments)->contains(fn ($doc) => $doc['path']))
                    <div class="border-t border-[#E5E5E0] mt-4 pt-4">
                        <p class="text-xs font-medium text-[#6B6B66] mb-2">Documentos de verificación</p>

                        <div class="flex flex-col gap-1.5 text-sm">
                            @foreach($emprendedorDocuments as $doc)
                                @if($doc['path'])
                                    <a href="{{ route($doc['route'], $viewingEmprendedor) }}" target="_blank"
                                       class="inline-flex items-center gap-1.5 text-blue-700 hover:underline">
                                        <i class="fa-solid fa-file"></i> {{ $doc['label'] }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @else
                    <p class="text-xs text-[#B8B8B2] mt-4 pt-4 border-t border-[#E5E5E0]">
                        Este emprendedor no tiene documentos de verificación cargados.
                    </p>
                @endif

                <div class="flex justify-end mt-4">
                    <button wire:click="closeDetails"
                        class="px-4 py-2 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-50">
                        Cerrar
                    </button>
                </div>

            </div>

        </div>

    @endif

    {{-- =========================================================
         MODAL: MOTIVO DE RECHAZO (obligatorio)
    ========================================================== --}}
    @if($rejectingEmprendedorId)

        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4"
            wire:key="reject-modal-{{ $rejectingEmprendedorId }}"
        >

            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">

                <h3 class="font-display text-lg font-bold text-[#111111] mb-2">
                    Motivo de rechazo
                </h3>

                <p class="text-sm text-[#6B6B66] mb-4">
                    Este motivo quedará guardado y el emprendedor podrá verlo para corregir su solicitud.
                </p>

                <textarea
                    wire:model="rejectionReason"
                    rows="4"
                    class="w-full rounded-xl border border-[#E5E5E0] px-4 py-3 text-sm text-[#111111]
                           placeholder:text-[#B8B8B2] focus:border-red-500 focus:ring-red-500"
                    placeholder="Ej: La foto del RIF no es legible, por favor sube una nueva."
                ></textarea>

                @error('rejectionReason')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror

                <div class="flex justify-end gap-2 mt-4">
                    <button
                        type="button"
                        wire:click="cancelReject"
                        class="px-4 py-2 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-50 transition"
                    >
                        Cancelar
                    </button>

                    <button
                        type="button"
                        wire:click="reject"
                        class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-red-600 hover:bg-red-700 transition"
                    >
                        Rechazar
                    </button>
                </div>

            </div>

        </div>

    @endif

</div>
