@php
    use App\Models\Driver;
@endphp

<div class="min-h-screen">

    {{-- =========================================================
         ENCABEZADO
    ========================================================== --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">

        <div>
            <h1 class="font-display text-3xl font-bold text-[#111111]">
                Aprobación de Repartidores
            </h1>

            <p class="text-sm text-[#6B6B66] mt-1">
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
                placeholder="Buscar por nombre, correo o placa..."
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

                {{-- CABECERA --}}
                <thead>

                    <tr class="bg-slate-50 border-b border-[#E5E5E0]">

                        <th class="px-6 py-4 text-left text-xs font-semibold text-[#6B6B66] uppercase">
                            Repartidor
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold text-[#6B6B66] uppercase">
                            Vehículo
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold text-[#6B6B66] uppercase">
                            Tipo
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold text-[#6B6B66] uppercase">
                            Documentos
                        </th>

                        <th class="px-6 py-4 text-center text-xs font-semibold text-[#6B6B66] uppercase">
                            Estado
                        </th>

                        <th class="px-6 py-4 text-right text-xs font-semibold text-[#6B6B66] uppercase">
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


                        <tr class="border-b border-[#F0F0EC] last:border-0 hover:bg-slate-50 transition">


                            {{-- =================================================
                                 REPARTIDOR
                            ================================================== --}}
                            <td class="px-6 py-4">

                                <button
                                    type="button"
                                    wire:click="viewDetails({{ $driver->id }})"
                                    class="text-left"
                                    title="Ver detalles del repartidor"
                                >

                                    <p class="font-semibold text-[#111111] hover:underline">
                                        {{ $driver->user?->name }}
                                    </p>

                                    <p class="text-xs text-[#6B6B66] mt-1">
                                        {{ $driver->user?->email }}
                                    </p>

                                </button>

                            </td>


                            {{-- =================================================
                                 VEHÍCULO
                            ================================================== --}}
                            <td class="px-6 py-4 text-[#4A4A45]">

                                {{ $driver->vehicle_plate }} · {{ $driver->vehicle_type }}

                            </td>


                            {{-- =================================================
                                 TIPO (delivery / hub)
                            ================================================== --}}
                            <td class="px-6 py-4 text-[#4A4A45]">

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
                                        <span class="text-xs text-[#B8B8B2]">Sin documentos</span>
                                    @endif

                                </div>

                            </td>


                            {{-- =================================================
                                 ESTADO (operativo + verificación)
                            ================================================== --}}
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

                                        {{ str_replace('_', ' ', $driver->status) }}

                                    </span>

                                    <x-verification-status-badge :status="$driver->verification_status" small title="Verificación de identidad" />

                                    @if($driver->verification_status === Driver::VERIFICATION_REJECTED && $driver->verification_rejection_reason)
                                        <p class="text-[10px] text-red-600 max-w-[10rem] truncate" title="{{ $driver->verification_rejection_reason }}">
                                            {{ $driver->verification_rejection_reason }}
                                        </p>
                                    @endif

                                </div>

                            </td>


                            {{-- =================================================
                                 ACCIONES
                            ================================================== --}}
                            <td class="px-6 py-4">

                                <div class="flex flex-wrap justify-end items-center gap-2">

                                    {{-- Ver detalles: siempre disponible --}}
                                    <button
                                        wire:click="viewDetails({{ $driver->id }})"
                                        class="px-3 py-2 rounded-lg
                                               bg-slate-50 text-slate-600
                                               hover:bg-slate-100
                                               text-xs font-semibold
                                               transition inline-flex items-center gap-1.5"
                                    >
                                        <i class="fa-solid fa-eye"></i>
                                        Ver detalles
                                    </button>

                                    {{-- =========================================
                                         PENDIENTE
                                    ========================================== --}}
                                    @if($driver->status === Driver::STATUS_PENDING)

                                        {{-- Aprobar --}}
                                        <button
                                            @click.prevent="$store.confirm.open({
                                                message: '¿Estás seguro de que deseas aprobar a este repartidor?',
                                                confirmText: 'Aprobar',
                                                variant: 'primary',
                                                onConfirm: () => $wire.approve({{ $driver->id }}),
                                            })"
                                            class="px-3 py-2 rounded-lg
                                                   bg-blue-600 text-white
                                                   hover:bg-blue-700
                                                   text-xs font-semibold
                                                   transition"
                                        >
                                            Aprobar
                                        </button>


                                        {{-- Rechazar (motivo obligatorio, ver modal al final) --}}
                                        <button
                                            wire:click="openReject({{ $driver->id }})"
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
                                            @click.prevent="$store.confirm.open({
                                                message: '¿Estás seguro de que deseas suspender a este repartidor?',
                                                confirmText: 'Suspender',
                                                variant: 'warning',
                                                onConfirm: () => $wire.suspend({{ $driver->id }}),
                                            })"
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
                                            @click.prevent="$store.confirm.open({
                                                message: '¿Deseas activar nuevamente a este repartidor?',
                                                confirmText: 'Activar',
                                                variant: 'primary',
                                                onConfirm: () => $wire.activate({{ $driver->id }}),
                                            })"
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

                                        <span class="text-xs text-[#B8B8B2]">
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

                                    <p class="font-semibold text-[#111111]">
                                        No hay repartidores registrados
                                    </p>

                                    <p class="text-sm text-[#6B6B66] mt-1">
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

            <div class="px-6 py-4 border-t border-[#E5E5E0]">

                {{ $drivers->links() }}

            </div>

        @endif

    </div>

    {{-- =========================================================
         MODAL: DETALLE DEL REPARTIDOR (datos + documentos)
    ========================================================== --}}
    @if($showDetailsModal && $viewingDriver)

        @php
            $modalStyle = $statusStyles[$viewingDriver->status] ?? [
                'bg' => 'bg-slate-50', 'text' => 'text-slate-600', 'dot' => 'bg-slate-400',
            ];
        @endphp

        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4"
            wire:key="driver-details-modal-{{ $viewingDriver->id }}"
        >

            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">

                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-display text-lg font-bold text-[#111111]">
                        Detalle del repartidor
                    </h3>
                    <button wire:click="closeDetails" class="text-slate-400 hover:text-slate-600">
                        ✕
                    </button>
                </div>

                <div class="flex items-center justify-between mb-4">
                    <p class="font-display text-xl font-bold text-[#111111]">
                        {{ $viewingDriver->user?->name }}
                    </p>

                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold {{ $modalStyle['bg'] }} {{ $modalStyle['text'] }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $modalStyle['dot'] }}"></span>
                        {{ str_replace('_', ' ', $viewingDriver->status) }}
                    </span>
                </div>

                <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm mb-2">

                    <div class="col-span-2">
                        <dt class="text-xs font-medium text-[#6B6B66]">Correo</dt>
                        <dd class="text-[#111111]">{{ $viewingDriver->user?->email }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium text-[#6B6B66]">Teléfono</dt>
                        <dd class="text-[#111111]">{{ $viewingDriver->phone ?? '—' }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium text-[#6B6B66]">Cédula</dt>
                        <dd class="text-[#111111]">{{ $viewingDriver->cedula ?? '—' }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium text-[#6B6B66]">Ciudad</dt>
                        <dd class="text-[#111111]">{{ $viewingDriver->city ?? '—' }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium text-[#6B6B66]">Estado (región)</dt>
                        <dd class="text-[#111111]">{{ $viewingDriver->state ?? '—' }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium text-[#6B6B66]">Placa</dt>
                        <dd class="text-[#111111]">{{ $viewingDriver->vehicle_plate }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium text-[#6B6B66]">Tipo de vehículo</dt>
                        <dd class="text-[#111111]">{{ $viewingDriver->vehicle_type }}</dd>
                    </div>

                    <div class="col-span-2">
                        <dt class="text-xs font-medium text-[#6B6B66]">Postulado el</dt>
                        <dd class="text-[#111111]">{{ $viewingDriver->created_at?->format('d/m/Y h:i A') }}</dd>
                    </div>

                </dl>

                <div class="border-t border-[#E5E5E0] mt-4 pt-4">
                    <p class="text-xs font-medium text-[#6B6B66] mb-2">Verificación de identidad</p>

                    <x-verification-status-badge :status="$viewingDriver->verification_status" />

                    @if($viewingDriver->verification_reviewed_at)
                        <p class="text-xs text-[#6B6B66] mt-1.5">
                            Revisado el {{ $viewingDriver->verification_reviewed_at->format('d/m/Y h:i A') }}
                        </p>
                    @endif

                    @if($viewingDriver->verification_status === Driver::VERIFICATION_REJECTED && $viewingDriver->verification_rejection_reason)
                        <p class="text-xs text-red-700 bg-red-50 border border-red-200 rounded-lg px-3 py-2 mt-2">
                            <strong>Motivo del rechazo:</strong> {{ $viewingDriver->verification_rejection_reason }}
                        </p>
                    @endif
                </div>

                @php
                    $driverDocuments = [
                        'license' => ['path' => $viewingDriver->license_photo_path, 'route' => 'drivers.documents.license', 'label' => 'Licencia'],
                        'id' => ['path' => $viewingDriver->id_photo_path, 'route' => 'drivers.documents.id', 'label' => 'Cédula (frente)'],
                        'cedula-back' => ['path' => $viewingDriver->cedula_back_photo_path, 'route' => 'drivers.documents.cedula-back', 'label' => 'Cédula (reverso)'],
                        'selfie' => ['path' => $viewingDriver->selfie_photo_path, 'route' => 'drivers.documents.selfie', 'label' => 'Selfie'],
                        'vehicle-photo' => ['path' => $viewingDriver->vehicle_photo_path, 'route' => 'drivers.documents.vehicle-photo', 'label' => 'Foto del vehículo'],
                        'plate-photo' => ['path' => $viewingDriver->plate_photo_path, 'route' => 'drivers.documents.plate-photo', 'label' => 'Foto de la placa'],
                        'vehicle-registration' => ['path' => $viewingDriver->vehicle_registration_photo_path, 'route' => 'drivers.documents.vehicle-registration', 'label' => 'Carnet de circulación'],
                    ];
                @endphp

                @if(collect($driverDocuments)->contains(fn ($doc) => $doc['path']))
                    <div class="border-t border-[#E5E5E0] mt-4 pt-4">
                        <p class="text-xs font-medium text-[#6B6B66] mb-2">Documentos de verificación</p>

                        <div class="flex flex-col gap-1.5 text-sm">
                            @foreach($driverDocuments as $doc)
                                @if($doc['path'])
                                    <a href="{{ route($doc['route'], $viewingDriver) }}" target="_blank"
                                       class="inline-flex items-center gap-1.5 text-blue-700 hover:underline">
                                        <i class="fa-solid fa-file"></i> {{ $doc['label'] }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @else
                    <p class="text-xs text-[#B8B8B2] mt-4 pt-4 border-t border-[#E5E5E0]">
                        Este repartidor no tiene documentos de verificación cargados.
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
    @if($rejectingDriverId)

        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4"
            wire:key="reject-modal-{{ $rejectingDriverId }}"
        >

            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">

                <h3 class="font-display text-lg font-bold text-[#111111] mb-2">
                    Motivo de rechazo
                </h3>

                <p class="text-sm text-[#6B6B66] mb-4">
                    Este motivo quedará guardado y el repartidor podrá verlo para corregir su solicitud.
                </p>

                <textarea
                    wire:model="rejectionReason"
                    rows="4"
                    class="w-full rounded-xl border border-[#E5E5E0] px-4 py-3 text-sm text-[#111111]
                           placeholder:text-[#B8B8B2] focus:border-red-500 focus:ring-red-500"
                    placeholder="Ej: La foto de la cédula está borrosa, por favor sube una nueva."
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
