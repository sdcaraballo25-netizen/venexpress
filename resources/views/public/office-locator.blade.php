@once
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
@endonce

<div>
    <section class="bg-white">
        <div class="max-w-7xl mx-auto px-6 py-14">

            <div class="text-center mb-10">
                <span class="inline-block bg-amber-100 text-amber-700 text-xs font-semibold tracking-wide uppercase px-3 py-1 rounded-full mb-4">
                    Red nacional
                </span>
                <h1 class="text-3xl md:text-4xl font-extrabold text-blue-950">Encuentra tu agencia aliada</h1>
                <p class="text-gray-500 mt-3 max-w-xl mx-auto">
                    Ubica la agencia Venexpress más cercana para entregar o retirar tu paquete.
                </p>
            </div>

            {{-- FILTROS --}}
            <div class="flex flex-col md:flex-row gap-3 mb-6">
                <div class="relative flex-1">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="text" wire:model.live.debounce.400ms="search"
                        placeholder="Buscar por nombre, ciudad o dirección..."
                        class="w-full rounded-lg border-gray-200 pl-10 text-sm focus:ring-blue-950 focus:border-blue-950">
                </div>
                <select wire:model.live="state" class="rounded-lg border-gray-200 text-sm focus:ring-blue-950 focus:border-blue-950 md:w-64">
                    <option value="">Todos los estados</option>
                    @foreach ($states as $s)
                        <option value="{{ $s }}">{{ $s }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid lg:grid-cols-5 gap-6">

                {{-- MAPA --}}
                <div class="lg:col-span-3">
                    <div
                        wire:ignore
                        x-data="officeLocatorMap(@js($mapPoints))"
                        x-init="init($el)"
                        class="rounded-2xl overflow-hidden border border-gray-100 shadow-sm"
                        style="height: 520px;"
                    ></div>
                </div>

                {{-- LISTA --}}
                <div class="lg:col-span-2 space-y-3 max-h-[520px] overflow-y-auto pr-1">
                    @forelse ($allies as $ally)
                        <div wire:key="ally-{{ $ally->id }}"
                            class="border border-gray-100 rounded-xl p-4 hover:border-blue-950 transition cursor-pointer"
                            onclick="window.venexpressFocusOffice && window.venexpressFocusOffice({{ $ally->id }})">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-blue-950">{{ $ally->business_name }}</p>
                                    <p class="text-sm text-gray-500 mt-0.5">{{ $ally->address }}</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ $ally->city }}, {{ $ally->state }}</p>
                                </div>
                                <a href="https://www.google.com/maps/dir/?api=1&destination={{ $ally->latitude }},{{ $ally->longitude }}"
                                    target="_blank" rel="noopener"
                                    onclick="event.stopPropagation()"
                                    class="shrink-0 w-9 h-9 rounded-full bg-blue-950 text-white flex items-center justify-center hover:bg-blue-900 transition">
                                    <i class="fa-solid fa-diamond-turn-right text-sm"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-gray-400 text-sm py-16 border border-dashed border-gray-200 rounded-xl">
                            <i class="fa-solid fa-map-location-dot text-2xl mb-2 block"></i>
                            No encontramos agencias con esos filtros.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    function officeLocatorMap(initialPoints) {
        return {
            map: null,
            markers: {},

            init(el) {
                this.map = L.map(el).setView([8.0, -66.0], 6); // Centro aprox. de Venezuela

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors',
                }).addTo(this.map);

                this.renderPoints(initialPoints);

                window.venexpressFocusOffice = (id) => {
                    const marker = this.markers[id];
                    if (marker) {
                        this.map.setView(marker.getLatLng(), 15);
                        marker.openPopup();
                    }
                };

                Livewire.on('offices-updated', ({ allies }) => this.renderPoints(allies));

                setTimeout(() => this.map.invalidateSize(), 150);
            },

            renderPoints(points) {
                Object.values(this.markers).forEach((m) => this.map.removeLayer(m));
                this.markers = {};

                if (!points.length) {
                    return;
                }

                const bounds = [];

                points.forEach((p) => {
                    const marker = L.marker([p.lat, p.lng])
                        .addTo(this.map)
                        .bindPopup(`<strong>${p.name}</strong><br>${p.address}<br>${p.city}`);

                    this.markers[p.id] = marker;
                    bounds.push([p.lat, p.lng]);
                });

                if (bounds.length > 1) {
                    this.map.fitBounds(bounds, { padding: [30, 30] });
                } else {
                    this.map.setView(bounds[0], 14);
                }
            },
        }
    }
</script>
