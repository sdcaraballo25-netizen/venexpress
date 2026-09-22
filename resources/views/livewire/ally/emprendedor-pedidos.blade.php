<div class="max-w-2xl space-y-6 font-sans">

    <div>
        <h1 class="font-display text-3xl font-bold tracking-tight text-[#111111]">Emprendedores</h1>
        <p class="text-sm text-[#6B6B66] mt-1">
            Genera la guía real de un pedido del marketplace cuando el emprendedor traiga el paquete a tu agencia.
        </p>
    </div>

    @if ($successMessage)
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ $successMessage }}
        </div>
    @endif

    @if ($errorMessage)
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
            {{ $errorMessage }}
        </div>
    @endif

    {{-- =========================================================
         BUSCAR PEDIDO
    ========================================================== --}}
    <div class="bg-white rounded-2xl border border-[#E5E5E0] shadow-sm p-5">
        <form wire:submit.prevent="buscar" class="flex items-end gap-3">
            <div class="flex-1">
                <label class="block text-xs font-medium text-[#6B6B66] mb-1">Nº de pedido</label>
                <input type="text" wire:model="pedidoIdInput" placeholder="Ej: 12"
                       class="w-full rounded-xl border-[#E5E5E0] text-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-xl transition">
                Buscar
            </button>
        </form>
        @if ($searchError)
            <p class="mt-2 text-xs text-red-600">{{ $searchError }}</p>
        @endif
    </div>

    @if ($pedido)
        {{-- =========================================================
             DATOS DEL PEDIDO (de solo lectura)
        ========================================================== --}}
        <div class="bg-white rounded-2xl border border-[#E5E5E0] shadow-sm p-5">
            <p class="text-xs font-bold text-[#6B6B66] uppercase tracking-wider mb-4">Pedido #{{ $pedido->id }}</p>

            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-xs text-[#6B6B66]">Remitente</p>
                    <p class="font-medium text-[#111111]">{{ $pedido->emprendedor->business_name }}</p>
                </div>
                <div>
                    <p class="text-xs text-[#6B6B66]">Producto</p>
                    <p class="font-medium text-[#111111]">{{ $pedido->producto?->nombre }} ({{ $pedido->cantidad }} unidad(es))</p>
                </div>
                <div>
                    <p class="text-xs text-[#6B6B66]">Destinatario</p>
                    <p class="font-medium text-[#111111]">{{ $pedido->cliente_nombre }}</p>
                    <p class="text-xs text-[#6B6B66]">{{ $pedido->cliente_id_doc }} · {{ $pedido->cliente_telefono }}</p>
                </div>
                <div>
                    <p class="text-xs text-[#6B6B66]">Destino</p>
                    <p class="font-medium text-[#111111]">{{ $pedido->destino_ciudad }}, {{ $pedido->destino_estado }}</p>
                    <p class="text-xs text-[#6B6B66]">{{ $pedido->direccion_entrega }}</p>
                    @if ($pedido->referencia_entrega)
                        <p class="text-xs text-[#6B6B66]">Ref: {{ $pedido->referencia_entrega }}</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- =========================================================
             VERIFICACIÓN FÍSICA DEL PAQUETE
        ========================================================== --}}
        <form wire:submit.prevent="generarGuia" class="bg-white rounded-2xl border border-[#E5E5E0] shadow-sm p-5 space-y-4">
            <p class="text-xs font-bold text-[#6B6B66] uppercase tracking-wider">Verificación del paquete</p>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-[#6B6B66] mb-1">Peso real (kg)</label>
                    <input type="number" step="0.01" min="0.01" wire:model.live="physical_weight_kg"
                           class="w-full rounded-xl border-[#E5E5E0] text-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('physical_weight_kg') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-xs font-medium text-[#6B6B66] mb-1">Largo (cm)</label>
                        <input type="number" step="0.1" min="0" wire:model.live="length_cm"
                               class="w-full rounded-xl border-[#E5E5E0] text-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-[#6B6B66] mb-1">Ancho (cm)</label>
                        <input type="number" step="0.1" min="0" wire:model.live="width_cm"
                               class="w-full rounded-xl border-[#E5E5E0] text-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-[#6B6B66] mb-1">Alto (cm)</label>
                        <input type="number" step="0.1" min="0" wire:model.live="height_cm"
                               class="w-full rounded-xl border-[#E5E5E0] text-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-6">
                <label class="flex items-center gap-2 text-sm text-[#4A4A45]">
                    <input type="checkbox" wire:model.live="is_fragile" class="rounded border-[#E5E5E0] text-blue-600 focus:ring-blue-500">
                    Es delicado / frágil
                </label>
                <label class="flex items-center gap-2 text-sm text-[#4A4A45]">
                    <input type="checkbox" wire:model.live="has_insurance" class="rounded border-[#E5E5E0] text-blue-600 focus:ring-blue-500">
                    Lleva seguro
                </label>
            </div>

            @if ($has_insurance)
                <div>
                    <label class="block text-xs font-medium text-[#6B6B66] mb-1">Valor declarado (USD)</label>
                    <input type="number" step="0.01" min="0.01" wire:model.live="declared_value_usd"
                           class="w-full rounded-xl border-[#E5E5E0] text-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('declared_value_usd') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            @endif

            @if ($pricePreview)
                <div class="rounded-xl bg-slate-50 border border-[#E5E5E0] px-4 py-3 text-sm text-[#4A4A45]">
                    Peso facturable: <strong>{{ number_format($pricePreview['billable_weight_kg'], 2) }} kg</strong>
                    · Total: <strong class="text-[#111111]">${{ number_format($pricePreview['total_price_usd'], 2) }}</strong>
                    (Bs. {{ number_format($pricePreview['total_price_ves'], 2) }})
                    <span class="block text-xs text-[#6B6B66] mt-1">Ya incluye el descuento de emprendedor vigente.</span>
                </div>
            @endif

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-xl transition">
                Generar guía
            </button>
        </form>
    @endif

</div>
