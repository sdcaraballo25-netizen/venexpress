<div class="max-w-2xl">

    <div class="mb-8">
        <h1 class="font-display text-3xl font-bold text-[#111111]">
            Recomendaciones
        </h1>

        <p class="text-sm text-[#6B6B66] mt-1">
            ¿Se te ocurre algo que podríamos mejorar? Cuéntanos, el equipo de Venexpress lee cada una.
        </p>
    </div>

    @if ($submitted)
        <div class="bg-white border border-[#E5E5E0] rounded-2xl p-8 text-center shadow-sm">
            <p class="text-emerald-700 font-semibold text-lg">¡Gracias por tu recomendación!</p>
            <p class="text-[#6B6B66] mt-2 text-sm">La leeremos con atención.</p>

            <button
                wire:click="$set('submitted', false)"
                class="mt-6 text-sm font-semibold text-[#111111] underline underline-offset-2"
            >
                Enviar otra
            </button>
        </div>
    @else
        <form wire:submit="submit" class="bg-white border border-[#E5E5E0] rounded-2xl p-6 shadow-sm space-y-5">
            <div>
                <label class="block text-sm font-semibold text-[#111111] mb-2">Tu recomendación</label>
                <textarea
                    wire:model="message"
                    rows="6"
                    placeholder="Cuéntanos qué te gustaría ver en Venexpress..."
                    class="w-full rounded-lg border-[#E5E5E0] text-sm focus:ring-amber-400 focus:border-amber-400"
                ></textarea>
                @error('message') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <button
                type="submit"
                class="w-full bg-[#111111] hover:bg-black text-white font-semibold text-sm px-6 py-3 rounded-lg transition"
            >
                Enviar recomendación
            </button>
        </form>
    @endif

</div>
