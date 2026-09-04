<div class="space-y-6">

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

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="font-display text-2xl font-semibold text-[#0F172A]">
                Escanear guía
            </h2>
            <p class="text-sm text-slate-500">
                Registra la salida del paquete desde la agencia y su recolección por Venexpress.
            </p>
        </div>

        <a
            href="{{ route('repartidor.dashboard') }}"
            class="inline-flex items-center justify-center rounded-xl border border-[#E2E8F0] bg-white px-4 py-2 text-sm font-medium text-[#0F172A] transition hover:bg-slate-50"
        >
            ← Volver al dashboard
        </a>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
            <h3 class="font-display text-lg font-semibold text-[#0F172A]">
                Lector QR
            </h3>

            <p class="mt-1 text-sm text-slate-500">
                Escanea el QR de la guía. El sistema validará que la agencia pertenezca a tu ruta activa.
            </p>

            <div
                id="qr-reader"
                wire:ignore
                class="mt-5 overflow-hidden rounded-2xl border border-slate-200 bg-slate-50"
            ></div>

            <div class="my-5 flex items-center gap-3">
                <div class="h-px flex-1 bg-slate-200"></div>
                <span class="text-xs font-medium uppercase tracking-wide text-slate-400">
                    o introduce la guía
                </span>
                <div class="h-px flex-1 bg-slate-200"></div>
            </div>

            <form wire:submit.prevent="searchPackage">
                <label class="text-sm font-medium text-slate-600">
                    Número de guía
                </label>

                <div class="mt-2 flex gap-2">
                    <input
                        type="text"
                        wire:model="trackingNumber"
                        placeholder="Ej. VEN-20260902-000123"
                        autocomplete="off"
                        class="min-w-0 flex-1 rounded-xl border border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900"
                    >

                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="rounded-xl bg-blue-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-800 disabled:opacity-50"
                    >
                        <span wire:loading.remove>Escanear</span>
                        <span wire:loading>Procesando...</span>
                    </button>
                </div>
            </form>
        </div>

        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-6 shadow-sm">

            @if ($package)

                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                            Guía
                        </p>
                        <h3 class="mt-1 font-display text-xl font-semibold text-[#0F172A]">
                            {{ $package->tracking_number }}
                        </h3>
                    </div>

                    <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700">
                        {{ $package->statusLabel() }}
                    </span>
                </div>

                @if ($securityWarning)
                    <div class="mt-5 rounded-xl border border-red-200 bg-red-50 p-4">
                        <p class="text-sm font-semibold text-red-800">
                            ⚠ Alerta de integridad
                        </p>
                        <p class="mt-1 text-sm text-red-700">
                            {{ $securityMessage }}
                        </p>
                    </div>
                @endif

                <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-xs text-slate-400">Destinatario</p>
                        <p class="mt-1 text-sm font-semibold text-slate-700">
                            {{ $package->recipient_name }}
                        </p>
                        @if ($package->recipient_phone)
                            <p class="mt-1 text-xs text-slate-500">
                                {{ $package->recipient_phone }}
                            </p>
                        @endif
                    </div>

                    <div>
                        <p class="text-xs text-slate-400">Destino</p>
                        <p class="mt-1 text-sm font-semibold text-slate-700">
                            {{ $package->destination_city }}
                            @if ($package->destination_state)
                                · {{ $package->destination_state }}
                            @endif
                        </p>
                    </div>
                </div>

                <div class="mt-5 rounded-xl bg-slate-50 p-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                        Agencia de origen
                    </p>
                    <p class="mt-1 text-sm font-semibold text-slate-700">
                        {{ $package->ally?->business_name ?? 'Agencia no disponible' }}
                    </p>
                </div>

                @if ($package->requires_delivery)
                    <div class="mt-4 rounded-xl border border-blue-100 bg-blue-50 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-blue-700">
                            Entrega a domicilio
                        </p>
                        <p class="mt-1 text-sm font-semibold text-blue-900">
                            {{ $package->delivery_address ?: 'Dirección no especificada' }}
                        </p>
                        @if ($package->delivery_sector)
                            <p class="mt-1 text-xs text-blue-700">
                                Sector: {{ $package->delivery_sector }}
                            </p>
                        @endif
                    </div>
                @else
                    <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-sm font-medium text-slate-700">
                            Retiro en agencia destino
                        </p>
                    </div>
                @endif

                @if ($package->is_cod)
                    <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-amber-700">
                            Cobro contra entrega
                        </p>
                        <p class="mt-1 text-xl font-bold text-amber-900">
                            ${{ number_format((float) $package->cod_amount_usd, 2) }}
                        </p>
                    </div>
                @endif

                <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                    @if ($package->current_status === \App\Models\Package::STATUS_RECOLECTADO_VENEXPRESS)
                        <p class="text-sm font-semibold text-emerald-800">
                            ✓ Salida registrada
                        </p>
                        <p class="mt-1 text-xs text-emerald-700">
                            El paquete ya quedó bajo custodia de Venexpress.
                        </p>
                    @elseif ($package->current_status === \App\Models\Package::STATUS_RECIBIDO_AGENCIA)
                        <p class="text-sm font-semibold text-emerald-800">
                            Guía localizada
                        </p>
                        <p class="mt-1 text-xs text-emerald-700">
                            El siguiente escaneo intentará registrar la salida desde la agencia.
                        </p>
                    @else
                        <p class="text-sm font-semibold text-slate-700">
                            {{ $package->statusLabel() }}
                        </p>
                    @endif
                </div>

            @else

                <div class="flex min-h-[420px] flex-col items-center justify-center text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-50 text-blue-900">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M3 7h4V3m14 4h-4V3M3 17h4v4m14-4h-4v4M7 7h10v10H7z" />
                        </svg>
                    </div>

                    <h3 class="mt-5 font-display text-lg font-semibold text-[#0F172A]">
                        Ninguna guía seleccionada
                    </h3>

                    <p class="mt-2 max-w-sm text-sm text-slate-500">
                        Escanea el QR o introduce manualmente el número de guía.
                    </p>
                </div>

            @endif
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('livewire:init', () => {
                let scanner = null;
                let scanning = false;

                async function startQrScanner() {
                    const element = document.getElementById('qr-reader');

                    if (!element || scanner || scanning || typeof Html5Qrcode === 'undefined') {
                        return;
                    }

                    try {
                        scanner = new Html5Qrcode('qr-reader');
                        scanning = true;

                        await scanner.start(
                            { facingMode: 'environment' },
                            {
                                fps: 10,
                                qrbox: { width: 250, height: 250 }
                            },
                            async (decodedText) => {
                                if (!decodedText || !scanning) {
                                    return;
                                }

                                scanning = false;

                                try {
                                    await scanner.stop();
                                } catch (error) {
                                    console.warn('No se pudo detener el scanner:', error);
                                }

                                try {
                                    await scanner.clear();
                                } catch (error) {
                                    console.warn('No se pudo limpiar el scanner:', error);
                                }

                                scanner = null;

                                @this.scan(decodedText.trim());

                                setTimeout(startQrScanner, 700);
                            },
                            () => {}
                        );
                    } catch (error) {
                        console.error('Error iniciando la cámara:', error);
                        scanner = null;
                        scanning = false;
                    }
                }

                startQrScanner();

                Livewire.hook('morph.updated', () => {
                    setTimeout(() => {
                        if (!scanner && !scanning) {
                            startQrScanner();
                        }
                    }, 300);
                });
            });
        </script>
    @endpush
@endonce
