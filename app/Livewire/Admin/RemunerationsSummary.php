<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\ExportsSpreadsheet;
use App\Models\Ally;
use App\Models\BcvRate;
use App\Models\Driver;
use App\Models\DriverPayment;
use App\Services\AllyFinancialService;
use App\Services\BcvRateService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Resumen consolidado de todo lo que el negocio debe pagar hoy, tanto
 * a Aliados (comisión, vía AllyFinancialService::getBalance()) como a
 * Repartidores (remuneración por entrega, vía DriverPayment pendientes)
 * — juntos en una sola tabla, porque AllyFinance solo muestra un
 * aliado a la vez y DriverPayments solo cubre repartidores.
 *
 * No persiste nada nuevo: "sincronizar" es simplemente recalcular esta
 * vista con los datos más recientes (los saldos ya se calculan al
 * vuelo desde la base de datos en cada render).
 */
#[Layout('layouts.admin')]
class RemunerationsSummary extends Component
{
    use ExportsSpreadsheet;

    public ?string $syncedAt = null;

    public function mount(): void
    {
        $this->syncedAt = now()->toDateTimeString();
    }

    public function sync(): void
    {
        $this->syncedAt = now()->toDateTimeString();

        session()->flash('success', 'Resumen sincronizado con los datos más recientes.');
    }

    /**
     * Un renglón por Aliado con saldo pendiente y por Repartidor con
     * remuneraciones pendientes, en el mismo formato para poder
     * exportarse juntos.
     *
     * "Paquetes"/"producido" reflejan actividad distinta según el rol:
     * para el Aliado es el histórico completo de guías que registró
     * (no hay, en el modelo actual, un vínculo 1-a-1 entre una
     * liquidación y las guías puntuales que la generaron — el saldo es
     * un balance de libro mayor, no una asignación por guía); para el
     * Repartidor son exactamente las guías detrás de sus pagos
     * pendientes (eso sí tiene un estado por guía). El "saldo a pagar"
     * es siempre la cifra que realmente se le debe hoy.
     *
     * @return Collection<int, array{
     *   role: string, doc: ?string, name: ?string, email: ?string,
     *   account_number: ?string, holder_id: ?string, packages: int,
     *   produced_usd: float, balance_usd: float, balance_ves: ?float,
     * }>
     */
    protected function rows(): Collection
    {
        $allyFinancialService = app(AllyFinancialService::class);
        $bcvRateService = app(BcvRateService::class);
        $rate = BcvRate::current();

        $toVes = fn (float $usd): ?float => $rate
            ? $bcvRateService->convertUsdToVes($usd, $rate)
            : null;

        $allyRows = Ally::query()
            ->where('status', Ally::STATUS_ACTIVE)
            ->with('user')
            ->get()
            ->map(function (Ally $ally) use ($allyFinancialService, $toVes) {
                $balanceUsd = $allyFinancialService->getBalance($ally->id);

                if ($balanceUsd <= 0) {
                    return null;
                }

                return [
                    'role' => 'Aliado',
                    'doc' => $ally->rif,
                    'name' => $ally->business_name,
                    'email' => $ally->user?->email,
                    'account_number' => $ally->bank_account_number,
                    'holder_id' => $ally->bank_account_holder_id,
                    'packages' => $ally->packages()->count(),
                    'produced_usd' => (float) $ally->packages()->sum('total_price_usd'),
                    'balance_usd' => $balanceUsd,
                    'balance_ves' => $toVes($balanceUsd),
                ];
            })
            ->filter();

        $driverRows = Driver::query()
            ->where('status', Driver::STATUS_ACTIVE)
            ->with('user')
            ->get()
            ->map(function (Driver $driver) use ($toVes) {
                $pendingPayments = DriverPayment::query()
                    ->where('driver_id', $driver->id)
                    ->where('status', DriverPayment::STATUS_PENDING)
                    ->with('package')
                    ->get();

                if ($pendingPayments->isEmpty()) {
                    return null;
                }

                $balanceUsd = (float) $pendingPayments->sum('amount_usd');

                return [
                    'role' => 'Repartidor',
                    'doc' => $driver->cedula,
                    'name' => $driver->user?->name,
                    'email' => $driver->user?->email,
                    'account_number' => $driver->bank_account_number,
                    'holder_id' => $driver->bank_account_holder_id,
                    'packages' => $pendingPayments->count(),
                    'produced_usd' => (float) $pendingPayments->sum(
                        fn (DriverPayment $payment) => (float) ($payment->package?->total_price_usd ?? 0)
                    ),
                    'balance_usd' => $balanceUsd,
                    'balance_ves' => $toVes($balanceUsd),
                ];
            })
            ->filter();

        return $allyRows->concat($driverRows)->values();
    }

    public function exportExcel(): BinaryFileResponse
    {
        $rows = $this->rows();

        $exportRows = $rows->map(fn (array $row) => [
            $row['role'],
            $row['doc'],
            $row['name'],
            $row['email'],
            $row['account_number'],
            $row['holder_id'],
            $row['packages'],
            number_format($row['produced_usd'], 2, '.', ''),
            number_format($row['balance_usd'], 2, '.', ''),
            $row['balance_ves'] !== null ? number_format($row['balance_ves'], 2, '.', '') : 'N/A',
        ]);

        $totalUsd = $rows->sum('balance_usd');
        $totalVes = $rows->every(fn (array $row) => $row['balance_ves'] !== null)
            ? $rows->sum('balance_ves')
            : null;

        $totalProducedByAllies = $rows
            ->where('role', 'Aliado')
            ->sum('produced_usd');

        $exportRows->push(['', '', '', '', '', '', '', '', '', '']);
        $exportRows->push([
            'TOTAL A PAGAR', '', '', '', '', '', '',
            '',
            number_format($totalUsd, 2, '.', ''),
            $totalVes !== null ? number_format($totalVes, 2, '.', '') : 'N/A',
        ]);
        $exportRows->push([
            'TOTAL PRODUCIDO POR ALIADOS (USD)', '', '', '', '', '', '',
            number_format($totalProducedByAllies, 2, '.', ''),
            '', '',
        ]);

        return $this->excelDownload(
            'resumen-pagos-'.now()->format('Y-m-d').'.xlsx',
            [
                'Rol', 'RIF o Cédula', 'Nombre', 'Correo', 'Número de cuenta',
                'Cédula del titular', 'Paquetes', 'Producido USD', 'Saldo USD a pagar', 'Saldo Bs a pagar',
            ],
            $exportRows,
        );
    }

    public function render()
    {
        $rows = $this->rows();

        return view('livewire.admin.remunerations-summary', [
            'rows' => $rows,
            'totalUsd' => $rows->sum('balance_usd'),
            'totalVes' => $rows->every(fn (array $row) => $row['balance_ves'] !== null)
                ? $rows->sum('balance_ves')
                : null,
            'totalProducedByAllies' => $rows->where('role', 'Aliado')->sum('produced_usd'),
            'bcvRate' => BcvRate::current(),
        ]);
    }
}
