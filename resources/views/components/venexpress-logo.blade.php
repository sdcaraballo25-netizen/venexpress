@props([
    'variant' => 'dark', // 'dark' = texto navy (fondo claro) | 'light' = texto blanco (fondo navy)
    'size' => 'md', // 'sm' | 'md' | 'lg'
])

@php
    $textClass = $variant === 'light' ? 'text-white' : 'text-navy-900';
    $sizeMap = [
        'sm' => ['mark' => 'h-7 w-7', 'text' => 'text-base', 'icon' => 'h-3.5 w-3.5'],
        'md' => ['mark' => 'h-9 w-9', 'text' => 'text-xl', 'icon' => 'h-[18px] w-[18px]'],
        'lg' => ['mark' => 'h-11 w-11', 'text' => 'text-2xl', 'icon' => 'h-5 w-5'],
    ];
    $s = $sizeMap[$size] ?? $sizeMap['md'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }}>
    <span class="relative {{ $s['mark'] }} shrink-0 rounded-[0.6rem] bg-navy-900 flex items-center justify-center overflow-hidden">
        <span class="absolute -right-2 -top-2 h-4 w-4 rotate-45 bg-gold-400"></span>
        <svg viewBox="0 0 24 24" fill="none" class="{{ $s['icon'] }} relative text-white">
            <path d="M12 2.5 3.5 7v10L12 21.5 20.5 17V7L12 2.5Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
            <path d="M3.9 7.2 12 11.8l8.1-4.6M12 11.8v9.5" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
        </svg>
    </span>
    <span class="font-display font-bold {{ $s['text'] }} {{ $textClass }} tracking-tight leading-none">
        Ven<span class="text-gold-500">Express</span>
    </span>
</span>
