@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full rounded-lg border-navy-100 bg-white text-navy-900 placeholder:text-slate-400 shadow-sm focus:border-navy-400 focus:ring-navy-400 disabled:bg-navy-50 disabled:text-slate-400']) }}>
