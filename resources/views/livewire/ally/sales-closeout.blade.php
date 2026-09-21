<div class="space-y-6">

    <div>
        <h2 class="font-display text-2xl font-semibold text-[#0F172A]">
            Cierre del día
        </h2>
        <p class="text-sm text-slate-500 mt-1">
            @if ($isPrincipal)
                Total vendido y por dónde se facturó, para cuadrar la caja contra el sistema.
            @else
                Lo que registraste hoy, para cuadrar tu caja al cerrar.
            @endif
        </p>
    </div>

    <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-end gap-4 mb-6">
            <div>
                <label class="text-sm text-slate-600">Fecha</label>
                <input type="date" wire:model.live="date"
                    class="mt-1 rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-black focus:ring-black">
            </div>

            @if ($isPrincipal)
                <div>
                    <label class="text-sm text-slate-600">Taquilla</label>
                    <select wire:model.live="registeredBy"
                        class="mt-1 rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-black focus:ring-black">
                        <option value="all">Todo el negocio</option>
                        <option value="{{ auth()->id() }}">Tú (registrado directamente)</option>
                        @foreach ($staffOptions as $option)
                            <option value="{{ $option->id }}">{{ $option->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
            <div class="rounded-xl bg-black px-5 py-4 text-white">
                <p class="text-xs text-gray-400 uppercase tracking-wide">Total vendido</p>
                <p class="font-display text-3xl font-bold mt-1">${{ number_format((float) $totalUsd, 2) }}</p>
            </div>
            <div class="rounded-xl bg-slate-50 border border-slate-200 px-5 py-4">
                <p class="text-xs text-slate-500 uppercase tracking-wide">Guías registradas</p>
                <p class="font-display text-3xl font-bold text-[#0F172A] mt-1">{{ $totalGuides }}</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
            <h3 class="text-sm font-semibold text-[#0F172A]">Por forma de pago</h3>

            @if ($byPaymentMethod->isNotEmpty())
                <button
                    type="button"
                    wire:click="exportExcel"
                    class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                >
                    ⬇ Exportar Excel
                </button>
            @endif
        </div>

        @if ($byPaymentMethod->isEmpty())
            <p class="text-sm text-slate-400 py-4">No hay ventas con forma de pago registrada en esta fecha.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-slate-400 border-b border-slate-200">
                            <th class="py-2 pr-4">Forma de pago</th>
                            <th class="py-2 pr-4">Guías</th>
                            <th class="py-2 pr-4">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($byPaymentMethod as $row)
                            <tr class="border-b border-slate-100 last:border-0">
                                <td class="py-3 pr-4 font-medium text-[#0F172A]">
                                    {{ \App\Models\Package::PAYMENT_METHOD_LABELS[$row->payment_method] ?? $row->payment_method }}
                                </td>
                                <td class="py-3 pr-4 text-slate-500">{{ $row->guides }}</td>
                                <td class="py-3 pr-4 font-medium text-[#0F172A]">${{ number_format((float) $row->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($codPendingCount > 0)
            <div class="mt-5 rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">
                <strong>{{ $codPendingCount }}</strong> guía(s) contra entrega (COD) por
                <strong>${{ number_format((float) $codPendingUsd, 2) }}</strong> registradas hoy —
                no están arriba porque ese cobro lo hace el repartidor al entregar, no la taquilla.
            </div>
        @endif
    </div>
</div>
