<div class="space-y-8 font-sans">

    {{-- =========================================================
         HEADER + FILTROS
    ========================================================== --}}
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="font-display text-3xl font-bold tracking-tight text-[#111111]">Reportes</h1>
            <p class="mt-1 text-sm text-[#6B6B66]">
                Ventas de tu tienda entre
                {{ $from->translatedFormat('d \d\e F') }} y {{ $to->translatedFormat('d \d\e F \d\e Y') }}.
            </p>
        </div>

        <button
            type="button"
            wire:click="exportExcel"
            class="rounded-xl border border-[#E5E5E0] bg-white px-4 py-2.5 text-sm font-semibold text-[#111111] hover:bg-slate-50"
        >
            ⬇ Exportar pedidos del período (Excel)
        </button>
    </div>

    <div class="rounded-2xl border border-[#E5E5E0] bg-white p-5 shadow-sm">
        <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3">
            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-[#6B6B66]">Periodo</label>
                <select wire:model.live="dateRange" class="w-full rounded-xl border border-[#E5E5E0] px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="7d">Última semana</option>
                    <option value="30d">Último mes</option>
                    <option value="90d">Últimos 3 meses</option>
                    <option value="custom">Rango personalizado</option>
                </select>
            </div>

            @if ($dateRange === 'custom')
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-[#6B6B66]">Desde</label>
                    <input type="date" wire:model.live="customFrom"
                           class="w-full rounded-xl border border-[#E5E5E0] px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-[#6B6B66]">Hasta</label>
                    <input type="date" wire:model.live="customTo"
                           class="w-full rounded-xl border border-[#E5E5E0] px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            @endif
        </div>
    </div>


    {{-- =========================================================
         KPIs
    ========================================================== --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-5">

        <div class="bg-white rounded-2xl border border-[#E5E5E0] p-5 shadow-sm">
            <p class="text-xs font-bold text-[#6B6B66] uppercase tracking-wider">Pedidos</p>
            <p class="mt-2 text-2xl font-bold text-[#111111]">{{ $registeredCount }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-[#E5E5E0] p-5 shadow-sm">
            <p class="text-xs font-bold text-[#6B6B66] uppercase tracking-wider">Confirmados</p>
            <p class="mt-2 text-2xl font-bold text-[#111111]">{{ $confirmedCount }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-[#E5E5E0] p-5 shadow-sm">
            <p class="text-xs font-bold text-[#6B6B66] uppercase tracking-wider">Tasa de confirmación</p>
            <p class="mt-2 text-2xl font-bold text-[#111111]">{{ $confirmationRate !== null ? $confirmationRate.'%' : 'N/A' }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-[#E5E5E0] p-5 shadow-sm">
            <p class="text-xs font-bold text-[#6B6B66] uppercase tracking-wider">Ventas (USD)</p>
            <p class="mt-2 text-2xl font-bold text-emerald-600">${{ number_format((float) $salesTotal, 2) }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-[#E5E5E0] p-5 shadow-sm">
            <p class="text-xs font-bold text-[#6B6B66] uppercase tracking-wider">Cancelados</p>
            <p class="mt-2 text-2xl font-bold text-[#111111]">{{ $cancelledCount }}</p>
        </div>

    </div>


    {{-- =========================================================
         GRÁFICOS
    ========================================================== --}}
    <div
        wire:ignore
        x-data="venexpressEmprendedorReportsCharts(@js($chartLabels), @js($chartRegistered), @js($chartConfirmed), @js($chartSales))"
        x-init="init()"
        class="grid grid-cols-1 lg:grid-cols-2 gap-5"
    >
        <div class="bg-white rounded-2xl border border-[#E5E5E0] p-6 shadow-sm">
            <p class="text-xs font-bold text-[#6B6B66] uppercase tracking-wider mb-4">Pedidos por día</p>
            <canvas x-ref="pedidosCanvas" height="220"></canvas>
        </div>

        <div class="bg-white rounded-2xl border border-[#E5E5E0] p-6 shadow-sm">
            <p class="text-xs font-bold text-[#6B6B66] uppercase tracking-wider mb-4">Ventas por día (USD)</p>
            <canvas x-ref="salesCanvas" height="220"></canvas>
        </div>
    </div>


    {{-- =========================================================
         TOP PRODUCTOS
    ========================================================== --}}
    <div class="bg-white rounded-2xl border border-[#E5E5E0] shadow-sm overflow-hidden">
        <div class="p-5 border-b border-[#E5E5E0]">
            <p class="text-xs font-bold text-[#6B6B66] uppercase tracking-wider">Top productos más vendidos</p>
        </div>

        <div class="divide-y divide-[#E5E5E0]">
            @forelse ($topProductos as $fila)
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm font-medium text-[#111111]">{{ $fila->producto?->nombre ?? 'Producto eliminado' }}</span>
                    <span class="text-sm text-[#6B6B66]">
                        {{ $fila->unidades }} unidades
                        <span class="ml-2 font-bold text-[#111111]">${{ number_format((float) $fila->total_usd, 2) }}</span>
                    </span>
                </div>
            @empty
                <p class="px-5 py-6 text-sm text-[#6B6B66]">Sin ventas confirmadas en este período.</p>
            @endforelse
        </div>
    </div>


    {{-- =========================================================
         DESGLOSE POR ESTADO
    ========================================================== --}}
    <div class="bg-white rounded-2xl border border-[#E5E5E0] shadow-sm overflow-hidden">
        <div class="p-5 border-b border-[#E5E5E0]">
            <p class="text-xs font-bold text-[#6B6B66] uppercase tracking-wider">
                Pedidos registrados en el período, por estado
            </p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 divide-x divide-y sm:divide-y-0 divide-[#E5E5E0]">
            @foreach ($statuses as $status)
                <div class="px-5 py-4">
                    <p class="text-xs text-[#6B6B66]">{{ ucfirst(strtolower($status)) }}</p>
                    <p class="mt-1 text-lg font-bold text-[#111111]">{{ $statusBreakdown[$status] ?? 0 }}</p>
                </div>
            @endforeach
        </div>
    </div>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
<script>
    function venexpressEmprendedorReportsCharts(labels, registered, confirmed, sales) {
        return {
            pedidosChart: null,
            salesChart: null,

            init() {
                this.pedidosChart = new Chart(this.$refs.pedidosCanvas.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [
                            {
                                label: 'Pedidos',
                                data: registered,
                                borderColor: '#2563eb',
                                backgroundColor: 'rgba(37, 99, 235, .12)',
                                tension: .3,
                                fill: true,
                            },
                            {
                                label: 'Confirmados',
                                data: confirmed,
                                borderColor: '#16a34a',
                                backgroundColor: 'rgba(22, 163, 74, .12)',
                                tension: .3,
                                fill: true,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { position: 'bottom' } },
                        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
                    },
                });

                this.salesChart = new Chart(this.$refs.salesCanvas.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [{
                            label: 'Ventas (USD)',
                            data: sales,
                            backgroundColor: '#f59e0b',
                            borderRadius: 6,
                        }],
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true } },
                    },
                });

                Livewire.on('emprendedor-reports-updated', ({ labels, registered, confirmed, sales }) => {
                    this.pedidosChart.data.labels = labels;
                    this.pedidosChart.data.datasets[0].data = registered;
                    this.pedidosChart.data.datasets[1].data = confirmed;
                    this.pedidosChart.update();

                    this.salesChart.data.labels = labels;
                    this.salesChart.data.datasets[0].data = sales;
                    this.salesChart.update();
                });
            },
        };
    }
</script>
