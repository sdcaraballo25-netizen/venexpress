@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-semibold text-sm text-blue-950']) }}>
    {{ $value ?? $slot }}
</label>
