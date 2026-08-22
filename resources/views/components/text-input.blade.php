@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full rounded-lg border-gray-300 bg-white text-blue-950 text-sm placeholder:text-gray-400 shadow-sm focus:border-blue-600 focus:ring-blue-600 disabled:bg-gray-50 disabled:text-gray-400']) }}>
