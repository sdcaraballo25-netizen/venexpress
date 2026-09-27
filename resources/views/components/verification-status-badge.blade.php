@props(['status', 'small' => false])

@php
    /*
     * Componente puramente de presentación: solo traduce
     * verification_status (PENDIENTE/EN_REVISION/VERIFICADO/RECHAZADO)
     * a un color + etiqueta legible. No decide ni valida nada — la
     * única fuente de verdad para "puede operar" sigue siendo
     * Driver/Ally/Emprendedor::canOperate() en el backend.
     */
    $styles = [
        'PENDIENTE' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'dot' => 'bg-amber-500', 'label' => 'Pendiente'],
        'EN_REVISION' => ['bg' => 'bg-sky-50', 'text' => 'text-sky-700', 'dot' => 'bg-sky-500', 'label' => 'En revisión'],
        'VERIFICADO' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'dot' => 'bg-emerald-600', 'label' => 'Verificado'],
        'RECHAZADO' => ['bg' => 'bg-red-50', 'text' => 'text-red-700', 'dot' => 'bg-red-500', 'label' => 'Rechazado'],
    ];

    $style = $styles[$status] ?? [
        'bg' => 'bg-slate-50', 'text' => 'text-slate-600', 'dot' => 'bg-slate-400', 'label' => str_replace('_', ' ', (string) $status),
    ];

    $padding = $small ? 'px-2.5 py-1' : 'px-3 py-1.5';
    $textSize = $small ? 'text-[10px]' : 'text-xs';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-2 rounded-lg font-semibold {$padding} {$textSize} {$style['bg']} {$style['text']}"]) }}>
    <span class="w-1.5 h-1.5 rounded-full {{ $style['dot'] }}"></span>
    {{ $style['label'] }}
</span>
