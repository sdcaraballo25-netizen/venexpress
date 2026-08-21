@props([
    'variant' => 'dark', // 'dark' = fondo claro -> logo a color | 'light' = fondo navy -> logo blanco
    'size' => 'md', // 'sm' | 'md' | 'lg'
])

@php
    $logo = $variant === 'light'
        ? asset('images/venexpress-logo-white.png')
        : asset('images/venexpress-logo.png');

    $sizeMap = [
        'sm' => 'h-7',
        'md' => 'h-9',
        'lg' => 'h-11',
    ];
    $h = $sizeMap[$size] ?? $sizeMap['md'];
@endphp

<img src="{{ $logo }}" alt="VenExpress" {{ $attributes->merge(['class' => "$h w-auto"]) }}>
