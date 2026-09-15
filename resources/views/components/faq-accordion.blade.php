@props(['faqs', 'title' => 'Preguntas frecuentes', 'subtitle' => null])

<div x-data="{ openIndex: null }" class="max-w-3xl">

    <h1 class="font-display text-2xl font-bold text-[#0F172A] mb-1">{{ $title }}</h1>

    @if ($subtitle)
        <p class="text-sm text-[#64748B] mb-6">{{ $subtitle }}</p>
    @else
        <div class="mb-6"></div>
    @endif

    <div class="space-y-3">
        @foreach ($faqs as $index => $faq)
            <div class="bg-white border border-[#E2E8F0] rounded-xl overflow-hidden">
                <button
                    type="button"
                    @click="openIndex = openIndex === {{ $index }} ? null : {{ $index }}"
                    class="w-full flex items-center justify-between gap-3 px-5 py-4 text-left"
                >
                    <span class="font-semibold text-[#0F172A] text-sm">{{ $faq['question'] }}</span>
                    <svg class="w-4 h-4 shrink-0 text-[#94A3B8] transition-transform" :class="{ 'rotate-180': openIndex === {{ $index }} }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="openIndex === {{ $index }}" x-cloak class="px-5 pb-4 text-sm text-[#64748B]">
                    {{ $faq['answer'] }}
                </div>
            </div>
        @endforeach
    </div>

</div>
