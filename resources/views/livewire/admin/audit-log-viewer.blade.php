<div class="min-h-screen">
    <div class="mb-8 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="font-display text-3xl font-bold text-[#0F172A]">Bitácora de auditoría</h1>
            <p class="mt-1 text-sm text-[#64748B]">Historial de acciones administrativas sensibles (usuarios, permisos y otros cambios).</p>
        </div>

        <button
            type="button"
            wire:click="exportExcel"
            class="rounded-xl border border-[#E2E8F0] bg-white px-4 py-2.5 text-sm font-semibold text-[#0F172A] hover:bg-slate-50"
        >
            ⬇ Exportar Excel
        </button>
    </div>

    <div class="mb-6 rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
        <div class="grid gap-4 md:grid-cols-3">
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
                        <option value="{{ $action }}">{{ \App\Models\AuditLog::labelFor($action) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-[#64748B]">Periodo</label>
                <select wire:model.live="dateRange" class="w-full rounded-xl border border-[#E2E8F0] px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="all">Todo</option>
                    <option value="3d">Últimos 3 días</option>
                    <option value="7d">Última semana</option>
                    <option value="30d">Último mes</option>
                    <option value="6m">Últimos 6 meses</option>
                    <option value="custom">Rango personalizado</option>
                </select>
            </div>
        </div>

        @if ($dateRange === 'custom')
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-[#64748B]">Desde</label>
                    <input type="date" wire:model.live="customFrom"
                           class="w-full rounded-xl border border-[#E2E8F0] px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-[#64748B]">Hasta</label>
                    <input type="date" wire:model.live="customTo"
                           class="w-full rounded-xl border border-[#E2E8F0] px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>
        @endif
    </div>

    <div class="overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-[#E2E8F0] bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#64748B]">Fecha</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#64748B]">Hora</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#64748B]">Usuario</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#64748B]">Acción</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#64748B]">Descripción</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#64748B]">IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr class="border-b border-[#F1F5F9] last:border-0 hover:bg-slate-50">
                            <td class="px-6 py-4 whitespace-nowrap text-[#475569]">
                                {{ $log->created_at?->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-[#475569]">
                                {{ $log->created_at?->format('h:i a') }}
                            </td>
                            <td class="px-6 py-4">
                                @if ($log->actor)
                                    <a
                                        href="{{ route('admin.users', ['search' => $log->actor->email]) }}"
                                        class="font-semibold text-blue-700 hover:text-blue-900 hover:underline"
                                        title="Ver los datos de este usuario"
                                    >
                                        {{ $log->actor->name }}
                                    </a>
                                    <p class="mt-0.5 text-xs text-[#64748B]">{{ $log->actor->email }}</p>
                                @else
                                    <p class="font-semibold text-[#0F172A]">Usuario eliminado</p>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700">
                                    {{ $log->actionLabel() }}
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
                            <td colspan="6" class="px-6 py-14 text-center">
                                <p class="font-semibold text-[#0F172A]">No hay registros</p>
                                <p class="mt-1 text-sm text-[#64748B]">No se encontró ninguna acción con estos filtros.</p>
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
