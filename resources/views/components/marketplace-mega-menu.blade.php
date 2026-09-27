{{--
    Panel de categorías del Marketplace, inspirado conceptualmente en
    el mega-menú de BestMarkets (categoría seleccionada + panel con
    subcategorías en columnas) pero adaptado a los datos reales de
    Venexpress: Categoria es plana (sin parent_id/subcategorías), así
    que este panel muestra únicamente las categorías reales en
    columnas, sin inventar un segundo nivel.

    Alpine.js controla solo la apertura/cierre (puramente visual). La
    selección de categoría sigue disparando el filtro real del
    Marketplace vía wire:click="$set('categoriaId', ...)" — mismo
    mecanismo que ya usaba el <select> original, Livewire re-renderiza
    el catálogo igual que antes.
--}}

@props(['categorias', 'categoriaId'])

@php
    $iconosPorSlug = [
        'ropa-y-accesorios' => 'fa-shirt',
        'alimentos-y-bebidas' => 'fa-utensils',
        'hogar-y-decoracion' => 'fa-couch',
        'belleza-y-cuidado-personal' => 'fa-spray-can-sparkles',
        'tecnologia' => 'fa-laptop',
        'artesanias' => 'fa-palette',
        'otros' => 'fa-tag',
    ];

    $categoriaActiva = $categorias->firstWhere('id', (int) $categoriaId);
@endphp

<div x-data="{ open: false }" @keydown.escape.window="open = false" class="relative">

    <button
        type="button"
        @click="open = !open"
        :aria-expanded="open"
        class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-4 py-2.5 text-sm font-semibold text-[#111111] hover:border-gray-300 hover:bg-gray-50 transition"
    >
        <i class="fa-solid fa-bars text-xs text-gray-400"></i>

        {{ $categoriaActiva?->nombre ?? 'Categorías' }}

        <i class="fa-solid fa-chevron-down text-[0.6rem] text-gray-400 transition-transform" :class="{ 'rotate-180': open }"></i>
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click.outside="open = false"
        class="absolute left-0 z-30 mt-2 w-[min(92vw,640px)] rounded-2xl border border-gray-200 bg-white p-6 shadow-xl"
    >

        <div class="flex items-center justify-between mb-4">
            <p class="text-xs font-bold uppercase tracking-wide text-gray-400">
                Categorías
            </p>

            <button
                type="button"
                wire:click="$set('categoriaId', '')"
                @click="open = false"
                class="text-xs font-semibold {{ $categoriaId === '' ? 'text-amber-600' : 'text-gray-400 hover:text-[#111111]' }}"
            >
                Ver todas
            </button>
        </div>

        <div class="[column-count:1] sm:[column-count:2] lg:[column-count:3] gap-x-6">

            @foreach ($categorias as $categoria)

                @php
                    $icono = $iconosPorSlug[$categoria->slug] ?? 'fa-tag';
                    $activa = (int) $categoriaId === $categoria->id;
                @endphp

                <button
                    type="button"
                    wire:click="$set('categoriaId', {{ $categoria->id }})"
                    @click="open = false"
                    class="w-full flex items-center gap-3 rounded-lg px-2 py-2.5 mb-1 text-left break-inside-avoid transition
                    {{ $activa ? 'bg-amber-50 text-[#111111]' : 'text-gray-600 hover:bg-gray-50' }}"
                >
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $activa ? 'bg-amber-400' : 'bg-gray-100' }}">
                        <i class="fa-solid {{ $icono }} text-xs {{ $activa ? 'text-[#111111]' : 'text-gray-500' }}"></i>
                    </span>

                    <span class="flex-1 text-sm font-medium truncate">
                        {{ $categoria->nombre }}
                    </span>

                    @if (($categoria->productos_count ?? 0) > 0)
                        <span class="shrink-0 text-[0.68rem] font-semibold text-gray-400">
                            {{ $categoria->productos_count }}
                        </span>
                    @endif
                </button>

            @endforeach

        </div>

    </div>

</div>
