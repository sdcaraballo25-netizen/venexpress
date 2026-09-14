<div x-data="{ openIndex: null }">
    <section class="bg-white">
        <div class="max-w-3xl mx-auto px-6 py-14">

            <div class="text-center mb-10">
                <span class="inline-block bg-amber-100 text-amber-700 text-xs font-semibold tracking-wide uppercase px-3 py-1 rounded-full mb-4">
                    Centro de ayuda
                </span>
                <h1 class="text-3xl md:text-4xl font-extrabold text-blue-950">Preguntas frecuentes</h1>
                <p class="text-gray-500 mt-3 max-w-xl mx-auto">
                    Resuelve tus dudas sobre envíos, rastreo, agencias aliadas y repartidores.
                </p>
            </div>

            <div class="space-y-3">
                @foreach ($faqs as $index => $faq)
                    <div class="bg-gray-50 border border-gray-100 rounded-xl overflow-hidden">
                        <button
                            type="button"
                            @click="openIndex = openIndex === {{ $index }} ? null : {{ $index }}"
                            class="w-full flex items-center justify-between gap-3 px-5 py-4 text-left"
                        >
                            <span class="font-semibold text-blue-950 text-sm">{{ $faq['question'] }}</span>
                            <svg class="w-4 h-4 shrink-0 text-gray-400 transition-transform" :class="{ 'rotate-180': openIndex === {{ $index }} }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="openIndex === {{ $index }}" x-cloak class="px-5 pb-4 text-sm text-gray-600">
                            {{ $faq['answer'] }}
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="text-center mt-10">
                <p class="text-sm text-gray-500">¿No encontraste lo que buscabas?</p>
                <a href="{{ route('public.recommendations') }}" class="inline-block mt-3 text-blue-900 font-semibold text-sm hover:underline">
                    Envíanos tu pregunta o recomendación →
                </a>
            </div>

        </div>
    </section>
</div>
