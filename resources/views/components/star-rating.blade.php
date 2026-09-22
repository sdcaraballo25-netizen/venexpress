@props(['rating' => null, 'count' => null, 'size' => 'text-base'])

@if ($rating !== null)
    <div class="flex items-center gap-1">
        <div class="flex items-center gap-0.5 {{ $size }}">
            @for ($i = 1; $i <= 5; $i++)
                <span class="{{ $i <= round($rating) ? 'text-amber-400' : 'text-gray-200' }}">★</span>
            @endfor
        </div>
        <span class="text-xs text-gray-400">
            {{ number_format($rating, 1) }}
            @if ($count !== null)
                ({{ $count }})
            @endif
        </span>
    </div>
@elseif ($count !== null)
    <p class="text-xs text-gray-400">Sin reseñas todavía</p>
@endif
