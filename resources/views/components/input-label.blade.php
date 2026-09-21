@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-semibold text-sm text-black']) }}>
    {{ $value ?? $slot }}
</label>
