<div class="space-y-6">

    @php
        $money = fn ($v) => '$'.number_format((float) $v, 2);
        $balanceLabel = function (float $balance) use ($money) {
            if ($balance > 0) {
                return ['A favor '.$money($balance), 'text-emerald-700 bg-emerald-50'];
            }
            if ($balance < 0) {
                return ['Debe '.$money(abs($balance)), 'text-red-700 bg-red-50'];
            }

            return ['Al día', 'text-slate-600 bg-slate-100'];
        };
    @endphp

    <div>
        <h1 class="text-2xl font-bold text-slate-900">Cuentas por cobrar — Franquicias</h1>
        <p class="mt-1 text-sm text-slate-500">
            Cada franquicia paga una cuota mensual: un porcentaje de sus ventas del mes. Registra aquí cada pago con
            su número de factura y voucher, y controla lo que queda a favor o pendiente.
        </p>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    {{-- TOTALES GENERALES --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs uppercase tracking-wide text-slate-500">Total facturado</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $money($totals['invoiced']) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs uppercase tracking-wide text-slate-500">Total cobrado</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $money($totals['paid']) }}</p>
        </div>
        @php [$label, $cls] = $balanceLabel($totals['balance']); @endphp
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs uppercase tracking-wide text-slate-500">Saldo de todas las franquicias</p>
            <p class="mt-2"><span class="rounded-lg px-3 py-1 text-lg font-bold {{ $cls }}">{{ $label }}</span></p>
        </div>
    </div>

    {{-- LISTA DE FRANQUICIAS --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="font-semibold text-slate-900">Franquicias</h2>
                <p class="text-sm text-slate-500">Selecciona una para ver su estado de cuenta y registrar pagos.</p>
            </div>
            <input type="text" wire:model.live.debounce.400ms="search" placeholder="Buscar franquicia, RIF o ciudad..."
                class="w-full rounded-xl border-slate-300 md:w-80">
        </div>

        <div class="mt-5 overflow-x-auto">
            <table class="min-w-[900px] w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-wide text-slate-500">
                        <th class="px-3 py-3">Franquicia</th>
                        <th class="px-3 py-3">Cuota</th>
                        <th class="px-3 py-3">Ventas del mes</th>
                        <th class="px-3 py-3">Cuota del mes (en curso)</th>
                        <th class="px-3 py-3">Meses sin facturar</th>
                        <th class="px-3 py-3">Facturado</th>
                        <th class="px-3 py-3">Pagado</th>
                        <th class="px-3 py-3">Saldo</th>
                        <th class="px-3 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($franchiseRows as $row)
                        @php [$label, $cls] = $balanceLabel($row['balance']); @endphp
                        <tr wire:key="franchise-{{ $row['ally']->id }}"
                            class="border-b border-slate-100 last:border-0 {{ $selectedAlly?->id === $row['ally']->id ? 'bg-blue-50' : '' }}">
                            <td class="px-3 py-3">
                                <p class="font-semibold text-slate-900">{{ $row['ally']->business_name }}</p>
                                <p class="text-xs text-slate-500">{{ $row['ally']->rif }} · {{ $row['ally']->city }}</p>
                            </td>
                            <td class="px-3 py-3 text-slate-700">{{ number_format((float) $row['ally']->franchise_fee_percentage, 2) }}%</td>
                            <td class="px-3 py-3 text-slate-700">{{ $money($row['current_sales']) }}</td>
                            <td class="px-3 py-3 text-slate-700">{{ $money($row['current_fee']) }}</td>
                            <td class="px-3 py-3">
                                @if ($row['pending_months'] > 0)
                                    <span class="rounded-lg bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700">
                                        {{ $row['pending_months'] }} · {{ $money($row['pending_amount']) }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-slate-700">{{ $money($row['invoiced']) }}</td>
                            <td class="px-3 py-3 text-slate-700">{{ $money($row['paid']) }}</td>
                            <td class="px-3 py-3"><span class="rounded-lg px-2 py-1 text-xs font-semibold {{ $cls }}">{{ $label }}</span></td>
                            <td class="px-3 py-3 text-right">
                                <button type="button" wire:click="selectAlly({{ $row['ally']->id }})"
                                    class="rounded-lg bg-blue-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-800">
                                    Ver cuenta
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-3 py-6 text-center text-slate-400">
                                No hay franquicias. Créalas desde Gestión de Usuarios (tipo "Franquiciado").
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $franchises->links() }}</div>
    </div>

    @if ($selectedAlly)
        @php [$label, $cls] = $balanceLabel($selectedBalance); @endphp

        <div class="rounded-2xl border border-blue-100 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-blue-900">Estado de cuenta</p>
                    <h2 class="text-xl font-bold text-slate-900">{{ $selectedAlly->business_name }}</h2>
                    <p class="text-sm text-slate-500">{{ $selectedAlly->rif }} · {{ $selectedAlly->address }}, {{ $selectedAlly->city }}</p>
                    <p class="mt-2"><span class="rounded-lg px-3 py-1 text-sm font-bold {{ $cls }}">{{ $label }}</span></p>
                </div>

                <form wire:submit="saveFee" class="flex flex-wrap items-end gap-2">
                    <div>
                        <label class="text-xs text-slate-500">Cuota mensual (% de ventas)</label>
                        <input type="number" step="0.01" min="0" max="100" wire:model="feePercentage"
                            class="mt-1 block w-32 rounded-xl border-slate-300 text-sm">
                        @error('feePercentage') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit" class="rounded-xl border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Guardar %
                    </button>
                    <button type="button" wire:click="selectAlly(null)" class="rounded-xl px-3 py-2 text-sm text-slate-500 hover:bg-slate-50">
                        Cerrar
                    </button>
                </form>
            </div>

            {{-- ESTADO DE CUENTA MES A MES --}}
            <div class="mt-6 overflow-x-auto rounded-xl border border-slate-200">
                <table class="min-w-[760px] w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                            <th class="px-3 py-2">Mes</th>
                            <th class="px-3 py-2">Ventas</th>
                            <th class="px-3 py-2">Cuota calculada</th>
                            <th class="px-3 py-2">Factura(s)</th>
                            <th class="px-3 py-2">Facturado</th>
                            <th class="px-3 py-2">Pagado</th>
                            <th class="px-3 py-2">Saldo</th>
                            <th class="px-3 py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($statement as $month)
                            <tr wire:key="month-{{ $month['key'] }}" class="border-b border-slate-100 last:border-0">
                                <td class="px-3 py-2 font-medium text-slate-900">
                                    {{ $month['label'] }}
                                    @if ($month['is_current'])
                                        <span class="text-xs text-slate-400">(en curso)</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-slate-600">{{ $money($month['sales']) }}</td>
                                <td class="px-3 py-2 text-slate-600">{{ $money($month['expected']) }}</td>
                                <td class="px-3 py-2 text-xs text-slate-600">{{ implode(', ', $month['invoice_numbers']) ?: '—' }}</td>
                                <td class="px-3 py-2 text-slate-600">{{ $month['invoiced'] > 0 ? $money($month['invoiced']) : '—' }}</td>
                                <td class="px-3 py-2 text-slate-600">{{ $month['paid'] > 0 ? $money($month['paid']) : '—' }}</td>
                                <td class="px-3 py-2">
                                    @if ($month['pending_invoice'])
                                        <span class="rounded-lg bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700">Sin facturar</span>
                                    @elseif ($month['invoiced'] > 0)
                                        @php [$mLabel, $mCls] = $balanceLabel($month['balance']); @endphp
                                        <span class="rounded-lg px-2 py-1 text-xs font-semibold {{ $mCls }}">{{ $mLabel }}</span>
                                    @else
                                        <span class="text-xs text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <button type="button" wire:click="$set('period', '{{ $month['key'] }}')"
                                        class="rounded-lg px-2 py-1 text-xs font-semibold text-blue-900 hover:bg-blue-50">
                                        Registrar pago
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- REGISTRAR PAGO --}}
            <form wire:submit="registerPayment" class="mt-6 rounded-xl bg-slate-50 p-4">
                <h3 class="text-sm font-semibold text-slate-900">Registrar pago</h3>
                <p class="mb-4 text-xs text-slate-500">
                    Si el número de factura ya existe, el pago se suma como abono a esa factura.
                </p>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label class="text-xs text-slate-500">Mes facturado</label>
                        <input type="month" wire:model.live="period" max="{{ now()->format('Y-m') }}"
                            class="mt-1 block w-full rounded-xl border-slate-300 text-sm">
                        @error('period') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-xs text-slate-500">N° de factura</label>
                        <input type="text" wire:model.live.debounce.400ms="invoiceNumber" maxlength="60"
                            class="mt-1 block w-full rounded-xl border-slate-300 text-sm">
                        @error('invoiceNumber') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-xs text-slate-500">Monto de la factura (USD)</label>
                        <input type="number" step="0.01" min="0" wire:model="invoiceAmount" @disabled($existingInvoice)
                            class="mt-1 block w-full rounded-xl border-slate-300 text-sm disabled:bg-slate-100">
                        @if ($existingInvoice)
                            <p class="mt-1 text-xs text-blue-900">
                                Factura existente · pagado {{ $money($existingInvoice->paidTotal()) }}
                                · {{ $balanceLabel($existingInvoice->balance())[0] }}
                            </p>
                        @endif
                        @error('invoiceAmount') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-xs text-slate-500">Monto pagado (USD)</label>
                        <input type="number" step="0.01" min="0" wire:model.live.debounce.400ms="amountPaid"
                            class="mt-1 block w-full rounded-xl border-slate-300 text-sm">
                        @error('amountPaid') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-xs text-slate-500">N° de voucher / referencia</label>
                        <input type="text" wire:model="paymentReference" maxlength="120"
                            class="mt-1 block w-full rounded-xl border-slate-300 text-sm">
                        @error('paymentReference') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-xs text-slate-500">Fecha del pago</label>
                        <input type="date" wire:model="paidOn" max="{{ now()->toDateString() }}"
                            class="mt-1 block w-full rounded-xl border-slate-300 text-sm">
                        @error('paidOn') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="text-xs text-slate-500">Notas (opcional)</label>
                        <input type="text" wire:model="notes" maxlength="1000"
                            class="mt-1 block w-full rounded-xl border-slate-300 text-sm">
                    </div>
                </div>

                @php
                    $previewInvoice = $existingInvoice ? (float) $existingInvoice->invoice_amount_usd : (float) ($invoiceAmount ?: 0);
                    $previewPaid = ($existingInvoice ? $existingInvoice->paidTotal() : 0) + (float) ($amountPaid ?: 0);
                    $preview = round($previewPaid - $previewInvoice, 2);
                @endphp

                <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-slate-600">
                        @if ($previewInvoice > 0)
                            Resultado de la factura:
                            <span class="rounded-lg px-2 py-1 text-xs font-semibold {{ $balanceLabel($preview)[1] }}">
                                {{ $balanceLabel($preview)[0] }}
                            </span>
                        @endif
                    </p>
                    <button type="submit" wire:loading.attr="disabled" wire:target="registerPayment"
                        class="w-full rounded-xl bg-blue-900 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800 disabled:opacity-60 sm:w-auto">
                        Registrar pago
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- HISTORIAL / REPORTE DE PAGOS --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 class="font-semibold text-slate-900">
                    Pagos registrados {{ $selectedAlly ? '— '.$selectedAlly->business_name : '— todas las franquicias' }}
                </h2>
                <p class="text-sm text-slate-500">
                    {{ $selectedAlly ? 'Cierra el estado de cuenta para ver y exportar los pagos de todas.' : 'Selecciona una franquicia para ver y exportar solo sus pagos.' }}
                </p>
            </div>

            <div class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="text-xs text-slate-500">Desde</label>
                    <input type="date" wire:model.live="filterFrom" class="mt-1 block rounded-xl border-slate-300 text-sm">
                </div>
                <div>
                    <label class="text-xs text-slate-500">Hasta</label>
                    <input type="date" wire:model.live="filterTo" class="mt-1 block rounded-xl border-slate-300 text-sm">
                </div>
                <button type="button" wire:click="exportPayments"
                    class="rounded-xl border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    ⬇ Exportar Excel
                </button>
            </div>
        </div>

        <div class="mt-5 overflow-x-auto">
            <table class="min-w-[900px] w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-wide text-slate-500">
                        <th class="px-3 py-2">Fecha</th>
                        <th class="px-3 py-2">Franquicia</th>
                        <th class="px-3 py-2">Período</th>
                        <th class="px-3 py-2">N° factura</th>
                        <th class="px-3 py-2">Monto factura</th>
                        <th class="px-3 py-2">Pagado</th>
                        <th class="px-3 py-2">Referencia</th>
                        <th class="px-3 py-2">Registrado por</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr wire:key="payment-{{ $payment->id }}" class="border-b border-slate-100 last:border-0">
                            <td class="px-3 py-2 text-slate-600">{{ $payment->paid_on?->format('d/m/Y') }}</td>
                            <td class="px-3 py-2 font-medium text-slate-900">{{ $payment->ally?->business_name }}</td>
                            <td class="px-3 py-2 text-slate-600">{{ ucfirst($payment->invoice->period_month->locale('es')->translatedFormat('F Y')) }}</td>
                            <td class="px-3 py-2 text-slate-600">{{ $payment->invoice->invoice_number }}</td>
                            <td class="px-3 py-2 text-slate-600">{{ $money($payment->invoice->invoice_amount_usd) }}</td>
                            <td class="px-3 py-2 font-semibold text-slate-900">{{ $money($payment->amount_paid_usd) }}</td>
                            <td class="px-3 py-2 text-slate-600">{{ $payment->payment_reference ?? '—' }}</td>
                            <td class="px-3 py-2 text-xs text-slate-500">{{ $payment->recordedBy?->name ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-3 py-6 text-center text-slate-400">No hay pagos registrados con estos filtros.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $payments->links() }}</div>
    </div>
</div>
