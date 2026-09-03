<div class="min-h-screen">
    <div class="mb-8">
        <h1 class="font-display text-3xl font-bold text-[#0F172A]">Incidencias</h1>
        <p class="mt-1 text-sm text-[#64748B]">Da seguimiento a las incidencias reportadas por las agencias aliadas.</p>
    </div>

    @if (session('success'))
        <div class="mb-6 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            <div class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100">✓</div>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="mb-6 rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
        <div class="grid gap-4 md:grid-cols-[1fr_220px]">
            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-[#64748B]">Buscar</label>
                <input wire:model.live.debounce.300ms="search" type="text"
                       placeholder="Tipo, descripción o número de guía..."
                       class="w-full rounded-xl border border-[#E2E8F0] px-4 py-3 text-sm text-[#0F172A] placeholder:text-[#94A3B8] focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-[#64748B]">Estado</label>
                <select wire:model.live="status" class="w-full rounded-xl border border-[#E2E8F0] px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="all">Todos</option>
                    @foreach (\App\Livewire\Admin\IncidentsManager::STATUS_LABELS as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-[#E2E8F0] bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#64748B]">Guía</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#64748B]">Agencia</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#64748B]">Tipo</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#64748B]">Descripción</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#64748B]">Reportada</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold uppercase text-[#64748B]">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($incidents as $incident)
                        <tr class="border-b border-[#F1F5F9] last:border-0 hover:bg-slate-50">
                            <td class="px-6 py-4 font-medium text-[#0F172A]">
                                {{ $incident->package?->tracking_number ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-[#64748B]">
                                {{ $incident->ally?->business_name ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-[#0F172A]">{{ $incident->type }}</td>
                            <td class="px-6 py-4 max-w-xs truncate text-[#64748B]" title="{{ $incident->description }}">
                                {{ $incident->description }}
                            </td>
                            <td class="px-6 py-4 text-[#64748B]">
                                {{ $incident->created_at->format('d/m/Y H:i') }}
                                <p class="text-xs text-[#94A3B8]">{{ $incident->reportedByUser?->name ?? '—' }}</p>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <select wire:change="updateStatus({{ $incident->id }}, $event.target.value)"
                                        class="rounded-xl border-[#E2E8F0] text-xs font-semibold focus:border-blue-500 focus:ring-blue-500">
                                    @foreach (\App\Livewire\Admin\IncidentsManager::STATUS_LABELS as $value => $label)
                                        <option value="{{ $value }}" @selected($incident->status === $value)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-[#64748B]">
                                No hay incidencias que coincidan con el filtro.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($incidents->hasPages())
            <div class="border-t border-[#E2E8F0] px-6 py-4">
                {{ $incidents->links() }}
            </div>
        @endif
    </div>
</div>
