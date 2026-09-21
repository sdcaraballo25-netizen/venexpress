<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\ExportsSpreadsheet;
use App\Models\Ally;
use App\Models\Driver;
use App\Models\Incident;
use App\Models\Package;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Dashboard de reportes/analítica para el admin: a diferencia de
 * Admin\Dashboard (que muestra el estado actual del sistema, sin
 * filtro de fecha), esto muestra la evolución de paquetes/ingresos en
 * un período elegido, para ver tendencias en vez de una foto fija.
 *
 * Mismo criterio de "fecha de entrega real" que Admin\Dashboard:
 * delivery_completed_at (con updated_at como respaldo para guías
 * entregadas antes de que esa columna existiera).
 */
#[Layout('layouts.admin')]
#[Title('Reportes')]
class Reports extends Component
{
    use ExportsSpreadsheet;

    public const RANGE_WEEK = '7d';

    public const RANGE_MONTH = '30d';

    public const RANGE_QUARTER = '90d';

    public const RANGE_CUSTOM = 'custom';

    protected const DELIVERED_AT_EXPRESSION = 'COALESCE(delivery_completed_at, updated_at)';

    public string $dateRange = self::RANGE_MONTH;

    public string $customFrom = '';

    public string $customTo = '';

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function periodBounds(): array
    {
        if ($this->dateRange === self::RANGE_CUSTOM) {
            $from = $this->customFrom !== ''
                ? Carbon::parse($this->customFrom)->startOfDay()
                : now()->subDays(29)->startOfDay();

            $to = $this->customTo !== ''
                ? Carbon::parse($this->customTo)->endOfDay()
                : now()->endOfDay();

            return $from->lte($to) ? [$from, $to] : [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        $days = match ($this->dateRange) {
            self::RANGE_WEEK => 7,
            self::RANGE_QUARTER => 90,
            default => 30,
        };

        return [now()->subDays($days - 1)->startOfDay(), now()->endOfDay()];
    }

    protected function deliveredQuery(Carbon $from, Carbon $to)
    {
        return Package::query()
            ->where('current_status', Package::STATUS_ENTREGADO)
            ->whereBetween(DB::raw(self::DELIVERED_AT_EXPRESSION), [$from, $to]);
    }

    public function exportExcel(): BinaryFileResponse
    {
        [$from, $to] = $this->periodBounds();

        $rows = (function () use ($from, $to) {
            foreach (
                Package::query()
                    ->whereBetween('created_at', [$from, $to])
                    ->with(['ally', 'driver.user'])
                    ->cursor() as $package
            ) {
                yield [
                    $package->tracking_number,
                    $package->created_at?->format('d/m/Y H:i'),
                    $package->ally?->business_name,
                    $package->driver?->user?->name,
                    Package::STATUS_LABELS[$package->current_status] ?? $package->current_status,
                    number_format((float) $package->total_price_usd, 2, '.', ''),
                    $package->is_cod ? 'Sí' : 'No',
                    $package->delivery_completed_at?->format('d/m/Y H:i'),
                ];
            }
        })();

        return $this->excelDownload(
            'reporte-paquetes-'.$from->format('Y-m-d').'-a-'.$to->format('Y-m-d').'.xlsx',
            ['Guía', 'Fecha de registro', 'Agencia', 'Repartidor', 'Estado', 'Precio USD', 'COD', 'Fecha de entrega'],
            $rows,
        );
    }

    public function render()
    {
        [$from, $to] = $this->periodBounds();

        $registeredCount = Package::query()->whereBetween('created_at', [$from, $to])->count();
        $deliveredCount = $this->deliveredQuery($from, $to)->count();

        $deliveryRate = $registeredCount > 0
            ? round($deliveredCount / $registeredCount * 100)
            : null;

        $revenueTotal = $this->deliveredQuery($from, $to)->sum('total_price_usd');

        // Igual que Admin\Dashboard: la comisión se cuenta por fecha de
        // registro de la guía, no por fecha de entrega.
        $commissionsTotal = Package::query()
            ->whereBetween('created_at', [$from, $to])
            ->sum('commission_amount_usd');

        $incidentsCount = Incident::query()
            ->whereBetween('created_at', [$from, $to])
            ->count();

        // Series diarias para los gráficos.
        $registeredByDay = Package::query()
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $deliveredByDay = $this->deliveredQuery($from, $to)
            ->selectRaw('DATE('.self::DELIVERED_AT_EXPRESSION.') as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $revenueByDay = $this->deliveredQuery($from, $to)
            ->selectRaw('DATE('.self::DELIVERED_AT_EXPRESSION.') as day, SUM(total_price_usd) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $days = collect();
        for ($cursor = $from->copy()->startOfDay(); $cursor->lte($to); $cursor->addDay()) {
            $days->push($cursor->toDateString());
        }

        $chartLabels = $days->map(fn ($day) => Carbon::parse($day)->format('d/m'))->values();
        $chartRegistered = $days->map(fn ($day) => (int) ($registeredByDay[$day] ?? 0))->values();
        $chartDelivered = $days->map(fn ($day) => (int) ($deliveredByDay[$day] ?? 0))->values();
        $chartRevenue = $days->map(fn ($day) => round((float) ($revenueByDay[$day] ?? 0), 2))->values();

        // Top 5 agencias por volumen de paquetes registrados en el período.
        //
        // whereHas() + withCount() (en vez de withCount() + having(),
        // que en SQLite falla con "HAVING clause on a non-aggregate
        // query" porque packages_count viene de una subconsulta
        // escalar, no de un GROUP BY real) para que el filtro "solo
        // agencias con paquetes en el período" funcione igual en
        // SQLite (tests) y MySQL (producción).
        $topAlliesFilter = fn ($query) => $query->whereBetween('created_at', [$from, $to]);

        $topAllies = Ally::query()
            ->whereHas('packages', $topAlliesFilter)
            ->withCount(['packages' => $topAlliesFilter])
            ->orderByDesc('packages_count')
            ->limit(5)
            ->get();

        // Top 5 repartidores por entregas completadas en el período.
        $topDriversFilter = function ($query) use ($from, $to) {
            $query->where('current_status', Package::STATUS_ENTREGADO)
                ->whereBetween(DB::raw(self::DELIVERED_AT_EXPRESSION), [$from, $to]);
        };

        $topDrivers = Driver::query()
            ->with('user')
            ->whereHas('packages', $topDriversFilter)
            ->withCount(['packages' => $topDriversFilter])
            ->orderByDesc('packages_count')
            ->limit(5)
            ->get();

        // Desglose de paquetes registrados en el período, por estado actual.
        $statusBreakdown = Package::query()
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('current_status, COUNT(*) as total')
            ->groupBy('current_status')
            ->pluck('total', 'current_status');

        $this->dispatch(
            'reports-updated',
            labels: $chartLabels,
            registered: $chartRegistered,
            delivered: $chartDelivered,
            revenue: $chartRevenue,
        );

        return view('livewire.admin.reports', [
            'from' => $from,
            'to' => $to,
            'registeredCount' => $registeredCount,
            'deliveredCount' => $deliveredCount,
            'deliveryRate' => $deliveryRate,
            'revenueTotal' => $revenueTotal,
            'commissionsTotal' => $commissionsTotal,
            'incidentsCount' => $incidentsCount,
            'chartLabels' => $chartLabels,
            'chartRegistered' => $chartRegistered,
            'chartDelivered' => $chartDelivered,
            'chartRevenue' => $chartRevenue,
            'topAllies' => $topAllies,
            'topDrivers' => $topDrivers,
            'statusBreakdown' => $statusBreakdown,
            'statuses' => Package::STATUSES,
            'statusLabels' => Package::STATUS_LABELS,
        ]);
    }
}
