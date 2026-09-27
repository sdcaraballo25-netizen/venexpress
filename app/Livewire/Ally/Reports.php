<?php

namespace App\Livewire\Ally;

use App\Livewire\Concerns\ExportsSpreadsheet;
use App\Models\Ally;
use App\Models\Driver;
use App\Models\Incident;
use App\Models\Package;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Mismo criterio que Admin\Reports (tendencias por período elegido,
 * no una foto fija como Ally\Dashboard), pero acotado a las guías de
 * la propia agencia — el aliado no ve datos de otras agencias.
 */
#[Layout('layouts.ally')]
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

    public function mount(): void
    {
        $this->ally();
    }

    protected function ally(): Ally
    {
        $ally = Auth::user()->resolveAlly();

        abort_unless($ally, 403, 'Tu usuario no tiene una agencia aliada asociada.');

        return $ally;
    }

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

    protected function deliveredQuery(int $allyId, Carbon $from, Carbon $to)
    {
        return Package::query()
            ->where('ally_id', $allyId)
            ->where('current_status', Package::STATUS_ENTREGADO)
            ->whereBetween(DB::raw(self::DELIVERED_AT_EXPRESSION), [$from, $to]);
    }

    public function exportExcel(): BinaryFileResponse
    {
        $ally = $this->ally();
        [$from, $to] = $this->periodBounds();

        $rows = (function () use ($ally, $from, $to) {
            foreach (
                Package::query()
                    ->where('ally_id', $ally->id)
                    ->whereBetween('created_at', [$from, $to])
                    ->with('driver.user')
                    ->cursor() as $package
            ) {
                yield [
                    $package->tracking_number,
                    $package->created_at?->format('d/m/Y H:i'),
                    $package->driver?->user?->name,
                    Package::STATUS_LABELS[$package->current_status] ?? $package->current_status,
                    number_format((float) $package->total_price_usd, 2, '.', ''),
                    $package->is_cod ? 'Sí' : 'No',
                    $package->delivery_completed_at?->format('d/m/Y H:i'),
                ];
            }
        })();

        return $this->excelDownload(
            'reporte-'.Str::slug($ally->business_name).'-'.$from->format('Y-m-d').'-a-'.$to->format('Y-m-d').'.xlsx',
            ['Guía', 'Fecha de registro', 'Repartidor', 'Estado', 'Precio USD', 'COD', 'Fecha de entrega'],
            $rows,
        );
    }

    public function render()
    {
        $ally = $this->ally();
        [$from, $to] = $this->periodBounds();

        $baseQuery = fn () => Package::query()
            ->where('ally_id', $ally->id)
            ->whereBetween('created_at', [$from, $to]);

        $registeredCount = $baseQuery()->count();
        $deliveredCount = $this->deliveredQuery($ally->id, $from, $to)->count();

        $deliveryRate = $registeredCount > 0
            ? round($deliveredCount / $registeredCount * 100)
            : null;

        $revenueTotal = $this->deliveredQuery($ally->id, $from, $to)->sum('total_price_usd');

        // Igual que Admin\Reports: la comisión se cuenta por fecha de
        // registro de la guía, no por fecha de entrega.
        $commissionsTotal = $baseQuery()->sum('commission_amount_usd');

        $incidentsCount = Incident::query()
            ->where('ally_id', $ally->id)
            ->whereBetween('created_at', [$from, $to])
            ->count();

        // Series diarias para los gráficos.
        $registeredByDay = $baseQuery()
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $deliveredByDay = $this->deliveredQuery($ally->id, $from, $to)
            ->selectRaw('DATE('.self::DELIVERED_AT_EXPRESSION.') as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $revenueByDay = $this->deliveredQuery($ally->id, $from, $to)
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

        // Top 5 repartidores por entregas completadas en el período
        // (solo de guías de esta agencia).
        $topDriversFilter = function ($query) use ($ally, $from, $to) {
            $query->where('ally_id', $ally->id)
                ->where('current_status', Package::STATUS_ENTREGADO)
                ->whereBetween(DB::raw(self::DELIVERED_AT_EXPRESSION), [$from, $to]);
        };

        $topDrivers = Driver::query()
            ->with('user')
            ->whereHas('packages', $topDriversFilter)
            ->withCount(['packages' => $topDriversFilter])
            ->orderByDesc('packages_count')
            ->limit(5)
            ->get();

        // Top 5 ciudades de destino por volumen de guías registradas.
        $topCities = $baseQuery()
            ->selectRaw('destination_city, destination_state, COUNT(*) as total')
            ->groupBy('destination_city', 'destination_state')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Desglose de guías registradas en el período, por estado actual.
        $statusBreakdown = $baseQuery()
            ->selectRaw('current_status, COUNT(*) as total')
            ->groupBy('current_status')
            ->pluck('total', 'current_status');

        $this->dispatch(
            'ally-reports-updated',
            labels: $chartLabels,
            registered: $chartRegistered,
            delivered: $chartDelivered,
            revenue: $chartRevenue,
        );

        return view('livewire.ally.reports', [
            'ally' => $ally,
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
            'topDrivers' => $topDrivers,
            'topCities' => $topCities,
            'statusBreakdown' => $statusBreakdown,
            'statuses' => Package::STATUSES,
            'statusLabels' => Package::STATUS_LABELS,
        ]);
    }
}
