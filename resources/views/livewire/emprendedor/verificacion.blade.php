@php
    use App\Models\Emprendedor;
@endphp

<div class="max-w-2xl space-y-6 font-sans">

    <div>
        <h1 class="font-display text-3xl font-bold tracking-tight text-[#111111]">Mi Verificación</h1>
        <p class="text-sm text-[#6B6B66] mt-1">
            Completa tus datos y documentos para que un administrador pueda verificarte. Sin esto no puedes
            publicar ni vender productos en el Marketplace.
        </p>
    </div>

    <div class="flex items-center gap-2">
        <x-verification-status-badge :status="$emprendedor->verification_status" />
    </div>

    @if ($emprendedor->verification_status === Emprendedor::VERIFICATION_REJECTED && $emprendedor->verification_rejection_reason)
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <strong>Motivo del rechazo:</strong> {{ $emprendedor->verification_rejection_reason }}
        </div>
    @endif

    @if ($emprendedor->verification_status === Emprendedor::VERIFICATION_VERIFIED)

        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            Ya estás verificado. Si necesitas corregir algún dato, contacta a soporte de Venexpress.
        </div>

    @elseif ($emprendedor->verification_status === Emprendedor::VERIFICATION_IN_REVIEW)

        <div class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-700">
            Tu información ya fue enviada y está siendo revisada por un administrador. Te avisaremos por
            correo cuando haya una respuesta.
        </div>

    @else

        <form wire:submit="submit" class="bg-white rounded-2xl border border-[#E5E5E0] shadow-sm p-5 space-y-4">

            <p class="text-xs text-[#6B6B66]">
                {{ auth()->user()->name }} · {{ auth()->user()->phone ?: 'Sin teléfono' }} ·
                <a href="{{ route('profile') }}" wire:navigate class="text-blue-600 hover:text-blue-800 underline">
                    editar nombre/teléfono en Mi Perfil
                </a>
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-[#6B6B66] mb-1">Nombre del emprendimiento</label>
                    <input type="text" wire:model="business_name" maxlength="255"
                           class="w-full rounded-xl border-[#E5E5E0] text-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('business_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-[#6B6B66] mb-1">Cédula</label>
                    <input type="text" wire:model="cedula" maxlength="20"
                           class="w-full rounded-xl border-[#E5E5E0] text-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('cedula') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-[#6B6B66] mb-1">RIF</label>
                    <input type="text" wire:model="document_id" maxlength="20"
                           class="w-full rounded-xl border-[#E5E5E0] text-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('document_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-[#6B6B66] mb-1">Ciudad</label>
                    <input type="text" wire:model="city" maxlength="255"
                           class="w-full rounded-xl border-[#E5E5E0] text-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('city') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-[#6B6B66] mb-1">Estado (región)</label>
                    <input type="text" wire:model="state" maxlength="255"
                           class="w-full rounded-xl border-[#E5E5E0] text-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('state') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-[#6B6B66] mb-1">Descripción breve</label>
                    <textarea wire:model="descripcion" rows="3" maxlength="1000"
                              placeholder="Cuéntanos a qué te dedicas, qué vendes..."
                              class="w-full rounded-xl border-[#E5E5E0] text-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                    @error('descripcion') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

            </div>

            <div class="border-t border-[#E5E5E0] pt-4">
                <p class="text-xs font-medium text-[#6B6B66] mb-3">Documentos</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    @foreach ([
                        ['field' => 'cedula_front_photo', 'existing' => $existingCedulaFrontPhotoPath, 'label' => 'Cédula (frente)'],
                        ['field' => 'cedula_back_photo', 'existing' => $existingCedulaBackPhotoPath, 'label' => 'Cédula (reverso)'],
                        ['field' => 'rif_document', 'existing' => $existingRifDocumentPath, 'label' => 'RIF'],
                        ['field' => 'product_or_workspace_photo', 'existing' => $existingProductOrWorkspacePhotoPath, 'label' => 'Foto de productos o espacio de trabajo'],
                    ] as $doc)
                        <div>
                            <label class="block text-xs font-medium text-[#6B6B66] mb-1">{{ $doc['label'] }}</label>
                            <input type="file" wire:model="{{ $doc['field'] }}"
                                   class="block w-full text-xs text-[#4A4A45] file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                            @if ($doc['existing'])
                                <p class="mt-1 text-[10px] text-emerald-700">Ya tienes uno cargado — sube otro solo si quieres reemplazarlo.</p>
                            @endif
                            @error($doc['field']) <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @endforeach

                </div>
            </div>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-xl transition">
                Enviar a revisión
            </button>

        </form>

    @endif

    <a href="{{ $emprendedor->canOperate() ? route('emprendedor.dashboard') : route('account.pending') }}" wire:navigate
       class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-500 hover:text-slate-700">
        ← Volver
    </a>

</div>
