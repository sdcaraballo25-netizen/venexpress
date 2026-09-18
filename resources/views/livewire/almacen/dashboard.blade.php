@php
    use App\Models\Package;
@endphp

<div class="space-y-6">

    {{-- =========================================================
         HEADER
    ========================================================== --}}
    <div>
        <p class="text-sm font-medium text-purple-700">
            Panel de almacén
        </p>

        <h1 class="mt-1 font-display text-2xl font-bold text-[#0F172A]">
            {{ $warehouse?->name ?? 'Almacén' }}
        </h1>

        <p class="mt-1 text-sm text-[#64748B]">
            @if ($warehouse)
                {{ $warehouse->city }}, {{ $warehouse->state }}
            @else
                Tu usuario todavía no tiene un almacén asignado. Contacta a un administrador.
            @endif
        </p>
    </div>

    @if ($warehouse)

        {{-- =========================================================
             STATS
        ========================================================== --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

            <div class="bg-white rounded-2xl border border-[#E2E8F0] p-6 shadow-sm">
                <div class="flex justify-between items-start">
                    <p class="text-xs font-bold text-[#64748B] uppercase tracking-wider">Por llegar</p>
                    <div class="p-2 bg-amber-50 rounded-lg text-amber-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="font-display text-3xl font-bold mt-4 text-[#0F172A]">{{ $pendingStops->count() }}</p>
                <p class="text-xs text-[#64748B] mt-2">Paradas todavía no confirmadas</p>
            </div>

            <div class="bg-white rounded-2xl border border-[#E2E8F0] p-6 shadow-sm">
                <div class="flex justify-between items-start">
                    <p class="text-xs font-bold text-[#64748B] uppercase tracking-wider">Recibidas</p>
                    <div class="p-2 bg-emerald-50 rounded-lg text-emerald-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>
                <p class="font-display text-3xl font-bold mt-4 text-[#0F172A]">{{ $visitedStops->count() }}</p>
                <p class="text-xs text-[#64748B] mt-2">Paradas confirmadas en tu almacén</p>
            </div>

        </div>

        {{-- =========================================================
             LECTOR QR
        ========================================================== --}}
        <div id="escanear" class="bg-white border border-[#E2E8F0] rounded-2xl shadow-sm p-6 scroll-mt-24">
            <div class="flex items-center gap-3 mb-1">
                <div class="p-2 bg-purple-50 rounded-lg text-purple-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7h4V3m14 4h-4V3M3 17h4v4m14-4h-4v4M7 7h10v10H7z" />
                    </svg>
                </div>
                <h2 class="font-semibold text-[#0F172A]">Lector QR</h2>
            </div>
            <p class="text-xs text-[#64748B] mb-4">
                Escanea el QR de la guía con la cámara. El sistema detecta automáticamente si es una llegada o un despacho pendiente.
            </p>

            <div
                id="qr-reader"
                wire:ignore
                class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50"
                style="max-width: 420px;"
            ></div>
        </div>

        {{-- =========================================================
             ESCANEAR LLEGADA
        ========================================================== --}}
        <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-sm p-6">
            <div class="flex items-center gap-3 mb-1">
                <div class="p-2 bg-purple-50 rounded-lg text-purple-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7V5a2 2 0 012-2h2M17 3h2a2 2 0 012 2v2M21 17v2a2 2 0 01-2 2h-2M7 21H5a2 2 0 01-2-2v-2M7 12h10M12 7v10" />
                    </svg>
                </div>
                <h2 class="font-semibold text-[#0F172A]">Escanear llegada de paquete</h2>
            </div>
            <p class="text-xs text-[#64748B] mb-4">
                O introduce manualmente el número de guía del paquete que acaba de llegar al almacén.
            </p>

            <form wire:submit="scanArrival" class="flex flex-col sm:flex-row gap-3">
                <input
                    type="text"
                    wire:model="trackingNumber"
                    placeholder="Ej. VEN-2026-000123"
                    autofocus
                    class="flex-1 rounded-xl border border-[#E2E8F0] px-4 py-3 text-sm focus:border-purple-900 focus:ring-purple-900"
                >
                <button
                    type="submit"
                    class="rounded-xl bg-purple-900 hover:bg-purple-800 text-white font-semibold text-sm px-6 py-3 transition"
                >
                    Registrar llegada
                </button>
            </form>

            @if ($scanSuccess)
                <p class="mt-3 text-sm text-emerald-700 font-medium">✓ {{ $scanSuccess }}</p>
            @endif

            @if ($scanError)
                <p class="mt-3 text-sm text-red-600 font-medium">{{ $scanError }}</p>
            @endif
        </div>

        {{-- =========================================================
             DESPACHAR PAQUETE (a cliente o a repartidor)
        ========================================================== --}}
        <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-sm p-6">
            <div class="flex items-center gap-3 mb-1">
                <div class="p-2 bg-blue-50 rounded-lg text-blue-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4m0 0l6-6m-6 6l6 6" />
                    </svg>
                </div>
                <h2 class="font-semibold text-[#0F172A]">Despachar paquete</h2>
            </div>
            <p class="text-xs text-[#64748B] mb-4">
                Entrega un paquete ya recibido en tu almacén a quien lo retira en persona, o asígnalo a un repartidor.
            </p>

            <form wire:submit="searchDispatch" class="flex flex-col sm:flex-row gap-3">
                <input
                    type="text"
                    wire:model="dispatchTrackingNumber"
                    placeholder="Ej. VEN-2026-000123"
                    class="flex-1 rounded-xl border border-[#E2E8F0] px-4 py-3 text-sm focus:border-blue-900 focus:ring-blue-900"
                >
                <button
                    type="submit"
                    class="rounded-xl bg-blue-900 hover:bg-blue-800 text-white font-semibold text-sm px-6 py-3 transition"
                >
                    Buscar guía
                </button>
            </form>

            @if ($dispatchError)
                <p class="mt-3 text-sm text-red-600 font-medium">{{ $dispatchError }}</p>
            @endif

            @if ($dispatchSuccess)
                <p class="mt-3 text-sm text-emerald-700 font-medium">✓ {{ $dispatchSuccess }}</p>
            @endif

            @if ($dispatchPackage && $dispatchPackage->current_status === Package::STATUS_LISTO_RETIRO)

                <div class="mt-5 pt-5 border-t border-[#E2E8F0]">

                    <p class="text-sm text-[#0F172A]">
                        <span class="font-semibold">{{ $dispatchPackage->recipient_name }}</span>
                        · {{ $dispatchPackage->destination_city }}, {{ $dispatchPackage->destination_state }}
                    </p>

                    @if ($dispatchPackage->requires_delivery)

                        {{-- ASIGNAR A REPARTIDOR --}}
                        <p class="text-xs text-[#64748B] mt-1 mb-3">
                            Este envío requiere entrega a domicilio. Asígnalo a un repartidor con una ruta de reparto en curso hacia esta ciudad.
                        </p>

                        @forelse ($this->availableDeliveryRoutes as $route)
                            <div class="flex items-center justify-between gap-3 rounded-xl border border-[#E2E8F0] px-4 py-3 mb-2">
                                <div>
                                    <p class="text-sm font-medium text-[#0F172A]">{{ $route->driver?->user?->name ?? 'Repartidor' }}</p>
                                    <p class="text-xs text-[#64748B]">{{ $route->name }} · {{ $route->city }}</p>
                                </div>
                                <button
                                    wire:click="assignToDriver({{ $route->id }})"
                                    wire:confirm="¿Asignar este paquete a este repartidor?"
                                    class="px-3 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 text-xs font-semibold transition shrink-0"
                                >
                                    Asignar
                                </button>
                            </div>
                        @empty
                            <p class="text-xs text-[#94A3B8]">No hay repartidores con una ruta en curso hacia {{ $dispatchPackage->destination_city }} en este momento.</p>
                        @endforelse

                    @else

                        {{-- ENTREGAR A CLIENTE --}}
                        <p class="text-xs text-[#64748B] mt-1 mb-3">
                            Este envío se retira en persona. Verifica el documento de quien lo retira antes de entregarlo.
                        </p>

                        <form wire:submit="deliverToClient" class="flex flex-col sm:flex-row gap-3">
                            <input
                                type="text"
                                wire:model="recipientIdDoc"
                                placeholder="Cédula de quien retira"
                                class="flex-1 rounded-xl border border-[#E2E8F0] px-4 py-3 text-sm focus:border-emerald-700 focus:ring-emerald-700"
                            >
                            <button
                                type="submit"
                                class="rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-sm px-6 py-3 transition"
                            >
                                Confirmar entrega
                            </button>
                        </form>

                        @error('recipientIdDoc')
                            <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                        @enderror

                    @endif

                </div>

            @endif
        </div>

        {{-- =========================================================
             PENDIENTES
        ========================================================== --}}
        <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-[#E2E8F0]">
                <h2 class="font-semibold text-[#0F172A]">Por llegar ({{ $pendingStops->count() }})</h2>
            </div>

            <div class="divide-y divide-[#F1F5F9]">
                @forelse ($pendingStops as $stop)
                    <div class="px-6 py-4 flex items-center justify-between">
                        <div>
                            <p class="font-medium text-[#0F172A]">
                                Ruta #{{ $stop->route_id }}
                                @if ($stop->route?->driver?->user)
                                    · {{ $stop->route->driver->user->name }}
                                @endif
                            </p>
                            <p class="text-xs text-[#64748B] mt-1">
                                {{ $stop->packages_collected_count }} paquete(s)
                            </p>
                        </div>
                        <span class="px-3 py-1.5 rounded-lg bg-amber-50 text-amber-700 text-xs font-semibold shrink-0">
                            Pendiente
                        </span>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center">
                        <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3 mx-auto">
                            📦
                        </div>
                        <p class="font-semibold text-[#0F172A]">No hay paradas pendientes</p>
                        <p class="text-sm text-[#64748B] mt-1">Aparecerán aquí cuando una ruta de distribución tenga a tu almacén como destino.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- =========================================================
             RECIBIDAS
        ========================================================== --}}
        <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-[#E2E8F0]">
                <h2 class="font-semibold text-[#0F172A]">Recibidas recientemente ({{ $visitedStops->count() }})</h2>
            </div>

            <div class="divide-y divide-[#F1F5F9]">
                @forelse ($visitedStops as $stop)
                    <div class="px-6 py-4 flex items-center justify-between">
                        <div>
                            <p class="font-medium text-[#0F172A]">
                                Ruta #{{ $stop->route_id }}
                                @if ($stop->route?->driver?->user)
                                    · {{ $stop->route->driver->user->name }}
                                @endif
                            </p>
                            <p class="text-xs text-[#64748B] mt-1">
                                {{ $stop->packages_collected_count }} paquete(s) · {{ $stop->visited_at?->format('d/m/Y H:i') }}
                            </p>
                        </div>
                        <span class="px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-semibold shrink-0">
                            Recibida
                        </span>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center">
                        <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3 mx-auto">
                            ✅
                        </div>
                        <p class="font-semibold text-[#0F172A]">Todavía no se ha recibido ninguna parada</p>
                        <p class="text-sm text-[#64748B] mt-1">Las paradas confirmadas aparecerán aquí.</p>
                    </div>
                @endforelse
            </div>
        </div>

    @else

        <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-sm p-12 text-center">
            <div class="w-14 h-14 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-4 mx-auto">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7l9-4 9 4M3 7l9 4m-9-4v10l9 4m0-10l9-4m-9 4v10m9-14v10l-9 4" />
                </svg>
            </div>
            <p class="font-semibold text-[#0F172A]">Sin almacén asignado</p>
            <p class="text-sm text-[#64748B] mt-1">Contacta a un administrador para que te asigne uno.</p>
        </div>

    @endif

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

                                $wire.scanGuide(decodedText.trim());

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
