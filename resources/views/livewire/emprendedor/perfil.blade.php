<div class="max-w-3xl space-y-6 font-sans">

    <div>
        <h1 class="font-display text-3xl font-bold tracking-tight text-[#111111]">Mi Tienda</h1>
        <p class="text-sm text-[#6B6B66] mt-1">
            Esto es lo que ve un comprador cuando entra a tu tienda en el marketplace. Complétalo para que
            confíe en que eres un negocio real.
        </p>
    </div>

    @if ($successMessage)
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ $successMessage }}
        </div>
    @endif

    <form wire:submit="save" class="bg-white rounded-2xl border border-[#E5E5E0] shadow-sm overflow-hidden">

        {{-- =========================================================
             PORTADA
        ========================================================== --}}
        <div class="relative h-40 bg-slate-100">
            @if ($cover && $cover->isPreviewable())
                <img src="{{ $cover->temporaryUrl() }}" class="w-full h-full object-cover" alt="Vista previa de portada">
            @elseif ($existingCoverPath)
                <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($existingCoverPath) }}"
                     class="w-full h-full object-cover" alt="Portada actual">
            @else
                <div class="w-full h-full flex items-center justify-center text-slate-300 text-sm">Sin portada</div>
            @endif

            <label class="absolute bottom-3 right-3 cursor-pointer bg-white/90 hover:bg-white text-xs font-semibold text-[#111111] px-3 py-1.5 rounded-lg shadow transition">
                <input type="file" wire:model="cover" class="hidden">
                Cambiar portada
            </label>

            {{-- LOGO, superpuesto sobre la portada --}}
            <div class="absolute -bottom-10 left-5 h-20 w-20 rounded-2xl bg-white border-4 border-white shadow-md overflow-hidden">
                @if ($logo && $logo->isPreviewable())
                    <img src="{{ $logo->temporaryUrl() }}" class="w-full h-full object-cover" alt="Vista previa de logo">
                @elseif ($existingLogoPath)
                    <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($existingLogoPath) }}"
                         class="w-full h-full object-cover" alt="Logo actual">
                @else
                    <div class="w-full h-full bg-amber-100 flex items-center justify-center text-amber-700 text-2xl font-bold">
                        {{ strtoupper(substr($emprendedor->business_name, 0, 1)) }}
                    </div>
                @endif
            </div>
        </div>

        <div class="pt-12 px-5 pb-5 space-y-4">

            <div>
                <label class="cursor-pointer text-xs font-semibold text-blue-600 hover:text-blue-800">
                    <input type="file" wire:model="logo" class="hidden">
                    Cambiar logo
                </label>
            </div>
            @error('cover') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            @error('logo') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

            <div>
                <p class="font-semibold text-[#111111]">{{ $emprendedor->business_name }}</p>
                <p class="text-xs text-[#6B6B66] mt-0.5">
                    {{ auth()->user()->phone ?: 'Sin teléfono' }} · {{ auth()->user()->email }} ·
                    <a href="{{ route('profile') }}" wire:navigate class="text-blue-600 hover:text-blue-800 underline">
                        editar en Mi Perfil
                    </a>
                </p>
            </div>

            <div>
                <label class="block text-xs font-medium text-[#6B6B66] mb-1">Descripción del negocio</label>
                <textarea wire:model="descripcion" rows="4" maxlength="1000"
                          placeholder="Cuéntales a tus compradores a qué te dedicas, qué vendes, desde cuándo..."
                          class="w-full rounded-xl border-[#E5E5E0] text-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                @error('descripcion') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-[#6B6B66] mb-1">Ubicación física (dirección del local)</label>
                <input type="text" wire:model="address" maxlength="255"
                       placeholder="Ej: Av. Bolívar, C.C. Los Próceres, local 12, Valencia"
                       class="w-full rounded-xl border-[#E5E5E0] text-sm focus:ring-blue-500 focus:border-blue-500">
                @error('address') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-xl transition">
                Guardar cambios
            </button>

        </div>
    </form>

    @if ($emprendedor->pickupAlly)
        <a href="{{ route('public.marketplace.store', $emprendedor->id) }}" target="_blank"
           class="inline-flex items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-800">
            Ver mi tienda como la ve un comprador →
        </a>
    @endif

</div>
