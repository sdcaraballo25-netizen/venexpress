<div class="space-y-6">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="font-display text-2xl font-semibold text-[#0F172A]">
                Recepción de paquetes
            </h2>
            <p class="text-sm text-slate-500">
                Registra físicamente la llegada de una guía al Hub o punto de destino.
            </p>
        </div>

        <a
            href="{{ route('admin.dashboard') }}"
            class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
        >
            ← Volver
        </a>
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

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="font-display text-lg font-semibold text-slate-900">
                Localizar guía
            </h3>

            <form wire:submit.prevent="search" class="mt-5 space-y-4">
                <div>
                    <label class="text-sm font-medium text-slate-600">
                        Número de guía
                    </label>

                    <div class="mt-2 flex gap-2">
                        <input
                            type="text"
                            wire:model="trackingNumber"
                            placeholder="VEN-..."
                            autocomplete="off"
                            class="min-w-0 flex-1 rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-900 focus:ring-blue-900"
                        >

                        <button
                            type="submit"
                            class="rounded-xl bg-blue-900 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-800"
                        >
                            Buscar
                        </button>
                    </div>
                </div>

                {{-- Lector QR: rellena el campo de guía y dispara la
                     búsqueda. La recepción sigue confirmándose a mano. --}}
                <div wire:ignore class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <span class="rounded-lg bg-blue-50 p-2 text-blue-900">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M3 7h4V3m14 4h-4V3M3 17h4v4m14-4h-4v4M7 7h10v10H7z" />
                                </svg>
                            </span>

                            <div>
                                <p class="text-sm font-semibold text-slate-800">
                                    Escanear QR de la guía
                                </p>
                                <p id="qr-hint" class="text-xs text-slate-500">
                                    Usa la cámara en lugar de teclear el número.
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            id="qr-toggle"
                            class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100"
                        >
                            Activar cámara
                        </button>
                    </div>

                    <div
                        id="qr-reader"
                        class="mt-4 hidden overflow-hidden rounded-xl border border-slate-200 bg-white"
                        style="max-width: 380px;"
                    ></div>
                </div>

                @if ($package)
                    <div class="rounded-xl bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">
                            Guía localizada
                        </p>

                        <p class="mt-1 font-semibold text-slate-800">
                            {{ $package->tracking_number }}
                        </p>

                        <p class="mt-2 text-sm text-slate-600">
                            Estado: {{ $package->statusLabel() }}
                        </p>

                        <p class="mt-1 text-sm text-slate-600">
                            Destinatario: {{ $package->recipient_name }}
                        </p>
                    </div>
                @endif
            </form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="font-display text-lg font-semibold text-slate-900">
                Confirmar recepción
            </h3>

            <p class="mt-1 text-sm text-slate-500">
                La recepción queda registrada en el historial inmutable.
            </p>

            <form wire:submit.prevent="receive" class="mt-5 space-y-4">

                <div>
                    <label class="text-sm font-medium text-slate-600">
                        Almacén de recepción
                    </label>

                    <select
                        wire:model="warehouseId"
                        class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-900 focus:ring-blue-900"
                    >
                        <option value="">Selecciona...</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>

                    @error('warehouseId')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="w-full rounded-xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50"
                >
                    Registrar recepción
                </button>

                @if ($package)
                    <button
                        type="button"
                        wire:click="clear"
                        class="w-full rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50"
                    >
                        Limpiar
                    </button>
                @endif
            </form>
        </div>
    </div>

    @if ($package && $this->canReleaseFromHub())
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 shadow-sm">
            <h3 class="font-display text-lg font-semibold text-slate-900">
                Liberar paquete en HUB destino
            </h3>

            <p class="mt-1 text-sm text-slate-600">
                Esta guía ya está físicamente en su HUB destino final. Elige qué ocurre a continuación:
            </p>

            <button
                type="button"
                wire:click="releaseFromHub"
                wire:loading.attr="disabled"
                wire:confirm="¿Confirmas esta acción? Esta guía cambiará de estado."
                class="mt-4 w-full rounded-xl bg-amber-500 px-5 py-3 text-sm font-semibold text-white hover:bg-amber-600 disabled:opacity-50 sm:w-auto"
            >
                {{ $this->releaseActionLabel() }}
            </button>
        </div>
    @endif

    @if ($package && $package->histories->count())
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="font-display text-lg font-semibold text-slate-900">
                Historial de la guía
            </h3>

            <div class="mt-5 divide-y divide-slate-100">
                @foreach ($package->histories->sortByDesc('created_at') as $history)
                    <div class="py-4 first:pt-0">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="text-sm font-semibold text-slate-800">
                                {{ $history->eventTypeLabel() }}
                            </span>

                            <span class="text-xs text-slate-400">
                                {{ $history->created_at?->format('d/m/Y H:i') }}
                            </span>
                        </div>

                        <p class="mt-1 text-xs text-slate-500">
                            {{ $history->location_description }}
                        </p>

                        @if ($history->origin_location || $history->destination_location)
                            <p class="mt-1 text-xs text-slate-500">
                                {{ $history->origin_location ?: '—' }}
                                →
                                {{ $history->destination_location ?: '—' }}
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

@assets
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
@endassets

@script
<script>
    let scanner = null;
    let scanning = false;

    const toggle = document.getElementById('qr-toggle');
    const reader = document.getElementById('qr-reader');
    const hint = document.getElementById('qr-hint');

    function setHint(text) {
        if (hint) {
            hint.textContent = text;
        }
    }

    async function stopScanner() {
        scanning = false;

        if (scanner) {
            try {
                await scanner.stop();
                await scanner.clear();
            } catch (error) {
                console.warn('No se pudo detener el lector QR:', error);
            }
        }

        scanner = null;

        reader.classList.add('hidden');
        toggle.textContent = 'Activar cámara';
    }

    async function startScanner() {
        if (scanning || typeof Html5Qrcode === 'undefined') {
            setHint('El lector QR no está disponible en este navegador.');
            return;
        }

        reader.classList.remove('hidden');
        toggle.textContent = 'Detener cámara';
        setHint('Apunta la cámara al QR de la guía.');

        try {
            scanner = new Html5Qrcode('qr-reader');
            scanning = true;

            await scanner.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 250, height: 250 } },
                async (decodedText) => {
                    if (!decodedText || !scanning) {
                        return;
                    }

                    const code = decodedText.trim();

                    await stopScanner();

                    setHint('Guía escaneada: ' + code);

                    $wire.scanGuide(code);
                },
                () => {}
            );
        } catch (error) {
            console.error('Error iniciando la cámara:', error);
            scanner = null;
            scanning = false;
            reader.classList.add('hidden');
            toggle.textContent = 'Activar cámara';
            setHint('No se pudo acceder a la cámara. Revisa los permisos del navegador.');
        }
    }

    if (toggle && reader) {
        toggle.addEventListener('click', () => {
            scanning ? stopScanner() : startScanner();
        });
    }
</script>
@endscript
