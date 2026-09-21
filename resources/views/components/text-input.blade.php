@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full rounded-lg border-gray-300 bg-white text-black text-sm placeholder:text-gray-400 shadow-sm focus:border-black focus:ring-black disabled:bg-gray-50 disabled:text-gray-400']) }}>
