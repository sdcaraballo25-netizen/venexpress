<div class="space-y-6">

    {{-- ENCABEZADO --}}
    <div class="flex flex-col gap-1">
        <p class="text-xs font-semibold uppercase tracking-wider text-blue-900">Franquicia</p>
        <h2 class="font-display text-2xl font-semibold text-[#111111]">
            Finanzas de {{ $ally->business_name }}
        </h2>
        <p class="text-sm text-slate-500">
            Tus ventas, las de tus taquillas, tus gastos, tu cuota de franquicia
            ({{ number_format($feePercentage, 2) }}% de tus ventas del mes) y tu utilidad.
        </p>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    {{-- FILTROS --}}
    <div class="rounded-2xl border border-[#E5E5E0] bg-white p-4 sm:p-5 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="flex flex-wrap gap-2">
                @foreach ([
                    'today' => 'Hoy',
                    '7d' => '7 días',
                    'month' => 'Este mes',
                    '30d' => '30 días',
                    'custom' => 'Personalizado',
                ] as $value => $label)
                    <button
                        type="button"
                        wire:click="$set('dateRange', '{{ $value }}')"
                        class="rounded-xl px-3 py-2 text-sm font-medium transition
                        {{ $dateRange === $value
                            ? 'bg-amber-400 text-[#111111]'
                            : 'border border-slate-200 text-slate-600 hover:bg-slate-50' }}"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            <div class="flex flex-wrap items-end gap-3">
                @if ($dateRange === 'custom')
                    <div>
                        <label class="text-xs text-slate-500">Desde</label>
                        <input type="date" wire:model.live="customFrom"
                            class="mt-1 block rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900">
                    </div>
                    <div>
                        <label class="text-xs text-slate-500">Hasta</label>
                        <input type="date" wire:model.live="customTo"
                            class="mt-1 block rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900">
                    </div>
                @endif

                <div>
                    <label class="text-xs text-slate-500">Taquilla</label>
                    <select wire:model.live="registeredBy"
                        class="mt-1 block rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900">
                        <option value="all">Toda la franquicia</option>
                        <option value="{{ auth()->id() }}">Tú (registrado directamente)</option>
                        @foreach ($staffOptions as $option)
                            <option value="{{ $option->id }}">{{ $option->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <p class="mt-3 text-xs text-slate-400">
            Período: {{ $from->format('d/m/Y') }} – {{ $to->format('d/m/Y') }}
            · Comparado con {{ $prevFrom->format('d/m/Y') }} – {{ $prevTo->format('d/m/Y') }}
        </p>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl bg-blue-900 px-5 py-4 text-white shadow-sm">
            <p class="text-xs uppercase tracking-wide text-blue-200">Ventas</p>
            <p class="mt-1 font-display text-3xl font-bold">${{ number_format($grossSales, 2) }}</p>
            <p class="mt-1 text-xs text-blue-200">
                @if ($salesChange === null)
                    Sin ventas en el período anterior
                @else
                    <span class="{{ $salesChange >= 0 ? 'text-green-300' : 'text-red-300' }} font-semibold">
                        {{ $salesChange >= 0 ? '▲' : '▼' }} {{ number_format(abs($salesChange), 1) }}%
                    </span>
                    vs. período anterior (${{ number_format($prevGrossSales, 2) }})
                @endif
            </p>
        </div>

        <div class="rounded-2xl border border-[#E5E5E0] bg-white px-5 py-4 shadow-sm">
            <p class="text-xs uppercase tracking-wide text-slate-500">Guías vendidas</p>
            <p class="mt-1 font-display text-3xl font-bold text-[#111111]">{{ number_format($guides) }}</p>
            <p class="mt-1 text-xs text-slate-500">
                Ticket promedio: <strong class="text-[#111111]">${{ number_format($avgTicket, 2) }}</strong>
            </p>
        </div>

        <div class="rounded-2xl border border-[#E5E5E0] bg-white px-5 py-4 shadow-sm">
            <p class="text-xs uppercase tracking-wide text-slate-500">Gastos</p>
            <p class="mt-1 font-display text-3xl font-bold text-red-600">${{ number_format($expensesTotal, 2) }}</p>
            <p class="mt-1 text-xs text-slate-500">
                Operativos de toda la franquicia ·
                + cuota {{ number_format($feePercentage, 2) }}%: <strong class="text-red-600">${{ number_format($feeAccrued, 2) }}</strong>
            </p>
        </div>

        <div class="rounded-2xl border px-5 py-4 shadow-sm {{ $netProfit >= 0 ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }}">
            <p class="text-xs uppercase tracking-wide {{ $netProfit >= 0 ? 'text-green-700' : 'text-red-700' }}">Utilidad neta</p>
            <p class="mt-1 font-display text-3xl font-bold {{ $netProfit >= 0 ? 'text-green-700' : 'text-red-700' }}">
                {{ $netProfit < 0 ? '-' : '' }}${{ number_format(abs($netProfit), 2) }}
            </p>
            <p class="mt-1 text-xs {{ $netProfit >= 0 ? 'text-green-700' : 'text-red-700' }}">
                @if ($margin !== null)
                    Margen {{ number_format($margin, 1) }}% ·
                @endif
                Anterior: {{ $prevNetProfit < 0 ? '-' : '' }}${{ number_format(abs($prevNetProfit), 2) }}
            </p>
        </div>
    </div>

    @if ($registeredBy !== 'all')
        <p class="-mt-2 text-xs text-slate-500">
            Ventas y guías filtradas por taquilla. Gastos y utilidad siempre se calculan sobre toda la
            franquicia (ventas totales: ${{ number_format($grossAll, 2) }}).
        </p>
    @endif

    {{-- COBRO --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-[#E5E5E0] bg-white px-5 py-4 shadow-sm">
            <p class="text-xs uppercase tracking-wide text-slate-500">Cobrado en taquilla</p>
            <p class="mt-1 font-display text-2xl font-bold text-[#111111]">${{ number_format($counterSales, 2) }}</p>
        </div>
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 shadow-sm">
            <p class="text-xs uppercase tracking-wide text-amber-700">COD por cobrar</p>
            <p class="mt-1 font-display text-2xl font-bold text-amber-800">${{ number_format($codPending, 2) }}</p>
        </div>
        <div class="rounded-2xl border border-[#E5E5E0] bg-white px-5 py-4 shadow-sm">
            <p class="text-xs uppercase tracking-wide text-slate-500">COD liquidado</p>
            <p class="mt-1 font-display text-2xl font-bold text-[#111111]">${{ number_format($codLiquidated, 2) }}</p>
            <p class="mt-1 text-xs text-slate-500">
                {{ $deliveredCount }} entregadas · {{ $returnedCount }} en devolución/devueltas
            </p>
        </div>
    </div>

    {{-- CUOTA DE FRANQUICIA (lo que registra el Admin en Cuentas por cobrar) --}}
    <div class="rounded-2xl border border-[#E5E5E0] bg-white p-4 sm:p-5 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-sm font-semibold text-[#111111]">Cuota de franquicia con ViaYaa</h3>
                <p class="text-xs text-slate-500">
                    {{ number_format($feePercentage, 2) }}% de tus ventas del mes. Los pagos los registra ViaYaa
                    con tu número de factura y de voucher.
                </p>
            </div>
            <span class="self-start rounded-lg px-3 py-1 text-sm font-bold
                {{ $feeBalance > 0 ? 'bg-emerald-50 text-emerald-700' : ($feeBalance < 0 ? 'bg-red-50 text-red-700' : 'bg-slate-100 text-slate-600') }}">
                @if ($feeBalance > 0)
                    A tu favor ${{ number_format($feeBalance, 2) }}
                @elseif ($feeBalance < 0)
                    Pendiente por pagar ${{ number_format(abs($feeBalance), 2) }}
                @else
                    Al día
                @endif
            </span>
        </div>

        @if ($recentFranchisePayments->isNotEmpty())
            <div class="mt-4 overflow-x-auto">
                <table class="w-full min-w-[520px] text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-wide text-slate-400">
                            <th class="py-2 pr-4">Fecha</th>
                            <th class="py-2 pr-4">Factura</th>
                            <th class="py-2 pr-4">Monto factura</th>
                            <th class="py-2 pr-4">Pagado</th>
                            <th class="py-2 pr-4">Referencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentFranchisePayments as $payment)
                            <tr wire:key="fee-payment-{{ $payment->id }}" class="border-b border-slate-100 last:border-0">
                                <td class="py-2 pr-4 text-slate-500">{{ $payment->paid_on?->format('d/m/Y') }}</td>
                                <td class="py-2 pr-4 text-slate-600">{{ $payment->invoice->invoice_number }}</td>
                                <td class="py-2 pr-4 text-slate-600">${{ number_format((float) $payment->invoice->invoice_amount_usd, 2) }}</td>
                                <td class="py-2 pr-4 font-medium text-[#111111]">${{ number_format((float) $payment->amount_paid_usd, 2) }}</td>
                                <td class="py-2 pr-4 text-slate-500">{{ $payment->payment_reference ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- TENDENCIA --}}
    <div class="rounded-2xl border border-[#E5E5E0] bg-white p-4 sm:p-5 shadow-sm">
        <div class="mb-4 flex items-center justify-between gap-3">
            <h3 class="text-sm font-semibold text-[#111111]">Ventas por día</h3>
            <span class="text-xs text-slate-400">Máx. ${{ number_format($trendMax, 2) }}</span>
        </div>

        @if ($grossSales <= 0)
            <p class="py-6 text-center text-sm text-slate-400">No hay ventas en este período.</p>
        @else
            <div class="overflow-x-auto">
                <div class="flex h-40 items-end gap-1" style="min-width: {{ max(100, $trend->count() * 14) }}px">
                    @foreach ($trend as $day)
                        <div class="group relative flex h-full flex-1 flex-col justify-end" wire:key="trend-{{ $loop->index }}">
                            <div
                                class="w-full rounded-t bg-blue-900/80 transition group-hover:bg-amber-400"
                                style="height: {{ $day['total'] > 0 ? max(2, $day['total'] / $trendMax * 100) : 0 }}%"
                                title="{{ $day['label'] }}: ${{ number_format($day['total'], 2) }} ({{ $day['guides'] }} guías)"
                            ></div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-1 flex gap-1 text-[10px] text-slate-400" style="min-width: {{ max(100, $trend->count() * 14) }}px">
                    @foreach ($trend as $day)
                        <div class="flex-1 text-center">
                            @if ($trend->count() <= 10 || $loop->first || $loop->last || $loop->index % 7 === 0)
                                {{ $day['label'] }}
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- VENTAS POR TAQUILLA --}}
    <div class="rounded-2xl border border-[#E5E5E0] bg-white p-4 sm:p-5 shadow-sm">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-semibold text-[#111111]">Ventas por taquilla</h3>
                <p class="text-xs text-slate-500">Rendimiento de cada punto de venta en el período.</p>
            </div>

            <button
                type="button"
                wire:click="exportSales"
                class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50"
            >
                ⬇ Exportar ventas (Excel)
            </button>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="w-full min-w-[640px] text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-400">
                        <th class="px-4 py-2">Taquilla</th>
                        <th class="px-4 py-2">Guías</th>
                        <th class="px-4 py-2">Ventas</th>
                        <th class="px-4 py-2">Ticket prom.</th>
                        <th class="px-4 py-2">Participación</th>
                        <th class="px-4 py-2">COD</th>
                        <th class="px-4 py-2">Última venta</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($staffSummary as $row)
                        <tr
                            wire:key="franchise-staff-{{ $row['user_id'] }}"
                            class="border-b border-slate-100 last:border-0 {{ (string) $row['user_id'] === $registeredBy ? 'bg-blue-50' : '' }}"
                        >
                            <td class="px-4 py-3 font-medium text-[#111111]">
                                {{ $row['name'] }}
                                @if ($row['is_self'])
                                    <span class="text-xs text-slate-400">(Tú)</span>
                                @elseif (! $row['is_active'])
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] text-slate-500">Inactiva</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ $row['guides'] }}</td>
                            <td class="px-4 py-3 font-medium text-[#111111]">${{ number_format($row['total'], 2) }}</td>
                            <td class="px-4 py-3 text-slate-500">${{ number_format($row['avg_ticket'], 2) }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="h-2 w-20 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full bg-amber-400" style="width: {{ min(100, $row['share']) }}%"></div>
                                    </div>
                                    <span class="text-xs text-slate-500">{{ number_format($row['share'], 1) }}%</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ $row['cod_guides'] }}</td>
                            <td class="px-4 py-3 text-xs text-slate-500">
                                {{ $row['last_sale_at']?->format('d/m H:i') ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if ((string) $row['user_id'] === $registeredBy)
                                    <button type="button" wire:click="$set('registeredBy', 'all')"
                                        class="rounded-lg border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                                        Quitar filtro
                                    </button>
                                @else
                                    <button type="button" wire:click="$set('registeredBy', '{{ $row['user_id'] }}')"
                                        class="rounded-lg border border-slate-200 px-3 py-1 text-xs font-semibold text-blue-900 hover:bg-blue-50">
                                        Filtrar
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        {{-- FORMA DE PAGO --}}
        <div class="rounded-2xl border border-[#E5E5E0] bg-white p-4 sm:p-5 shadow-sm">
            <h3 class="mb-3 text-sm font-semibold text-[#111111]">Cobrado en taquilla por forma de pago</h3>

            @if ($byPaymentMethod->isEmpty())
                <p class="py-4 text-sm text-slate-400">Sin ventas cobradas en taquilla en este período.</p>
            @else
                <div class="space-y-3">
                    @foreach ($byPaymentMethod as $row)
                        @php $pct = $counterSales > 0 ? (float) $row->total / $counterSales * 100 : 0; @endphp
                        <div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-medium text-[#111111]">
                                    {{ $row->payment_method
                                        ? (\App\Models\Package::PAYMENT_METHOD_LABELS[$row->payment_method] ?? $row->payment_method)
                                        : 'Sin especificar' }}
                                </span>
                                <span class="text-slate-600">
                                    ${{ number_format((float) $row->total, 2) }}
                                    <span class="text-xs text-slate-400">· {{ $row->guides }} guías</span>
                                </span>
                            </div>
                            <div class="mt-1 h-2 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-blue-900" style="width: {{ min(100, $pct) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- GASTOS POR CATEGORÍA --}}
        <div class="rounded-2xl border border-[#E5E5E0] bg-white p-4 sm:p-5 shadow-sm">
            <h3 class="mb-3 text-sm font-semibold text-[#111111]">Gastos por categoría</h3>

            @if ($expensesByCategory->isEmpty())
                <p class="py-4 text-sm text-slate-400">No has registrado gastos en este período.</p>
            @else
                <div class="space-y-3">
                    @foreach ($expensesByCategory as $row)
                        @php $pct = $expensesTotal > 0 ? (float) $row->total / $expensesTotal * 100 : 0; @endphp
                        <div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-medium text-[#111111]">
                                    {{ \App\Models\FranchiseExpense::CATEGORIES[$row->category] ?? $row->category }}
                                </span>
                                <span class="text-slate-600">
                                    ${{ number_format((float) $row->total, 2) }}
                                    <span class="text-xs text-slate-400">· {{ number_format($pct, 1) }}%</span>
                                </span>
                            </div>
                            <div class="mt-1 h-2 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-red-500" style="width: {{ min(100, $pct) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- GASTOS --}}
    <div class="rounded-2xl border border-[#E5E5E0] bg-white p-4 sm:p-5 shadow-sm">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-semibold text-[#111111]">Gastos operativos</h3>
                <p class="text-xs text-slate-500">Registra alquiler, nómina, servicios, etc. para conocer tu utilidad real.</p>
            </div>

            @if ($expenses->total() > 0)
                <button
                    type="button"
                    wire:click="exportExpenses"
                    class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                >
                    ⬇ Exportar gastos (Excel)
                </button>
            @endif
        </div>

        <form wire:submit="addExpense" class="mb-5 grid grid-cols-1 gap-3 rounded-xl bg-slate-50 p-4 sm:grid-cols-2 lg:grid-cols-5">
            <div>
                <label class="text-xs text-slate-500">Categoría</label>
                <select wire:model="expenseCategory"
                    class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900">
                    @foreach (\App\Models\FranchiseExpense::CATEGORIES as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('expenseCategory') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="lg:col-span-2">
                <label class="text-xs text-slate-500">Descripción</label>
                <input type="text" wire:model="expenseDescription" maxlength="255" placeholder="Ej. Alquiler del local de noviembre"
                    class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900">
                @error('expenseDescription') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="text-xs text-slate-500">Monto (USD)</label>
                <input type="number" step="0.01" min="0.01" wire:model="expenseAmount" placeholder="0.00"
                    class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900">
                @error('expenseAmount') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="text-xs text-slate-500">Fecha</label>
                <input type="date" wire:model="expenseDate" max="{{ now()->toDateString() }}"
                    class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900">
                @error('expenseDate') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-2 lg:col-span-5 flex justify-end">
                <button type="submit" wire:loading.attr="disabled" wire:target="addExpense"
                    class="w-full rounded-xl bg-blue-900 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800 disabled:opacity-60 sm:w-auto">
                    Registrar gasto
                </button>
            </div>
        </form>

        @if ($expenses->isEmpty())
            <p class="py-4 text-sm text-slate-400">No hay gastos registrados en este período.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[560px] text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-wide text-slate-400">
                            <th class="py-2 pr-4">Fecha</th>
                            <th class="py-2 pr-4">Categoría</th>
                            <th class="py-2 pr-4">Descripción</th>
                            <th class="py-2 pr-4 text-right">Monto</th>
                            <th class="py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($expenses as $expense)
                            <tr wire:key="expense-{{ $expense->id }}" class="border-b border-slate-100 last:border-0">
                                <td class="py-3 pr-4 text-slate-500">{{ $expense->spent_on?->format('d/m/Y') }}</td>
                                <td class="py-3 pr-4 text-slate-600">
                                    {{ \App\Models\FranchiseExpense::CATEGORIES[$expense->category] ?? $expense->category }}
                                </td>
                                <td class="py-3 pr-4 text-[#111111]">{{ $expense->description }}</td>
                                <td class="py-3 pr-4 text-right font-medium text-red-600">${{ number_format((float) $expense->amount_usd, 2) }}</td>
                                <td class="py-3 text-right">
                                    <button type="button"
                                        wire:click="deleteExpense({{ $expense->id }})"
                                        wire:confirm="¿Eliminar este gasto?"
                                        class="rounded-lg px-2 py-1 text-xs font-semibold text-red-600 hover:bg-red-50">
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>
</div>
