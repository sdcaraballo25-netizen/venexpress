<div class="space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="font-display text-2xl font-semibold text-slate-900">Remuneración de repartidores</h2>
            <p class="mt-1 text-sm text-slate-500">
                Resumen de lo que se le debe a cada repartidor por entregas confirmadas — como una nómina.
            </p>
        </div>

        @if ($summary->isNotEmpty())
            <button
                type="button"
                wire:click="exportExcel"
                class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50"
            >
                ⬇ Exportar Excel
            </button>
        @endif
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <input type="search" wire:model.live.debounce.300ms="search"
               placeholder="Buscar guía o repartidor..."
               class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm">
        <select wire:model.live="status" class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm">
            <option value="pendiente">Pendientes</option>
            <option value="pagada">Pagadas</option>
            <option value="cancelada">Canceladas</option>
            <option value="all">Todas</option>
        </select>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        @if ($summary->isEmpty())
            <div class="px-5 py-12 text-center text-sm text-slate-500">No hay remuneraciones para mostrar.</div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach ($summary as $row)
                    <div wire:key="driver-{{ $row->driver_id }}">
                        <div
                            role="button"
                            tabindex="0"
                            wire:click="toggleDriver({{ $row->driver_id }})"
                            class="flex w-full cursor-pointer flex-col gap-4 px-5 py-5 text-left hover:bg-slate-50 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div>
                                <p class="font-semibold text-slate-900">{{ $row->driver?->user?->name ?? 'Repartidor' }}</p>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $row->payments_count }} {{ $row->payments_count === 1 ? 'guía' : 'guías' }}
                                </p>
                            </div>

                            <div class="flex items-center gap-4">
                                <div class="text-right">
                                    <p class="text-xs text-slate-400">Total</p>
                                    <p class="text-lg font-semibold text-slate-900">
                                        ${{ number_format((float) $row->total_usd, 2) }}
                                    </p>
                                </div>

                                @if ($status === 'pendiente')
                                    <button
                                        type="button"
                                        wire:click.stop="markAllPaidForDriver({{ $row->driver_id }})"
                                        wire:confirm="¿Confirmas que deseas marcar como pagadas las {{ $row->payments_count }} remuneración(es) pendientes de este repartidor, por un total de ${{ number_format((float) $row->total_usd, 2) }}?"
                                        class="rounded-xl bg-black px-4 py-2.5 text-sm font-semibold text-white hover:bg-gray-800"
                                    >
                                        Marcar todo pagado
                                    </button>
                                @endif

                                <svg
                                    class="h-4 w-4 shrink-0 text-slate-400 transition-transform {{ $expandedDriverId === $row->driver_id ? 'rotate-180' : '' }}"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>

                        @if ($expandedDriverId === $row->driver_id)
                            <div class="border-t border-slate-100 bg-slate-50/60 px-5 py-4">
                                <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Guías de {{ $row->driver?->user?->name ?? 'este repartidor' }}
                                </p>

                                <div class="space-y-2">
                                    @foreach ($expandedPayments as $payment)
                                        <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-white px-4 py-3 text-sm">
                                            <div>
                                                <span class="font-tracking font-semibold text-slate-900">
                                                    {{ $payment->package?->tracking_number ?? '—' }}
                                                </span>
                                                <span class="ml-2 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">
                                                    {{ ucfirst($payment->status) }}
                                                </span>
                                                <p class="mt-0.5 text-xs text-slate-500">
                                                    Entrega: {{ $payment->package?->delivery_completed_at?->format('d/m/Y H:i') ?? '—' }}
                                                </p>
                                            </div>

                                            <div class="flex items-center gap-3">
                                                <span class="font-semibold text-slate-900">
                                                    ${{ number_format((float) $payment->amount_usd, 2) }}
                                                </span>

                                                @if ($payment->status === \App\Models\DriverPayment::STATUS_PENDING)
                                                    <button type="button"
                                                            wire:click="markPaid({{ $payment->id }})"
                                                            wire:confirm="¿Confirmas que deseas registrar este pago?"
                                                            class="rounded-lg bg-black px-3 py-1.5 text-xs font-semibold text-white hover:bg-gray-800">
                                                        Marcar pagado
                                                    </button>
                                                    <button type="button"
                                                            wire:click="cancelPayment({{ $payment->id }})"
                                                            wire:confirm="¿Confirmas que deseas cancelar esta remuneración pendiente?"
                                                            class="rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50">
                                                        Cancelar
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
            <div class="border-t border-slate-200 px-5 py-4">{{ $summary->links() }}</div>
        @endif
    </div>
</div>
