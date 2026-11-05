@props([
    'variant' => 'dark', // 'dark' = fondo claro -> logo a color | 'light' = fondo navy -> logo blanco
    'size' => 'md', // 'sm' | 'md' | 'lg'
])

@php
    $logo = $variant === 'light'
        ? asset('images/viayaa-logo-white.png')
        : asset('images/viayaa-logo.png');

    $sizeMap = [
        'sm' => 'h-7',
        'md' => 'h-9',
        'lg' => 'h-11',
    ];
    $h = $sizeMap[$size] ?? $sizeMap['md'];
@endphp

<img src="{{ $logo }}" alt="ViaYaa" {{ $attributes->merge(['class' => "$h w-auto"]) }}>
