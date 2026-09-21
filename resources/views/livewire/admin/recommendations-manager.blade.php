@php
    use App\Models\Recommendation;
@endphp

<div class="min-h-screen">

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h1 class="font-display text-3xl font-bold text-[#0F172A]">
                Recomendaciones
            </h1>
            <p class="text-sm text-[#64748B] mt-1">
                Sugerencias enviadas por visitantes desde la página pública.
            </p>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="mb-6 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            <div class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100">✓</div>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="space-y-4">
        @forelse ($recommendations as $recommendation)
            <div
                wire:key="recommendation-{{ $recommendation->id }}"
                wire:mouseenter="markRead({{ $recommendation->id }})"
                class="bg-white border border-[#E2E8F0] rounded-2xl p-5 shadow-sm"
            >
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="font-semibold text-[#0F172A]">{{ $recommendation->name }}</p>
                        @if ($recommendation->email)
                            <p class="text-xs text-[#64748B] mt-0.5">{{ $recommendation->email }}</p>
                        @endif
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        @if ($recommendation->status === Recommendation::STATUS_NEW)
                            <span class="px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 text-xs font-semibold">Nueva</span>
                        @endif

                        <button
                            wire:click="archive({{ $recommendation->id }})"
                            wire:confirm="¿Archivar esta recomendación?"
                            class="px-3 py-1.5 rounded-lg bg-slate-50 text-slate-600 hover:bg-slate-100 text-xs font-semibold transition"
                        >
                            Archivar
                        </button>
                    </div>
                </div>

                <p class="mt-3 text-sm text-[#334155] whitespace-pre-line">{{ $recommendation->message }}</p>

                <p class="mt-3 text-xs text-[#94A3B8]">{{ $recommendation->created_at->diffForHumans() }}</p>
            </div>
        @empty
            <div class="bg-white border border-[#E2E8F0] rounded-2xl p-12 text-center text-sm text-[#94A3B8]">
                No hay recomendaciones por revisar.
            </div>
        @endforelse
    </div>

    @if ($recommendations->hasPages())
        <div class="mt-6">
            {{ $recommendations->links() }}
        </div>
    @endif

</div>
