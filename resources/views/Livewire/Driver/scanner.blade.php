<div class="space-y-6">

    {{-- ENCABEZADO --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <h2 class="font-display text-2xl font-semibold text-[#0F172A]">
                Escanear guía
            </h2>

            <p class="text-sm text-slate-500">
                Escanea el código QR de una guía disponible.
            </p>
        </div>

        <a
            href="{{ route('repartidor.dashboard') }}"
            class="inline-flex items-center justify-center rounded-xl border border-[#E2E8F0] bg-white px-4 py-2 text-sm font-medium text-[#0F172A] transition hover:bg-slate-50"
        >
            ← Volver al dashboard
        </a>

    </div>


    {{-- CONTENEDOR --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">


        {{-- ESCÁNER --}}
        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-6 shadow-sm">

            <div class="mb-5">
                <h3 class="font-display text-lg font-semibold text-[#0F172A]">
                    Lector QR
                </h3>

                <p class="mt-1 text-sm text-slate-500">
                    Apunta la cámara al código QR de la guía.
                </p>
            </div>


            {{-- ÁREA DE CÁMARA --}}
            <div
    id="qr-reader"
    wire:ignore
    class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50"
></div>


            <div class="my-5 flex items-center gap-3">

                <div class="h-px flex-1 bg-slate-200"></div>

                <span class="text-xs font-medium uppercase tracking-wide text-slate-400">
                    o introduce la guía
                </span>

                <div class="h-px flex-1 bg-slate-200"></div>

            </div>


            {{-- BÚSQUEDA MANUAL --}}
            <form wire:submit.prevent="searchPackage">

                <label class="text-sm font-medium text-slate-600">
                    Número de guía
                </label>

                <div class="mt-2 flex gap-2">

                    <input
                        type="text"
                        wire:model="trackingNumber"
                        placeholder="Ej. VEN-20260826-000123"
                        autocomplete="off"
                        class="min-w-0 flex-1 rounded-xl border border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900"
                    >

                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="rounded-xl bg-blue-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-800 disabled:opacity-50"
                    >
                        <span wire:loading.remove>
                            Buscar
                        </span>

                        <span wire:loading>
                            Buscando...
                        </span>
                    </button>

                </div>

            </form>


            {{-- ERROR --}}
            @if ($errorMessage)

                <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3">

                    <div class="flex gap-3">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600">
                            !
                        </div>

                        <div>
                            <p class="text-sm font-medium text-red-700">
                                No se pudo encontrar la guía
                            </p>

                            <p class="mt-1 text-sm text-red-600">
                                {{ $errorMessage }}
                            </p>
                        </div>

                    </div>

                </div>

            @endif

        </div>


        {{-- INFORMACIÓN DEL PAQUETE --}}
        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-6 shadow-sm">

            @if ($package)

                <div class="flex items-start justify-between gap-4">

                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                            Guía encontrada
                        </p>

                        <h3 class="mt-1 font-display text-2xl font-semibold text-[#0F172A]">
                            {{ $package->tracking_number }}
                        </h3>
                    </div>

                    <button
                        type="button"
                        wire:click="clearSearch"
                        class="rounded-xl border border-[#E2E8F0] px-3 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50"
                    >
                        Limpiar
                    </button>

                </div>


                {{-- ESTADO --}}
                <div class="mt-5 rounded-2xl bg-amber-50 px-4 py-4">

                    <p class="text-xs font-medium uppercase tracking-wide text-amber-600">
                        Estado actual
                    </p>

                    <p class="mt-1 text-sm font-semibold text-amber-800">
                        {{ $package->statusLabel() }}
                    </p>

                </div>


                {{-- DESTINATARIO --}}
                <div class="mt-6">

                    <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                        Destinatario
                    </h4>

                    <div class="mt-3">

                        <p class="text-base font-semibold text-[#0F172A]">
                            {{ $package->recipient_name }}
                        </p>

                        @if ($package->recipient_id_doc)
                            <p class="mt-1 text-sm text-slate-500">
                                C.I./RIF: {{ $package->recipient_id_doc }}
                            </p>
                        @endif

                        @if ($package->recipient_phone)
                            <p class="mt-1 text-sm text-slate-500">
                                Teléfono: {{ $package->recipient_phone }}
                            </p>
                        @endif

                    </div>

                </div>


                {{-- DESTINO --}}
                <div class="mt-6">

                    <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                        Destino
                    </h4>

                    <p class="mt-2 text-sm font-medium text-[#0F172A]">
                        {{ $package->destination_city ?? '—' }}
                        @if ($package->destination_state)
                            , {{ $package->destination_state }}
                        @endif
                    </p>

                </div>


                {{-- DELIVERY --}}
                @if ($package->requires_delivery)

                    <div class="mt-6 rounded-2xl bg-slate-50 p-4">

                        <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Entrega a domicilio
                        </h4>

                        @if ($package->delivery_address)
                            <p class="mt-2 text-sm font-medium text-[#0F172A]">
                                {{ $package->delivery_address }}
                            </p>
                        @endif

                        @if ($package->delivery_sector)
                            <p class="mt-1 text-sm text-slate-500">
                                Sector: {{ $package->delivery_sector }}
                            </p>
                        @endif

                        @if ($package->delivery_reference)
                            <p class="mt-1 text-sm text-slate-500">
                                Referencia: {{ $package->delivery_reference }}
                            </p>
                        @endif

                    </div>

                @else

                    <div class="mt-6 rounded-2xl bg-blue-50 p-4">

                        <p class="text-sm font-medium text-blue-900">
                            Retiro en agencia destino
                        </p>

                        <p class="mt-1 text-xs text-blue-700">
                            Este paquete no requiere delivery.
                        </p>

                    </div>

                @endif


                {{-- INFORMACIÓN DEL PAQUETE --}}
                <div class="mt-6 grid grid-cols-2 gap-3">

                    <div class="rounded-xl border border-slate-200 p-3">

                        <p class="text-xs text-slate-400">
                            Tipo
                        </p>

                        <p class="mt-1 text-sm font-semibold text-[#0F172A]">
                            {{ ucfirst($package->package_type) }}
                        </p>

                    </div>

                    <div class="rounded-xl border border-slate-200 p-3">

                        <p class="text-xs text-slate-400">
                            Peso
                        </p>

                        <p class="mt-1 text-sm font-semibold text-[#0F172A]">
                            {{ $package->billable_weight_kg ?? $package->physical_weight_kg }} kg
                        </p>

                    </div>

                </div>


                {{-- COD --}}
                @if ($package->is_cod)

                    <div class="mt-4 rounded-xl border border-orange-200 bg-orange-50 p-4">

                        <p class="text-xs font-semibold uppercase tracking-wide text-orange-600">
                            Cobro contra entrega
                        </p>

                        <p class="mt-1 text-xl font-bold text-orange-800">
                            ${{ number_format($package->cod_amount_usd ?? 0, 2) }}
                        </p>

                        <p class="mt-1 text-xs text-orange-600">
                            Monto que debe cobrarse al destinatario.
                        </p>

                    </div>

                @endif


                {{-- ACCIÓN --}}
                <div class="mt-6">

                    <button
                        type="button"
                        class="w-full rounded-xl bg-blue-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-800"
                    >
                        Iniciar entrega
                    </button>

                </div>


            @else

                {{-- ESTADO VACÍO --}}
                <div class="flex min-h-[420px] flex-col items-center justify-center text-center">

                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-50">

                        <svg
                            class="h-8 w-8 text-blue-900"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M3 7h4V3m14 4h-4V3M3 17h4v4m14-4h-4v4M7 7h10v10H7z"
                            />
                        </svg>

                    </div>

                    <h3 class="mt-5 font-display text-lg font-semibold text-[#0F172A]">
                        Ninguna guía seleccionada
                    </h3>

                    <p class="mt-2 max-w-sm text-sm text-slate-500">
                        Escanea el código QR de una guía o introduce manualmente
                        su número para consultar la entrega.
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

                    if (!element) {
                        return;
                    }

                    if (scanner || scanning) {
                        return;
                    }

                    try {

                        scanner = new Html5Qrcode('qr-reader');
                        scanning = true;

                        await scanner.start(
                            {
                                facingMode: 'environment'
                            },
                            {
                                fps: 10,
                                qrbox: {
                                    width: 250,
                                    height: 250
                                }
                            },
                            async (decodedText) => {

                                if (!decodedText) {
                                    return;
                                }

                                /*
                                 * Evitamos múltiples lecturas
                                 * del mismo código.
                                 */
                                if (!scanning) {
                                    return;
                                }

                                scanning = false;

                                const trackingNumber = decodedText.trim();

                                /*
                                 * Detenemos temporalmente la cámara.
                                 */
                                try {
                                    await scanner.stop();
                                } catch (error) {
                                    console.warn(
                                        'No se pudo detener el scanner:',
                                        error
                                    );
                                }

                                /*
                                 * Limpiamos el lector.
                                 */
                                try {
                                    await scanner.clear();
                                } catch (error) {
                                    console.warn(
                                        'No se pudo limpiar el scanner:',
                                        error
                                    );
                                }

                                scanner = null;

                                /*
                                 * Enviamos el número a Livewire.
                                 */
                                @this.scan(trackingNumber);

                                /*
                                 * Esperamos a que Livewire termine
                                 * de actualizar el componente.
                                 */
                                setTimeout(() => {
                                    startQrScanner();
                                }, 500);

                            },
                            (errorMessage) => {
                                /*
                                 * Los errores normales de lectura
                                 * se ignoran.
                                 */
                            }
                        );

                    } catch (error) {

                        console.error(
                            'Error iniciando la cámara:',
                            error
                        );

                        scanner = null;
                        scanning = false;

                        element.innerHTML = `
                            <div class="flex min-h-[300px] items-center justify-center p-6 text-center">
                                <div>
                                    <p class="text-sm font-medium text-slate-600">
                                        No se pudo acceder a la cámara.
                                    </p>

                                    <p class="mt-1 text-xs text-slate-400">
                                        Verifica los permisos de cámara o introduce
                                        el número de guía manualmente.
                                    </p>
                                </div>
                            </div>
                        `;
                    }
                }


                /*
                 * Inicializar cámara.
                 */
                startQrScanner();


                /*
                 * Livewire actualizó el DOM.
                 *
                 * Gracias a wire:ignore, el contenido interno
                 * del lector no será destruido.
                 */
                Livewire.hook('morph.updated', () => {

                    setTimeout(() => {

                        const element =
                            document.getElementById('qr-reader');

                        if (
                            element &&
                            !scanner &&
                            !scanning
                        ) {
                            startQrScanner();
                        }

                    }, 300);

                });

            });
        </script>

    @endpush

@endonce