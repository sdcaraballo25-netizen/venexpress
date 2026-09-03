<div class="min-h-screen">
    <div class="mb-8">
        <h1 class="font-display text-3xl font-bold text-[#0F172A]">Bitácora de auditoría</h1>
        <p class="mt-1 text-sm text-[#64748B]">Historial de acciones administrativas sensibles (usuarios, permisos y otros cambios).</p>
    </div>

    <div class="mb-6 rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
        <div class="grid gap-4 md:grid-cols-[1fr_240px]">
            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-[#64748B]">Buscar</label>
                <input wire:model.live.debounce.300ms="search" type="text"
                       placeholder="Descripción o nombre del administrador..."
                       class="w-full rounded-xl border border-[#E2E8F0] px-4 py-3 text-sm text-[#0F172A] placeholder:text-[#94A3B8] focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-[#64748B]">Acción</label>
                <select wire:model.live="actionFilter" class="w-full rounded-xl border border-[#E2E8F0] px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Todas</option>
                    @foreach ($actions as $action)
                        <option value="{{ $action }}">{{ $action }}</option>
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
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#64748B]">Fecha</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#64748B]">Administrador</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#64748B]">Acción</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#64748B]">Descripción</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#64748B]">IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr class="border-b border-[#F1F5F9] last:border-0 hover:bg-slate-50">
                            <td class="px-6 py-4 whitespace-nowrap text-[#475569]">
                                {{ $log->created_at?->format('d/m/Y h:i a') }}
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-semibold text-[#0F172A]">{{ $log->actor?->name ?? 'Usuario eliminado' }}</p>
                                <p class="mt-0.5 text-xs text-[#64748B]">{{ $log->actor?->email }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-lg bg-slate-100 px-3 py-1.5 font-tracking text-xs font-semibold text-slate-700">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-[#475569]">
                                {{ $log->description }}
                            </td>
                            <td class="px-6 py-4 font-tracking text-xs text-[#64748B]">
                                {{ $log->ip_address }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-14 text-center">
                                <p class="font-semibold text-[#0F172A]">No hay registros</p>
                                <p class="mt-1 text-sm text-[#64748B]">Todavía no se ha registrado ninguna acción en la bitácora.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="border-t border-[#E2E8F0] px-6 py-4">{{ $logs->links() }}</div>
        @endif
    </div>
</div>
