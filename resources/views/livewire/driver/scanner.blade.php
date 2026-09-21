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
            @if ($operationTitle)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-900 px-3 py-1 text-xs font-bold uppercase tracking-wide text-white">
                    {{ $operationTitle }}
                </span>
            @endif

            <h2 class="mt-2 font-display text-2xl font-semibold text-[#0F172A]">
                Escanear guía
            </h2>
            <p class="text-sm text-slate-500">
                @if ($operationInstructions)
                    {{ $operationInstructions }}
                @elseif ($isDistribution)
                    Registra la salida del HUB y la llegada al almacén destino de Venexpress.
                @else
                    Registra la salida del paquete desde la agencia y su recolección por Venexpress.
                @endif
            </p>
        </div>

        <a
            href="{{ route('repartidor.dashboard') }}"
            class="text-sm font-medium text-blue-700 hover:text-blue-900"
        >
            ← Resumen
        </a>
    </div>

    @if ($activeRoute && $operationTitle)
        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-5">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        Ruta
                    </p>
                    <p class="mt-1 text-sm font-semibold text-[#0F172A]">
                        {{ $activeRoute->name }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        @if ($operation === 'collection')
                            Aliado
                        @else
                            Almacén destino
                        @endif
                    </p>
                    <p class="mt-1 text-sm font-semibold text-[#0F172A]">
                        {{ $contextStop?->ally?->business_name ?? $contextStop?->warehouse?->name ?? '—' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        Ciudad
                    </p>
                    <p class="mt-1 text-sm font-semibold text-[#0F172A]">
                        {{ $contextStop?->ally?->city ?? $contextStop?->warehouse?->city ?? $activeRoute->city }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        Pendientes
                    </p>
                    <p class="mt-1 text-sm font-semibold text-[#0F172A]">
                        {{ $pendingCount ?? '—' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        Procesados
                    </p>
                    <p class="mt-1 text-sm font-semibold text-[#0F172A]">
                        {{ $processedCount }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
            <h3 class="font-display text-lg font-semibold text-[#0F172A]">
                Lector QR
            </h3>

            <p class="mt-1 text-sm text-slate-500">
                @if ($operation === 'hub_departure')
                    Escanea el QR de la guía. El sistema validará que el paquete esté en HUB para tu ruta de distribución.
                @elseif ($operation === 'hub_arrival')
                    Escanea el QR de la guía. El sistema validará que el paquete esté en tránsito nacional bajo tu custodia.
                @elseif ($isDistribution)
                    Escanea el QR de la guía. El sistema validará que el paquete esté en HUB o en tránsito para tu ruta de distribución.
                @else
                    Escanea el QR de la guía. El sistema validará que la agencia pertenezca a tu ruta activa.
                @endif
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

                @unless ($operation)
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
                @endunless

                @if ($pendingOperationView && $pendingOperationView['eligible'])
                    <div class="mt-5 rounded-xl border border-blue-200 bg-blue-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">
                            Ya escaneaste esta guía hace un momento
                        </p>
                        <p class="mt-1 text-sm font-semibold text-blue-900">
                            {{ $pendingOperationView['label'] }}
                        </p>
                        <p class="mt-1 text-xs text-blue-700">
                            {{ $pendingOperationView['hint'] }} Confirma solo si de verdad quieres repetir la operación sobre esta guía.
                        </p>

                        <button
                            type="button"
                            wire:click="confirmOperation('{{ $pendingOperationView['key'] }}')"
                            wire:loading.attr="disabled"
                            class="mt-4 inline-flex w-full items-center justify-center rounded-xl bg-blue-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-800 disabled:opacity-50 sm:w-auto"
                        >
                            <span wire:loading.remove wire:target="confirmOperation">{{ $pendingOperationView['cta'] }}</span>
                            <span wire:loading wire:target="confirmOperation">Procesando...</span>
                        </button>
                    </div>
                @else
                <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                    @if ($lastAction === 'collection')
                        <p class="text-sm font-semibold text-emerald-800">
                            ✓ Paquete procesado
                        </p>
                        <p class="mt-1 font-mono text-xs text-emerald-700">
                            {{ $package->tracking_number }}
                        </p>
                        <p class="mt-2 text-sm font-semibold text-emerald-800">
                            Salida registrada desde la agencia
                        </p>
                        <p class="mt-1 text-xs text-emerald-700">
                            El paquete quedó bajo custodia de Venexpress.
                        </p>

                        @if ($operationTotal)
                            <p class="mt-3 text-sm font-semibold text-emerald-800">
                                {{ $operationProcessedCount }} de {{ $operationTotal }} paquetes procesados
                            </p>
                            <p class="mt-1 text-xs text-emerald-700">
                                @if ($pendingCount > 0)
                                    Continúa escaneando las guías restantes de este aliado.
                                @else
                                    Operación completada. No quedan más guías pendientes en este aliado.
                                @endif
                            </p>
                        @endif
                    @elseif ($lastAction === 'hub_departure')
                        <p class="text-sm font-semibold text-emerald-800">
                            ✓ Paquete procesado
                        </p>
                        <p class="mt-1 font-mono text-xs text-emerald-700">
                            {{ $package->tracking_number }}
                        </p>
                        <p class="mt-2 text-sm font-semibold text-emerald-800">
                            Salida del HUB registrada
                        </p>
                        <p class="mt-1 text-xs text-emerald-700">
                            Destino: {{ $package->destination_city }}
                            @if ($package->destination_state)
                                · {{ $package->destination_state }}
                            @endif
                        </p>

                        @if ($operationTotal)
                            <p class="mt-3 text-sm font-semibold text-emerald-800">
                                {{ $operationProcessedCount }} de {{ $operationTotal }} paquetes procesados
                            </p>
                            <p class="mt-1 text-xs text-emerald-700">
                                @if ($pendingCount > 0)
                                    Continúa escaneando las guías restantes.
                                @else
                                    Operación completada. Cuando salgas hacia el destino, continúa con la recepción en almacén.
                                @endif
                            </p>
                        @endif
                    @elseif ($lastAction === 'hub_arrival')
                        <p class="text-sm font-semibold text-emerald-800">
                            ✓ Paquete procesado
                        </p>
                        <p class="mt-1 font-mono text-xs text-emerald-700">
                            {{ $package->tracking_number }}
                        </p>
                        <p class="mt-2 text-sm font-semibold text-emerald-800">
                            Recepción en almacén registrada
                        </p>
                        <p class="mt-1 text-xs text-emerald-700">
                            Almacén: {{ $arrivalWarehouse?->name ?? 'Almacén destino' }}
                        </p>

                        @if ($operationTotal)
                            <p class="mt-3 text-sm font-semibold text-emerald-800">
                                {{ $operationProcessedCount }} de {{ $operationTotal }} paquetes procesados
                            </p>
                            <p class="mt-1 text-xs text-emerald-700">
                                @if ($pendingCount > 0)
                                    Continúa escaneando las guías restantes.
                                @else
                                    Operación completada. No quedan más guías pendientes de recepción.
                                @endif
                            </p>
                        @endif
                    @elseif ($lastAction === 'hub_reception')
                        <p class="text-sm font-semibold text-emerald-800">
                            ✓ Paquete procesado
                        </p>
                        <p class="mt-1 font-mono text-xs text-emerald-700">
                            {{ $package->tracking_number }}
                        </p>
                        <p class="mt-2 text-sm font-semibold text-emerald-800">
                            Recepción en HUB registrada
                        </p>
                        <p class="mt-1 text-xs text-emerald-700">
                            El paquete quedó EN_HUB.
                        </p>

                        @if ($operationTotal)
                            <p class="mt-3 text-sm font-semibold text-emerald-800">
                                {{ $operationProcessedCount }} de {{ $operationTotal }} paquetes procesados
                            </p>
                            <p class="mt-1 text-xs text-emerald-700">
                                @if ($pendingCount > 0)
                                    Continúa escaneando las guías restantes.
                                @else
                                    Operación completada. No quedan más guías pendientes de recepción.
                                @endif
                            </p>
                        @endif
                    @else
                        <p class="text-sm font-semibold text-slate-700">
                            {{ $package->statusLabel() }}
                        </p>
                    @endif
                </div>
                @endif

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
            let venexpressScannerState = null;
            let venexpressScannerRunning = false;

            async function startQrScanner() {
                const element = document.getElementById('qr-reader');

                if (!element || venexpressScannerState || venexpressScannerRunning || typeof Html5Qrcode === 'undefined') {
                    return;
                }

                try {
                    venexpressScannerState = new Html5Qrcode('qr-reader');
                    venexpressScannerRunning = true;

                    await venexpressScannerState.start(
                        { facingMode: 'environment' },
                        {
                            fps: 10,
                            qrbox: { width: 250, height: 250 }
                        },
                        async (decodedText) => {
                            if (!decodedText || !venexpressScannerRunning) {
                                return;
                            }

                            await stopQrScanner();

                            $wire.scan(decodedText.trim());

                            setTimeout(startQrScanner, 700);
                        },
                        () => {}
                    );
                } catch (error) {
                    console.error('Error iniciando la cámara:', error);
                    venexpressScannerState = null;
                    venexpressScannerRunning = false;
                }
            }

            async function stopQrScanner() {
                if (!venexpressScannerState) {
                    venexpressScannerRunning = false;

                    return;
                }

                const instance = venexpressScannerState;
                venexpressScannerState = null;
                venexpressScannerRunning = false;

                try {
                    await instance.stop();
                } catch (error) {
                    console.warn('No se pudo detener el scanner:', error);
                }

                try {
                    await instance.clear();
                } catch (error) {
                    console.warn('No se pudo limpiar el scanner:', error);
                }
            }

            document.addEventListener('livewire:init', () => {
                Livewire.hook('morph.updated', () => {
                    setTimeout(() => {
                        if (!venexpressScannerState && !venexpressScannerRunning) {
                            startQrScanner();
                        }
                    }, 300);
                });
            });

            // livewire:navigated dispara tanto en la primera carga de la
            // página como en cada navegación posterior vía wire:navigate.
            // livewire:init, en cambio, solo dispara una vez por sesión
            // SPA — por eso la cámara dejaba de arrancar al volver a esta
            // pantalla sin refrescar el navegador.
            document.addEventListener('livewire:navigated', () => {
                startQrScanner();
            });

            // Libera la cámara al salir de esta pantalla vía wire:navigate,
            // para no dejar el stream de video corriendo en segundo plano.
            document.addEventListener('livewire:navigate', () => {
                stopQrScanner();
            });
        </script>
    @endpush
@endonce
