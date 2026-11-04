<?php

namespace App\Livewire\Ally;

use App\Livewire\Concerns\ExportsSpreadsheet;
use App\Models\Ally;
use App\Models\FranchiseExpense;
use App\Models\Package;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Finanzas del Franquiciado (role 'franquiciado').
 *
 * A diferencia del Aliado Administrador, el Franquiciado no tiene
 * comisión ni liquidaciones con la empresa (ver Ally::isFranchise()):
 * todo lo que vende es suyo. Por eso, en lugar de "Comisiones y saldo"
 * y "Corte de caja", tiene este panel con control más completo:
 *
 * - Ventas del período elegido, comparadas contra el período anterior.
 * - Ventas por taquilla (participación, ticket promedio, COD).
 * - Ventas por forma de pago y tendencia diaria.
 * - Registro de gastos operativos y utilidad neta (ventas - gastos).
 *
 * Las ventas se cuentan por fecha de registro de la guía, igual que
 * Ally\Dashboard y Ally\SalesCloseout, para que las cifras cuadren
 * entre pantallas.
 */
#[Layout('layouts.ally')]
#[Title('Finanzas')]
class FranchiseFinance extends Component
{
    use ExportsSpreadsheet;
    use WithPagination;

    public const RANGE_TODAY = 'today';

    public const RANGE_WEEK = '7d';

    public const RANGE_MONTH = 'month';

    public const RANGE_30 = '30d';

    public const RANGE_CUSTOM = 'custom';

    public string $dateRange = self::RANGE_MONTH;

    public string $customFrom = '';

    public string $customTo = '';

    /**
     * ID del usuario (dueño o taquilla) a filtrar, o 'all'.
     */
    public string $registeredBy = 'all';

    // Formulario de gasto.
    public string $expenseCategory = 'otro';

    public string $expenseDescription = '';

    public string $expenseAmount = '';

    public string $expenseDate = '';

    public function mount(): void
    {
        $this->ally();
        $this->expenseDate = now()->toDateString();
    }

    protected function ally(): Ally
    {
        $user = Auth::user();
        $ally = $user?->resolveAlly();

        abort_unless(
            $ally && $user->isFranquiciado() && $ally->isFranchise(),
            403,
            'Esta sección es exclusiva del Franquiciado.'
        );

        return $ally;
    }

    public function updatedDateRange(): void
    {
        $this->resetPage('expensesPage');
    }

    public function updatedRegisteredBy(): void
    {
        // Blindaje: solo se puede filtrar por el propio dueño o por una
        // taquilla de ESTA franquicia.
        if ($this->registeredBy !== 'all' && ! $this->staffIds()->contains((int) $this->registeredBy)) {
            $this->registeredBy = 'all';
        }
    }

    /**
     * IDs de quienes pueden registrar guías en esta franquicia: el
     * dueño y todas sus taquillas (activas o no).
     */
    protected function staffIds(): Collection
    {
        return $this->ally()->staffUsers()->pluck('id')->push(Auth::id());
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function periodBounds(): array
    {
        return match ($this->dateRange) {
            self::RANGE_TODAY => [now()->startOfDay(), now()->endOfDay()],
            self::RANGE_WEEK => [now()->subDays(6)->startOfDay(), now()->endOfDay()],
            self::RANGE_30 => [now()->subDays(29)->startOfDay(), now()->endOfDay()],
            self::RANGE_CUSTOM => $this->customBounds(),
            default => [now()->startOfMonth(), now()->endOfDay()],
        };
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function customBounds(): array
    {
        try {
            $from = $this->customFrom !== ''
                ? Carbon::parse($this->customFrom)->startOfDay()
                : now()->startOfMonth();

            $to = $this->customTo !== ''
                ? Carbon::parse($this->customTo)->endOfDay()
                : now()->endOfDay();
        } catch (\Throwable) {
            return [now()->startOfMonth(), now()->endOfDay()];
        }

        return $from->lte($to)
            ? [$from, $to]
            : [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
    }

    /**
     * Período inmediatamente anterior, de la misma duración, para la
     * comparación (ej. este mes vs. los mismos días del mes pasado).
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function previousBounds(Carbon $from, Carbon $to): array
    {
        $days = (int) $from->copy()->startOfDay()->diffInDays($to->copy()->startOfDay()) + 1;

        $prevTo = $from->copy()->subDay()->endOfDay();
        $prevFrom = $prevTo->copy()->subDays($days - 1)->startOfDay();

        return [$prevFrom, $prevTo];
    }

    protected function salesQuery(Carbon $from, Carbon $to, bool $applyStaffFilter = true): Builder
    {
        $query = Package::query()
            ->where('ally_id', $this->ally()->id)
            ->whereBetween('created_at', [$from, $to]);

        if ($applyStaffFilter && $this->registeredBy !== 'all') {
            $query->where('registered_by_user_id', (int) $this->registeredBy);
        }

        return $query;
    }

    protected function expensesQuery(Carbon $from, Carbon $to): Builder
    {
        return FranchiseExpense::query()
            ->where('ally_id', $this->ally()->id)
            ->whereBetween('spent_on', [$from->toDateString(), $to->toDateString()]);
    }

    /*
    |--------------------------------------------------------------------------
    | Gastos
    |--------------------------------------------------------------------------
    */

    public function addExpense(): void
    {
        $ally = $this->ally();

        $validated = $this->validate([
            'expenseCategory' => ['required', Rule::in(array_keys(FranchiseExpense::CATEGORIES))],
            'expenseDescription' => ['required', 'string', 'max:255'],
            'expenseAmount' => ['required', 'numeric', 'min:0.01', 'max:9999999999'],
            'expenseDate' => ['required', 'date', 'before_or_equal:today'],
        ], [
            'expenseDescription.required' => 'Describe el gasto.',
            'expenseAmount.required' => 'Indica el monto.',
            'expenseAmount.min' => 'El monto debe ser mayor que cero.',
            'expenseDate.before_or_equal' => 'La fecha no puede ser futura.',
        ]);

        FranchiseExpense::create([
            'ally_id' => $ally->id,
            'created_by_user_id' => Auth::id(),
            'category' => $validated['expenseCategory'],
            'description' => $validated['expenseDescription'],
            'amount_usd' => round((float) $validated['expenseAmount'], 2),
            'spent_on' => $validated['expenseDate'],
        ]);

        $this->reset(['expenseDescription', 'expenseAmount']);
        $this->expenseCategory = 'otro';
        $this->expenseDate = now()->toDateString();

        session()->flash('success', 'Gasto registrado.');
    }

    public function deleteExpense(int $expenseId): void
    {
        FranchiseExpense::query()
            ->where('ally_id', $this->ally()->id)
            ->whereKey($expenseId)
            ->firstOrFail()
            ->delete();

        session()->flash('success', 'Gasto eliminado.');
    }

    /*
    |--------------------------------------------------------------------------
    | Exportaciones
    |--------------------------------------------------------------------------
    */

    public function exportSales(): BinaryFileResponse
    {
        $ally = $this->ally();
        [$from, $to] = $this->periodBounds();
        $query = $this->salesQuery($from, $to)->with('registeredBy:id,name')->orderBy('created_at');

        $rows = (function () use ($query) {
            foreach ($query->cursor() as $package) {
                yield [
                    $package->tracking_number,
                    $package->created_at?->format('d/m/Y H:i'),
                    $package->registeredBy?->name,
                    $package->is_cod
                        ? 'Contra entrega (COD)'
                        : (Package::PAYMENT_METHOD_LABELS[$package->payment_method] ?? $package->payment_method),
                    Package::STATUS_LABELS[$package->current_status] ?? $package->current_status,
                    number_format((float) $package->total_price_usd, 2, '.', ''),
                ];
            }
        })();

        return $this->excelDownload(
            'ventas-'.Str::slug($ally->business_name).'-'.$from->format('Y-m-d').'-a-'.$to->format('Y-m-d').'.xlsx',
            ['Guía', 'Fecha', 'Registrada por', 'Forma de pago', 'Estado', 'Total USD'],
            $rows,
        );
    }

    public function exportExpenses(): BinaryFileResponse
    {
        $ally = $this->ally();
        [$from, $to] = $this->periodBounds();

        $rows = $this->expensesQuery($from, $to)
            ->orderBy('spent_on')
            ->get()
            ->map(fn (FranchiseExpense $expense) => [
                $expense->spent_on?->format('d/m/Y'),
                FranchiseExpense::CATEGORIES[$expense->category] ?? $expense->category,
                $expense->description,
                number_format((float) $expense->amount_usd, 2, '.', ''),
            ]);

        return $this->excelDownload(
            'gastos-'.Str::slug($ally->business_name).'-'.$from->format('Y-m-d').'-a-'.$to->format('Y-m-d').'.xlsx',
            ['Fecha', 'Categoría', 'Descripción', 'Monto USD'],
            $rows,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    /**
     * Una fila por persona que puede vender en la franquicia (dueño +
     * taquillas), incluidas las que no vendieron nada en el período.
     * No aplica el filtro de taquilla: es la vista comparativa.
     */
    protected function perStaffSummary(Ally $ally, Carbon $from, Carbon $to, float $grossAll): Collection
    {
        $totals = $this->salesQuery($from, $to, applyStaffFilter: false)
            ->whereNotNull('registered_by_user_id')
            ->selectRaw('registered_by_user_id,
                SUM(total_price_usd) as total,
                COUNT(*) as guides,
                SUM(CASE WHEN is_cod THEN 1 ELSE 0 END) as cod_guides,
                MAX(created_at) as last_sale_at')
            ->groupBy('registered_by_user_id')
            ->get()
            ->keyBy('registered_by_user_id');

        $people = collect([Auth::user()])
            ->merge($ally->staffUsers()->orderBy('name')->get());

        return $people->map(function ($person) use ($totals, $grossAll) {
            $row = $totals->get($person->id);
            $total = (float) ($row->total ?? 0);
            $guides = (int) ($row->guides ?? 0);

            return [
                'user_id' => $person->id,
                'name' => $person->name,
                'is_self' => $person->id === Auth::id(),
                'is_active' => $person->isActive(),
                'total' => $total,
                'guides' => $guides,
                'cod_guides' => (int) ($row->cod_guides ?? 0),
                'avg_ticket' => $guides > 0 ? $total / $guides : 0.0,
                'share' => $grossAll > 0 ? $total / $grossAll * 100 : 0.0,
                'last_sale_at' => $row?->last_sale_at ? Carbon::parse($row->last_sale_at) : null,
            ];
        })
            ->sortByDesc('total')
            ->values();
    }

    public function render()
    {
        $ally = $this->ally();
        [$from, $to] = $this->periodBounds();
        [$prevFrom, $prevTo] = $this->previousBounds($from, $to);

        $sales = fn () => $this->salesQuery($from, $to);

        $grossSales = (float) $sales()->sum('total_price_usd');
        $guides = $sales()->count();
        $avgTicket = $guides > 0 ? $grossSales / $guides : 0.0;

        $prevGrossSales = (float) $this->salesQuery($prevFrom, $prevTo)->sum('total_price_usd');
        $salesChange = $prevGrossSales > 0
            ? ($grossSales - $prevGrossSales) / $prevGrossSales * 100
            : null;

        // Cobrado en taquilla vs. contra entrega (COD): el COD lo cobra
        // el repartidor al entregar, así que todavía no está en caja
        // hasta que se liquida.
        $counterSales = (float) $sales()->where('is_cod', false)->sum('total_price_usd');
        $codPending = (float) $sales()->where('is_cod', true)->where('cod_status', Package::COD_PENDIENTE)->sum('cod_amount_usd');
        $codLiquidated = (float) $sales()->where('is_cod', true)->where('cod_status', Package::COD_LIQUIDADO)->sum('cod_amount_usd');

        $returnedCount = $sales()->whereIn('current_status', [Package::STATUS_EN_DEVOLUCION, Package::STATUS_DEVUELTO])->count();
        $deliveredCount = $sales()->where('current_status', Package::STATUS_ENTREGADO)->count();

        $byPaymentMethod = $sales()
            ->where('is_cod', false)
            ->selectRaw('payment_method, SUM(total_price_usd) as total, COUNT(*) as guides')
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get();

        // Tendencia diaria.
        $byDay = $sales()
            ->selectRaw('DATE(created_at) as day, SUM(total_price_usd) as total, COUNT(*) as guides')
            ->groupBy('day')
            ->get()
            ->keyBy(fn ($row) => Carbon::parse($row->day)->toDateString());

        $trend = collect();
        for ($cursor = $from->copy()->startOfDay(); $cursor->lte($to); $cursor->addDay()) {
            $key = $cursor->toDateString();
            $trend->push([
                'label' => $cursor->format('d/m'),
                'total' => (float) ($byDay[$key]->total ?? 0),
                'guides' => (int) ($byDay[$key]->guides ?? 0),
            ]);
        }
        $trendMax = max(1.0, (float) $trend->max('total'));

        // Gastos y utilidad (de toda la franquicia: los gastos no se
        // asignan a una taquilla, así que el filtro de taquilla no los
        // afecta y la utilidad se calcula sobre las ventas totales).
        $grossAll = $this->registeredBy === 'all'
            ? $grossSales
            : (float) $this->salesQuery($from, $to, applyStaffFilter: false)->sum('total_price_usd');

        $expensesTotal = (float) $this->expensesQuery($from, $to)->sum('amount_usd');
        $prevExpensesTotal = (float) $this->expensesQuery($prevFrom, $prevTo)->sum('amount_usd');
        $prevGrossAll = $this->registeredBy === 'all'
            ? $prevGrossSales
            : (float) $this->salesQuery($prevFrom, $prevTo, applyStaffFilter: false)->sum('total_price_usd');

        $netProfit = $grossAll - $expensesTotal;
        $prevNetProfit = $prevGrossAll - $prevExpensesTotal;
        $margin = $grossAll > 0 ? $netProfit / $grossAll * 100 : null;

        $expensesByCategory = $this->expensesQuery($from, $to)
            ->selectRaw('category, SUM(amount_usd) as total, COUNT(*) as items')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $expenses = $this->expensesQuery($from, $to)
            ->with('createdBy:id,name')
            ->orderByDesc('spent_on')
            ->orderByDesc('id')
            ->paginate(10, ['*'], 'expensesPage');

        return view('livewire.ally.franchise-finance', [
            'ally' => $ally,
            'from' => $from,
            'to' => $to,
            'prevFrom' => $prevFrom,
            'prevTo' => $prevTo,
            'staffOptions' => $ally->staffUsers()->orderBy('name')->get(['id', 'name']),
            'grossSales' => $grossSales,
            'guides' => $guides,
            'avgTicket' => $avgTicket,
            'prevGrossSales' => $prevGrossSales,
            'salesChange' => $salesChange,
            'counterSales' => $counterSales,
            'codPending' => $codPending,
            'codLiquidated' => $codLiquidated,
            'returnedCount' => $returnedCount,
            'deliveredCount' => $deliveredCount,
            'byPaymentMethod' => $byPaymentMethod,
            'trend' => $trend,
            'trendMax' => $trendMax,
            'staffSummary' => $this->perStaffSummary($ally, $from, $to, $grossAll),
            'grossAll' => $grossAll,
            'expensesTotal' => $expensesTotal,
            'netProfit' => $netProfit,
            'prevNetProfit' => $prevNetProfit,
            'margin' => $margin,
            'expensesByCategory' => $expensesByCategory,
            'expenses' => $expenses,
        ]);
    }
}
