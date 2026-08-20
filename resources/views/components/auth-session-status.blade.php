@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'flex items-center gap-2 rounded-lg bg-emerald-50 px-3 py-2 font-medium text-sm text-emerald-700']) }}>
        <svg viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 shrink-0"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-7.4 7.4a1 1 0 0 1-1.4 0L3.3 9.5a1 1 0 1 1 1.4-1.4l3.9 3.9 6.7-6.7a1 1 0 0 1 1.4 0Z" clip-rule="evenodd"/></svg>
        {{ $status }}
    </div>
@endif
