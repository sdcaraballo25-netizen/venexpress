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

    @if (session('error'))
        <div class="mb-6 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <div class="flex h-7 w-7 items-center justify-center rounded-full bg-red-100">!</div>
            <span>{{ session('error') }}</span>
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
                                @if ($incident->status === \App\Models\Incident::STATUS_CLOSED && ! auth()->user()?->isAdminPrincipal())
                                    <span class="inline-flex items-center rounded-xl bg-slate-100 px-3 py-2 text-xs font-semibold text-[#64748B]"
                                          title="Solo un Administrador Principal puede modificar una incidencia cerrada.">
                                        {{ \App\Livewire\Admin\IncidentsManager::STATUS_LABELS[$incident->status] }}
                                    </span>
                                @else
                                    <select wire:change="updateStatus({{ $incident->id }}, $event.target.value)"
                                            class="rounded-xl border-[#E2E8F0] text-xs font-semibold focus:border-blue-500 focus:ring-blue-500">
                                        @foreach (\App\Livewire\Admin\IncidentsManager::STATUS_LABELS as $value => $label)
                                            <option value="{{ $value }}" @selected($incident->status === $value)>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif

                                @if ($incident->resolution_notes)
                                    <p class="mt-1 max-w-[14rem] truncate text-xs text-[#94A3B8]" title="{{ $incident->resolution_notes }}">
                                        {{ $incident->resolution_notes }}
                                    </p>
                                @endif
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

    {{-- =========================================================
         MODAL DE NOTAS DE RESOLUCIÓN
         Se muestra al pasar a 'resuelta' o 'cerrada' cuando la
         incidencia todavía no tiene resolution_notes.
    ========================================================== --}}
    @if ($showResolutionModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div class="w-full max-w-md rounded-2xl bg-white shadow-xl">
                <div class="border-b border-[#E2E8F0] p-6">
                    <h2 class="text-lg font-bold text-[#0F172A]">
                        {{ $pendingStatus === \App\Models\Incident::STATUS_CLOSED ? 'Cerrar incidencia' : 'Resolver incidencia' }}
                    </h2>
                    <p class="mt-1 text-sm text-[#64748B]">
                        Describe cómo se resolvió para dejarlo registrado en el historial.
                    </p>
                </div>

                <div class="p-6">
                    <label class="mb-2 block text-xs font-bold uppercase text-[#64748B]">
                        Notas de resolución
                    </label>
                    <textarea wire:model="resolutionNotesInput" rows="4"
                              class="w-full rounded-xl border-[#E2E8F0] text-sm focus:border-blue-500 focus:ring-blue-500"
                              placeholder="Ej: Se contactó al destinatario y se reprogramó la entrega el 05/09..."></textarea>
                    @error('resolutionNotesInput')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-3 border-t border-[#E2E8F0] p-6">
                    <button wire:click="cancelResolution" type="button"
                            class="rounded-xl border border-[#E2E8F0] px-4 py-2 text-sm">
                        Cancelar
                    </button>
                    <button wire:click="confirmResolution" type="button"
                            class="rounded-xl bg-[#0F172A] px-4 py-2 text-sm font-semibold text-white">
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
