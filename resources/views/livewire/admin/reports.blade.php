<div class="min-h-screen space-y-8 font-sans">

    {{-- =========================================================
         HEADER + FILTROS
    ========================================================== --}}
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="font-display text-3xl font-bold tracking-tight text-[#111111]">Reportes</h1>
            <p class="mt-1 text-sm text-[#6B6B66]">
                Tendencias de paquetes, ingresos e incidencias entre
                {{ $from->translatedFormat('d \d\e F') }} y {{ $to->translatedFormat('d \d\e F \d\e Y') }}.
            </p>
        </div>

        <button
            type="button"
            wire:click="exportExcel"
            class="rounded-xl border border-[#E5E5E0] bg-white px-4 py-2.5 text-sm font-semibold text-[#111111] hover:bg-slate-50"
        >
            ⬇ Exportar guías del período (Excel)
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
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-5">

        <div class="bg-white rounded-2xl border border-[#E5E5E0] p-5 shadow-sm">
            <p class="text-xs font-bold text-[#6B6B66] uppercase tracking-wider">Registrados</p>
            <p class="mt-2 text-2xl font-bold text-[#111111]">{{ $registeredCount }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-[#E5E5E0] p-5 shadow-sm">
            <p class="text-xs font-bold text-[#6B6B66] uppercase tracking-wider">Entregados</p>
            <p class="mt-2 text-2xl font-bold text-[#111111]">{{ $deliveredCount }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-[#E5E5E0] p-5 shadow-sm">
            <p class="text-xs font-bold text-[#6B6B66] uppercase tracking-wider">Tasa de entrega</p>
            <p class="mt-2 text-2xl font-bold text-[#111111]">{{ $deliveryRate !== null ? $deliveryRate.'%' : 'N/A' }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-[#E5E5E0] p-5 shadow-sm">
            <p class="text-xs font-bold text-[#6B6B66] uppercase tracking-wider">Ingresos (USD)</p>
            <p class="mt-2 text-2xl font-bold text-emerald-600">${{ number_format((float) $revenueTotal, 2) }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-[#E5E5E0] p-5 shadow-sm">
            <p class="text-xs font-bold text-[#6B6B66] uppercase tracking-wider">Comisiones (USD)</p>
            <p class="mt-2 text-2xl font-bold text-[#111111]">${{ number_format((float) $commissionsTotal, 2) }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-[#E5E5E0] p-5 shadow-sm">
            <p class="text-xs font-bold text-[#6B6B66] uppercase tracking-wider">Incidencias</p>
            <p class="mt-2 text-2xl font-bold text-[#111111]">{{ $incidentsCount }}</p>
        </div>

    </div>


    {{-- =========================================================
         GRÁFICOS
    ========================================================== --}}
    <div
        wire:ignore
        x-data="venexpressReportsCharts(@js($chartLabels), @js($chartRegistered), @js($chartDelivered), @js($chartRevenue))"
        x-init="init()"
        class="grid grid-cols-1 lg:grid-cols-2 gap-5"
    >
        <div class="bg-white rounded-2xl border border-[#E5E5E0] p-6 shadow-sm">
            <p class="text-xs font-bold text-[#6B6B66] uppercase tracking-wider mb-4">Paquetes por día</p>
            <canvas x-ref="packagesCanvas" height="220"></canvas>
        </div>

        <div class="bg-white rounded-2xl border border-[#E5E5E0] p-6 shadow-sm">
            <p class="text-xs font-bold text-[#6B6B66] uppercase tracking-wider mb-4">Ingresos por día (USD)</p>
            <canvas x-ref="revenueCanvas" height="220"></canvas>
        </div>
    </div>


    {{-- =========================================================
         TABLAS
    ========================================================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- Top agencias --}}
        <div class="bg-white rounded-2xl border border-[#E5E5E0] shadow-sm overflow-hidden">
            <div class="p-5 border-b border-[#E5E5E0]">
                <p class="text-xs font-bold text-[#6B6B66] uppercase tracking-wider">Top agencias por volumen</p>
            </div>

            <div class="divide-y divide-[#E5E5E0]">
                @forelse ($topAllies as $ally)
                    <div class="flex items-center justify-between px-5 py-3">
                        <span class="text-sm font-medium text-[#111111]">{{ $ally->business_name }}</span>
                        <span class="text-sm font-bold text-[#111111]">{{ $ally->packages_count }}</span>
                    </div>
                @empty
                    <p class="px-5 py-6 text-sm text-[#6B6B66]">Sin paquetes registrados en este período.</p>
                @endforelse
            </div>
        </div>

        {{-- Top repartidores --}}
        <div class="bg-white rounded-2xl border border-[#E5E5E0] shadow-sm overflow-hidden">
            <div class="p-5 border-b border-[#E5E5E0]">
                <p class="text-xs font-bold text-[#6B6B66] uppercase tracking-wider">Top repartidores por entregas</p>
            </div>

            <div class="divide-y divide-[#E5E5E0]">
                @forelse ($topDrivers as $driver)
                    <div class="flex items-center justify-between px-5 py-3">
                        <span class="text-sm font-medium text-[#111111]">{{ $driver->user?->name }}</span>
                        <span class="text-sm font-bold text-[#111111]">{{ $driver->packages_count }}</span>
                    </div>
                @empty
                    <p class="px-5 py-6 text-sm text-[#6B6B66]">Sin entregas completadas en este período.</p>
                @endforelse
            </div>
        </div>

    </div>


    {{-- =========================================================
         DESGLOSE POR ESTADO
    ========================================================== --}}
    <div class="bg-white rounded-2xl border border-[#E5E5E0] shadow-sm overflow-hidden">
        <div class="p-5 border-b border-[#E5E5E0]">
            <p class="text-xs font-bold text-[#6B6B66] uppercase tracking-wider">
                Paquetes registrados en el período, por estado actual
            </p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 divide-x divide-y sm:divide-y-0 divide-[#E5E5E0]">
            @foreach ($statuses as $status)
                <div class="px-5 py-4">
                    <p class="text-xs text-[#6B6B66]">{{ $statusLabels[$status] ?? $status }}</p>
                    <p class="mt-1 text-lg font-bold text-[#111111]">{{ $statusBreakdown[$status] ?? 0 }}</p>
                </div>
            @endforeach
        </div>
    </div>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
<script>
    function venexpressReportsCharts(labels, registered, delivered, revenue) {
        return {
            packagesChart: null,
            revenueChart: null,

            init() {
                this.packagesChart = new Chart(this.$refs.packagesCanvas.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [
                            {
                                label: 'Registrados',
                                data: registered,
                                borderColor: '#2563eb',
                                backgroundColor: 'rgba(37, 99, 235, .12)',
                                tension: .3,
                                fill: true,
                            },
                            {
                                label: 'Entregados',
                                data: delivered,
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

                this.revenueChart = new Chart(this.$refs.revenueCanvas.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [{
                            label: 'Ingresos (USD)',
                            data: revenue,
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

                Livewire.on('reports-updated', ({ labels, registered, delivered, revenue }) => {
                    this.packagesChart.data.labels = labels;
                    this.packagesChart.data.datasets[0].data = registered;
                    this.packagesChart.data.datasets[1].data = delivered;
                    this.packagesChart.update();

                    this.revenueChart.data.labels = labels;
                    this.revenueChart.data.datasets[0].data = revenue;
                    this.revenueChart.update();
                });
            },
        };
    }
</script>
