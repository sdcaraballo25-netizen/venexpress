<div>
    <section class="bg-white">
        <div class="max-w-2xl mx-auto px-6 py-14">

            <div class="text-center mb-10">
                <span class="inline-block bg-amber-100 text-amber-700 text-xs font-semibold tracking-wide uppercase px-3 py-1 rounded-full mb-4">
                    Tu opinión nos ayuda
                </span>
                <h1 class="text-3xl md:text-4xl font-extrabold text-black">Envíanos tu recomendación</h1>
                <p class="text-gray-500 mt-3 max-w-xl mx-auto">
                    ¿Se te ocurre algo que podríamos mejorar? Cuéntanos.
                </p>
            </div>

            @if ($submitted)
                <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-8 text-center">
                    <p class="text-emerald-700 font-semibold text-lg">¡Gracias por tu recomendación!</p>
                    <p class="text-emerald-600 mt-2 text-sm">La leeremos con atención.</p>
                </div>
            @else
                <form wire:submit="submit" class="bg-gray-50 border border-gray-100 rounded-2xl p-6 space-y-5">

                    <div>
                        <label class="block text-sm font-semibold text-black mb-2">Nombre</label>
                        <input type="text" wire:model="name"
                            class="w-full rounded-lg border-gray-200 text-sm focus:ring-black focus:border-black">
                        @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-black mb-2">Correo electrónico (opcional)</label>
                        <input type="email" wire:model="email"
                            class="w-full rounded-lg border-gray-200 text-sm focus:ring-black focus:border-black">
                        @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-black mb-2">Tu recomendación</label>
                        <textarea wire:model="message" rows="5"
                            class="w-full rounded-lg border-gray-200 text-sm focus:ring-black focus:border-black"></textarea>
                        @error('message') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit"
                        class="w-full bg-black hover:bg-gray-800 text-white font-semibold text-sm px-6 py-3 rounded-lg transition">
                        Enviar recomendación
                    </button>

                </form>
            @endif

        </div>
    </section>
</div>
