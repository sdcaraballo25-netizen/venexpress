{{--
    Lista de categorías reutilizada en el sidebar de escritorio y en el
    drawer de filtros de móvil (ver marketplace.blade.php). Mismo
    mecanismo de selección que el mega-menú: wire:click="$set('categoriaId', ...)".
--}}

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
@endphp

<nav class="space-y-0.5">

    <button
        type="button"
        wire:click="$set('categoriaId', '')"
        class="w-full flex items-center justify-between rounded-lg px-3 py-2.5 text-sm font-semibold transition
        {{ $categoriaId === '' ? 'bg-amber-50 text-[#111111]' : 'text-gray-600 hover:bg-gray-50' }}"
    >
        Todas las categorías
    </button>

    @foreach ($categorias as $categoria)

        @php
            $icono = $iconosPorSlug[$categoria->slug] ?? 'fa-tag';
            $activa = (int) $categoriaId === $categoria->id;
        @endphp

        <button
            type="button"
            wire:click="$set('categoriaId', {{ $categoria->id }})"
            class="w-full flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-left transition
            {{ $activa ? 'bg-amber-50 text-[#111111] font-semibold' : 'text-gray-600 hover:bg-gray-50' }}"
        >
            <i class="fa-solid {{ $icono }} text-xs w-4 text-center {{ $activa ? 'text-[#111111]' : 'text-gray-400' }}"></i>

            <span class="flex-1 truncate">{{ $categoria->nombre }}</span>

            @if (($categoria->productos_count ?? 0) > 0)
                <span class="text-[0.68rem] font-semibold text-gray-400">{{ $categoria->productos_count }}</span>
            @endif
        </button>

    @endforeach

</nav>
