{{--
    Tarjeta de producto para la vista previa del Marketplace en la
    landing pública (welcome.blade.php). Es una versión reducida y
    100% Blade (sin wire:click) de la tarjeta interactiva que usa
    resources/views/public/marketplace.blade.php: esa tarjeta llama a
    métodos del componente Livewire Marketplace (verProducto,
    comprarProducto) y solo tiene sentido dentro de ese componente.
    Aquí no hay compra ni modal de detalle, solo enlace directo a la
    tienda del emprendedor.
--}}

@props(['producto'])

<a href="{{ route('public.marketplace.store', $producto->emprendedor_id) }}"
   class="market-card group">

    <div class="market-card-image">

        @if ($producto->foto_principal_path)
            <img
                src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($producto->foto_principal_path) }}"
                alt="{{ $producto->nombre }}"
                class="w-full h-full object-contain group-hover:scale-105 transition-transform"
            >
        @else
            <div class="market-card-placeholder" aria-hidden="true">📦</div>
        @endif

    </div>

    <div class="market-card-body">

        <p class="market-card-seller">
            {{ $producto->emprendedor->business_name }}
        </p>

        <p class="market-card-name">
            {{ $producto->nombre }}
        </p>

        <x-star-rating
            :rating="$producto->resenas_avg_estrellas"
            :count="$producto->resenas_count"
            size="text-xs"
        />

        <p class="market-card-price">
            ${{ number_format((float) $producto->precio_usd, 2) }}
        </p>

    </div>

</a>
