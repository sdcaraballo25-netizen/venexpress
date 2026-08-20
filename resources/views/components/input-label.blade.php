@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-semibold text-sm text-navy-800']) }}>
    {{ $value ?? $slot }}
</label>
