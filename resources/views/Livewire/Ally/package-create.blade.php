<div class="mx-auto max-w-5xl px-4 py-8">

    <div class="mb-6">
        <p class="text-sm font-medium uppercase tracking-wide text-teal-700">Taquilla aliada</p>
        <h1 class="mt-1 text-2xl font-semibold text-slate-900">Registrar nueva guía</h1>
    </div>

    @if ($createdTrackingNumber)
        <div class="mb-6 rounded-lg border border-teal-200 bg-teal-50 px-5 py-4">
            <p class="text-sm font-medium text-teal-800">Guía registrada correctamente</p>
            <p class="mt-1 font-mono text-lg font-semibold text-teal-900">{{ $createdTrackingNumber }}</p>
            <button
                type="button"
                wire:click="$set('createdTrackingNumber', null)"
                class="mt-2 text-sm font-medium text-teal-700 underline underline-offset-2 hover:text-teal-900"
            >
                Registrar otra guía
            </button>
        </div>
    @endif

    <form wire:submit="save" class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- Columna del formulario --}}
        <div class="space-y-6 lg:col-span-2">

            {{-- Remitente --}}
            <fieldset class="rounded-lg border border-slate-200 bg-white p-5">
                <legend class="px-1 text-sm font-semibold text-slate-900">Remitente</legend>
                <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700">Nombre completo</label>
                        <input type="text" wire:model="sender_name"
                            class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-600 focus:ring-teal-600">
                        @error('sender_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Documento (V-, E-, J-, G-, P-, C-)</label>
                        <input type="text" wire:model="sender_id_doc" placeholder="V-12345678"
                            class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-600 focus:ring-teal-600">
                        @error('sender_id_doc') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Teléfono</label>
                        <input type="text" wire:model="sender_phone"
                            class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-600 focus:ring-teal-600">
                        @error('sender_phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </fieldset>

            {{-- Destinatario --}}
            <fieldset class="rounded-lg border border-slate-200 bg-white p-5">
                <legend class="px-1 text-sm font-semibold text-slate-900">Destinatario</legend>
                <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700">Nombre completo</label>
                        <input type="text" wire:model="recipient_name"
                            class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-600 focus:ring-teal-600">
                        @error('recipient_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Documento</label>
                        <input type="text" wire:model="recipient_id_doc" placeholder="V-87654321"
                            class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-600 focus:ring-teal-600">
                        @error('recipient_id_doc') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Teléfono</label>
                        <input type="text" wire:model="recipient_phone"
                            class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-600 focus:ring-teal-600">
                        @error('recipient_phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </fieldset>

            {{-- Envío --}}
            <fieldset class="rounded-lg border border-slate-200 bg-white p-5">
                <legend class="px-1 text-sm font-semibold text-slate-900">Detalles del envío</legend>
                <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Ciudad origen</label>
                        <input type="text" wire:model.live="origin_city"
                            class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-600 focus:ring-teal-600">
                        @error('origin_city') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Ciudad destino</label>
                        <input type="text" wire:model.live="destination_city"
                            class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-600 focus:ring-teal-600">
                        @error('destination_city') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Tipo</label>
                        <select wire:model="package_type"
                            class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-600 focus:ring-teal-600">
                            <option value="sobre">Sobre</option>
                            <option value="paquete">Paquete</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Peso físico (kg)</label>
                        <input type="number" step="0.001" wire:model.live="physical_weight_kg"
                            class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-600 focus:ring-teal-600">
                        @error('physical_weight_kg') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Largo (cm)</label>
                        <input type="number" step="0.01" wire:model.live="length_cm"
                            class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-600 focus:ring-teal-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Ancho (cm)</label>
                        <input type="number" step="0.01" wire:model.live="width_cm"
                            class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-600 focus:ring-teal-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Alto (cm)</label>
                        <input type="number" step="0.01" wire:model.live="height_cm"
                            class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-600 focus:ring-teal-600">
                    </div>
                </div>
            </fieldset>

            @error('ally') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

            <button type="submit"
                wire:loading.attr="disabled"
                wire:target="save"
                class="inline-flex items-center rounded-md bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-800 disabled:cursor-not-allowed disabled:opacity-60">
                <span wire:loading.remove wire:target="save">Registrar guía</span>
                <span wire:loading wire:target="save">Guardando…</span>
            </button>
        </div>

        {{-- Columna de cotización en vivo --}}
        <div class="lg:col-span-1">
            <div class="sticky top-6 rounded-lg border border-slate-200 bg-white p-5">
                <p class="text-sm font-semibold text-slate-900">Cotización</p>

                @error('pricing')
                    <p class="mt-3 text-sm text-amber-700">{{ $message }}</p>
                @else
                    @if ($this->pricingPreview)
                        <dl class="mt-4 space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-slate-500">Peso volumétrico</dt>
                                <dd class="font-medium text-slate-900">{{ number_format($this->pricingPreview['volumetric_weight_kg'], 3) }} kg</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500">Peso facturable</dt>
                                <dd class="font-medium text-slate-900">{{ number_format($this->pricingPreview['billable_weight_kg'], 3) }} kg</dd>
                            </div>
                            <div class="flex justify-between border-t border-slate-100 pt-3">
                                <dt class="text-slate-500">Total USD</dt>
                                <dd class="font-semibold text-slate-900">${{ number_format($this->pricingPreview['total_price_usd'], 2) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500">Total VES</dt>
                                <dd class="font-semibold text-slate-900">Bs. {{ number_format($this->pricingPreview['total_price_ves'], 2) }}</dd>
                            </div>
                            <div class="flex justify-between text-xs text-slate-400">
                                <dt>Tasa BCV usada</dt>
                                <dd>{{ number_format($this->pricingPreview['bcv_rate_used'], 4) }}</dd>
                            </div>
                        </dl>
                    @else
                        <p class="mt-3 text-sm text-slate-500">Completa origen, destino y peso para ver el total.</p>
                    @endif
                @enderror
            </div>
        </div>
    </form>
</div>
