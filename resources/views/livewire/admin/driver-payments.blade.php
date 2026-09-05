<div class="space-y-6">
    <div>
        <h2 class="font-display text-2xl font-semibold text-slate-900">Remuneración de repartidores</h2>
        <p class="mt-1 text-sm text-slate-500">Pagos generados únicamente por entregas confirmadas.</p>
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
        @if ($payments->isEmpty())
            <div class="px-5 py-12 text-center text-sm text-slate-500">No hay pagos para mostrar.</div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach ($payments as $payment)
                    <div class="flex flex-col gap-4 px-5 py-5 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-tracking text-sm font-semibold text-slate-900">
                                    {{ $payment->package?->tracking_number ?? '—' }}
                                </span>
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </div>
                            <p class="mt-2 text-sm text-slate-700">{{ $payment->driver?->user?->name ?? 'Repartidor' }}</p>
                            <p class="mt-1 text-xs text-slate-500">
                                Entrega: {{ $payment->package?->delivery_completed_at?->format('d/m/Y H:i') ?? '—' }}
                            </p>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="text-right">
                                <p class="text-xs text-slate-400">Remuneración</p>
                                <p class="text-lg font-semibold text-slate-900">
                                    ${{ number_format((float) $payment->amount_usd, 2) }}
                                </p>
                            </div>
                            @if ($payment->status === \App\Models\DriverPayment::STATUS_PENDING)
                                <div class="flex items-center gap-2">
                                    <button type="button"
                                            wire:click="markPaid({{ $payment->id }})"
                                            wire:confirm="¿Confirmas que deseas registrar este pago?"
                                            class="rounded-xl bg-blue-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-800">
                                        Marcar pagado
                                    </button>
                                    <button type="button"
                                            wire:click="cancelPayment({{ $payment->id }})"
                                            wire:confirm="¿Confirmas que deseas cancelar esta remuneración pendiente?"
                                            class="rounded-xl border border-red-200 bg-white px-4 py-2.5 text-sm font-semibold text-red-700 hover:bg-red-50">
                                        Cancelar
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="border-t border-slate-200 px-5 py-4">{{ $payments->links() }}</div>
        @endif
    </div>
</div>
