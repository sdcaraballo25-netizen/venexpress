<div class="space-y-6">

    <div>
        <h1 class="font-display text-2xl font-bold text-slate-900">
            Retiro en agencia
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Verifica la guía y el documento del destinatario antes de entregar.
        </p>
    </div>

    @if($message)
        <div class="rounded-xl bg-emerald-50 p-4 text-sm text-emerald-700">
            {{ $message }}
        </div>
    @endif

    @if($error)
        <div class="rounded-xl bg-red-50 p-4 text-sm text-red-700">
            {{ $error }}
        </div>
    @endif

    <div class="rounded-2xl border bg-white p-6 shadow-sm">

        <form wire:submit.prevent="search" class="space-y-4">

            <div class="flex flex-col gap-3 sm:flex-row">

                <input
                    id="pickup-tracking-number"
                    wire:model="trackingNumber"
                    class="flex-1 rounded-xl border px-4 py-3 outline-none transition focus:border-blue-800 focus:ring-2 focus:ring-blue-100"
                    placeholder="Número de guía"
                    autocomplete="off"
                >

                <button
                    type="submit"
                    class="rounded-xl bg-blue-900 px-5 py-3 font-semibold text-white"
                >
                    Buscar
                </button>

            </div>


            <!-- OPCIONES DE FOTO Y CÁMARA -->
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">

                <!-- Cámara -->
                <label
                    for="pickup-camera"
                    class="flex cursor-pointer items-center justify-center gap-3 rounded-xl border-2 border-dashed border-blue-200 bg-blue-50 px-4 py-4 text-sm font-semibold text-blue-900 transition hover:border-blue-400 hover:bg-blue-100"
                >

                    <span class="text-2xl">📷</span>

                    <span>
                        Usar cámara
                        <small class="block font-normal text-blue-700">
                            Fotografiar la guía
                        </small>
                    </span>

                </label>

                <input
                    id="pickup-camera"
                    type="file"
                    accept="image/*"
                    capture="environment"
                    class="hidden"
                >


                <!-- Galería -->
                <label
                    for="pickup-photo"
                    class="flex cursor-pointer items-center justify-center gap-3 rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 px-4 py-4 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-100"
                >

                    <span class="text-2xl">🖼️</span>

                    <span>
                        Subir foto
                        <small class="block font-normal text-slate-500">
                            Elegir una imagen
                        </small>
                    </span>

                </label>

                <input
                    id="pickup-photo"
                    type="file"
                    accept="image/*"
                    class="hidden"
                >

            </div>


            <!-- ESTADO OCR -->
            <div
                id="pickup-ocr-status"
                class="hidden rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800"
            ></div>


            <!-- PREVISUALIZACIÓN -->
            <div id="pickup-preview-container" class="hidden">

                <div class="overflow-hidden rounded-xl border border-slate-200 bg-slate-50">

                    <img
                        id="pickup-preview"
                        src=""
                        alt="Vista previa de la guía"
                        class="max-h-64 w-full object-contain"
                    >

                </div>

            </div>

        </form>


        @if($package)

            <div class="mt-5 rounded-xl bg-slate-50 p-5">

                <div class="font-tracking font-semibold">
                    {{ $package->tracking_number }}
                </div>

                <div class="mt-1 text-sm">
                    {{ $package->recipient_name }} · {{ $package->destination_city }}
                </div>

                <div class="mt-2 text-xs text-slate-500">
                    COD:
                    {{ $package->is_cod ? '$'.number_format((float)$package->cod_amount_usd,2) : 'No' }}
                </div>


                <form
                    wire:submit.prevent="deliver"
                    class="mt-5 space-y-3"
                >

                    <input
                        wire:model="recipientIdDoc"
                        class="w-full rounded-xl border px-4 py-3"
                        placeholder="Documento del destinatario"
                    >

                    <button
                        wire:loading.attr="disabled"
                        wire:confirm="¿Confirmas la identidad y entrega del paquete?"
                        class="w-full rounded-xl bg-emerald-600 px-5 py-3 font-semibold text-white"
                    >
                        Confirmar retiro
                    </button>

                </form>

            </div>

        @endif

    </div>

</div>


{{-- OCR PARA LEER EL NÚMERO DE GUÍA --}}
@once

    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>

    <script>

        document.addEventListener('DOMContentLoaded', function () {

            const cameraInput =
                document.getElementById('pickup-camera');

            const photoInput =
                document.getElementById('pickup-photo');

            const trackingInput =
                document.getElementById('pickup-tracking-number');

            const statusBox =
                document.getElementById('pickup-ocr-status');

            const previewContainer =
                document.getElementById('pickup-preview-container');

            const preview =
                document.getElementById('pickup-preview');


            if (!cameraInput || !photoInput || !trackingInput) {
                return;
            }


            function mostrarEstado(mensaje) {

                statusBox.classList.remove('hidden');

                statusBox.textContent = mensaje;
            }


            async function procesarImagen(file) {

                if (!file) {
                    return;
                }


                preview.src = URL.createObjectURL(file);

                previewContainer.classList.remove('hidden');


                mostrarEstado(
                    '🔎 Analizando la imagen y buscando el número de guía...'
                );


                try {

                    const resultado = await Tesseract.recognize(

                        file,

                        'eng',

                        {

                            logger: function (info) {

                                if (
                                    info.status ===
                                    'recognizing text'
                                ) {

                                    const porcentaje =
                                        Math.round(
                                            (info.progress || 0) * 100
                                        );

                                    mostrarEstado(
                                        '🔎 Reconociendo guía... ' +
                                        porcentaje +
                                        '%'
                                    );
                                }

                            }

                        }

                    );


                    const texto =
                        resultado.data.text || '';


                    console.log(
                        'Texto OCR retiro:',
                        texto
                    );


                    const guia =
                        extraerGuia(texto);


                    if (guia) {

                        trackingInput.value = guia;


                        trackingInput.dispatchEvent(
                            new Event(
                                'input',
                                { bubbles: true }
                            )
                        );


                        trackingInput.dispatchEvent(
                            new Event(
                                'change',
                                { bubbles: true }
                            )
                        );


                        mostrarEstado(
                            '✅ Guía detectada: ' +
                            guia +
                            '. Ahora presiona "Buscar".'
                        );


                    } else {

                        mostrarEstado(
                            '⚠️ No pude identificar la guía. ' +
                            'Toma una foto más clara o escríbela manualmente.'
                        );

                    }


                } catch (error) {

                    console.error(error);

                    mostrarEstado(
                        '❌ No se pudo leer la imagen. ' +
                        'Puedes escribir la guía manualmente.'
                    );

                }

            }


            function extraerGuia(texto) {

                let limpio = texto
                    .toUpperCase()
                    .replace(/\n/g, ' ')
                    .replace(/\s+/g, ' ');


                const patrones = [

                    /\bVEN[-\s]?\d{4}[-\s]?\d{5,10}\b/i,

                    /\bVE[-\s]?\d{4}[-\s]?\d{5,10}\b/i,

                    /\bVEN\d{8,16}\b/i,

                    /\bVE\d{8,16}\b/i

                ];


                for (const patron of patrones) {

                    const encontrado =
                        limpio.match(patron);


                    if (encontrado) {

                        return normalizarGuia(
                            encontrado[0]
                        );

                    }

                }


                /*
                 * Tolerancia para errores del OCR.
                 */

                const tolerante =
                    limpio.match(
                        /\bV[A-Z][A-Z]?[-\s]?\d{4}[-\s]?\d{5,10}\b/i
                    );


                if (tolerante) {

                    let guia =
                        tolerante[0]
                            .toUpperCase()
                            .replace(/\s+/g, '-');


                    guia =
                        guia.replace(
                            /^VK/,
                            'VE'
                        );


                    guia =
                        guia.replace(
                            /^VFN/,
                            'VEN'
                        );


                    return normalizarGuia(guia);

                }


                return null;

            }


            function normalizarGuia(guia) {

                return guia
                    .toUpperCase()
                    .replace(/\s+/g, '-')
                    .replace(/--+/g, '-');

            }


            cameraInput.addEventListener(
                'change',
                function () {

                    procesarImagen(
                        this.files[0]
                    );

                    this.value = '';

                }
            );


            photoInput.addEventListener(
                'change',
                function () {

                    procesarImagen(
                        this.files[0]
                    );

                    this.value = '';

                }
            );

        });

    </script>

@endonce
