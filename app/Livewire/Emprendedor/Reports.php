<?php

namespace App\Livewire\Emprendedor;

use App\Livewire\Concerns\ExportsSpreadsheet;
use App\Models\Emprendedor;
use App\Models\Pedido;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Mismo criterio que Admin\Reports / Ally\Reports (tendencias de
 * ventas por período elegido, no una foto fija como Dashboard), pero
 * acotado a los pedidos del propio emprendedor.
 */
#[Layout('layouts.emprendedor')]
#[Title('Reportes')]
class Reports extends Component
{
    use ExportsSpreadsheet;

    public const RANGE_WEEK = '7d';

    public const RANGE_MONTH = '30d';

    public const RANGE_QUARTER = '90d';

    public const RANGE_CUSTOM = 'custom';

    /**
     * Pedido no tiene un STATUS_LABELS como Package: los valores del
     * status ya están en español (PENDIENTE, PAGADO...), así que solo
     * se capitalizan para mostrarlos.
     */
    protected const STATUSES = [
        Pedido::STATUS_PENDIENTE,
        Pedido::STATUS_PAGADO,
        Pedido::STATUS_CONFIRMADO,
        Pedido::STATUS_CANCELADO,
    ];

    public string $dateRange = self::RANGE_MONTH;

    public string $customFrom = '';

    public string $customTo = '';

    protected function emprendedor(): Emprendedor
    {
        return Auth::user()->emprendedor;
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

    protected function confirmedQuery(int $emprendedorId, Carbon $from, Carbon $to)
    {
        return Pedido::query()
            ->where('emprendedor_id', $emprendedorId)
            ->where('status', Pedido::STATUS_CONFIRMADO)
            ->whereBetween('created_at', [$from, $to]);
    }

    public function exportExcel(): BinaryFileResponse
    {
        $emprendedor = $this->emprendedor();
        [$from, $to] = $this->periodBounds();

        $rows = (function () use ($emprendedor, $from, $to) {
            foreach (
                Pedido::query()
                    ->where('emprendedor_id', $emprendedor->id)
                    ->whereBetween('created_at', [$from, $to])
                    ->with('producto')
                    ->cursor() as $pedido
            ) {
                yield [
                    $pedido->id,
                    $pedido->created_at?->format('d/m/Y H:i'),
                    $pedido->cliente_nombre,
                    $pedido->producto?->nombre,
                    $pedido->cantidad,
                    number_format((float) $pedido->precio_total_usd, 2, '.', ''),
                    $pedido->status,
                ];
            }
        })();

        return $this->excelDownload(
            'reporte-ventas-'.$from->format('Y-m-d').'-a-'.$to->format('Y-m-d').'.xlsx',
            ['Pedido', 'Fecha', 'Cliente', 'Producto', 'Cantidad', 'Total USD', 'Estado'],
            $rows,
        );
    }

    public function render()
    {
        $emprendedor = $this->emprendedor();
        [$from, $to] = $this->periodBounds();

        $baseQuery = fn () => Pedido::query()
            ->where('emprendedor_id', $emprendedor->id)
            ->whereBetween('created_at', [$from, $to]);

        $registeredCount = $baseQuery()->count();
        $confirmedCount = $this->confirmedQuery($emprendedor->id, $from, $to)->count();

        $confirmationRate = $registeredCount > 0
            ? round($confirmedCount / $registeredCount * 100)
            : null;

        $salesTotal = $this->confirmedQuery($emprendedor->id, $from, $to)->sum('precio_total_usd');

        $cancelledCount = $baseQuery()->where('status', Pedido::STATUS_CANCELADO)->count();

        // Series diarias para los gráficos.
        $registeredByDay = $baseQuery()
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $confirmedByDay = $this->confirmedQuery($emprendedor->id, $from, $to)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $salesByDay = $this->confirmedQuery($emprendedor->id, $from, $to)
            ->selectRaw('DATE(created_at) as day, SUM(precio_total_usd) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $days = collect();
        for ($cursor = $from->copy()->startOfDay(); $cursor->lte($to); $cursor->addDay()) {
            $days->push($cursor->toDateString());
        }

        $chartLabels = $days->map(fn ($day) => Carbon::parse($day)->format('d/m'))->values();
        $chartRegistered = $days->map(fn ($day) => (int) ($registeredByDay[$day] ?? 0))->values();
        $chartConfirmed = $days->map(fn ($day) => (int) ($confirmedByDay[$day] ?? 0))->values();
        $chartSales = $days->map(fn ($day) => round((float) ($salesByDay[$day] ?? 0), 2))->values();

        // Top 5 productos más vendidos (por unidades) entre los
        // pedidos confirmados del período.
        $topProductos = $this->confirmedQuery($emprendedor->id, $from, $to)
            ->selectRaw('producto_id, SUM(cantidad) as unidades, SUM(precio_total_usd) as total_usd')
            ->groupBy('producto_id')
            ->orderByDesc('unidades')
            ->with('producto')
            ->limit(5)
            ->get();

        // Desglose de pedidos registrados en el período, por estado.
        $statusBreakdown = $baseQuery()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $this->dispatch(
            'emprendedor-reports-updated',
            labels: $chartLabels,
            registered: $chartRegistered,
            confirmed: $chartConfirmed,
            sales: $chartSales,
        );

        return view('livewire.emprendedor.reports', [
            'emprendedor' => $emprendedor,
            'from' => $from,
            'to' => $to,
            'registeredCount' => $registeredCount,
            'confirmedCount' => $confirmedCount,
            'confirmationRate' => $confirmationRate,
            'salesTotal' => $salesTotal,
            'cancelledCount' => $cancelledCount,
            'chartLabels' => $chartLabels,
            'chartRegistered' => $chartRegistered,
            'chartConfirmed' => $chartConfirmed,
            'chartSales' => $chartSales,
            'topProductos' => $topProductos,
            'statusBreakdown' => $statusBreakdown,
            'statuses' => self::STATUSES,
        ]);
    }
}
