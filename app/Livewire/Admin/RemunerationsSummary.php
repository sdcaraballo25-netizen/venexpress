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
 * Resumen de todo lo que el negocio debe pagar hoy, en dos tablas
 * separadas: Aliados (comisión, vía AllyFinancialService::getBalance())
 * y Repartidores (una remuneración fija por paquete entregado, vía
 * DriverPayment pendientes) — son dos negocios distintos (porcentaje
 * de venta vs. tarifa fija) y no deben mezclarse en una sola tabla.
 * AllyFinance solo muestra un aliado a la vez y DriverPayments solo
 * cubre repartidores, así que tampoco hay hoy una vista de conjunto
 * de cada uno.
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

    protected function currentBcvRate(): ?BcvRate
    {
        return BcvRate::current();
    }

    protected function toVesConverter(): \Closure
    {
        $bcvRateService = app(BcvRateService::class);
        $rate = $this->currentBcvRate();

        return fn (float $usd): ?float => $rate
            ? $bcvRateService->convertUsdToVes($usd, $rate)
            : null;
    }

    /**
     * Un renglón por Aliado con saldo de comisión pendiente. "Paquetes"/
     * "producido" son el histórico completo de guías que registró: no
     * hay, en el modelo actual, un vínculo 1-a-1 entre una liquidación
     * y las guías puntuales que la generaron — el saldo es un balance
     * de libro mayor, no una asignación por guía. El "saldo a pagar" sí
     * es siempre la cifra que realmente se le debe hoy.
     *
     * @return Collection<int, array{
     *   doc: ?string, name: ?string, email: ?string, account_number: ?string,
     *   holder_id: ?string, packages: int, produced_usd: float,
     *   balance_usd: float, balance_ves: ?float,
     * }>
     */
    protected function allyRows(): Collection
    {
        $allyFinancialService = app(AllyFinancialService::class);
        $toVes = $this->toVesConverter();

        return Ally::query()
            ->where('status', Ally::STATUS_ACTIVE)
            ->with('user')
            ->get()
            ->map(function (Ally $ally) use ($allyFinancialService, $toVes) {
                $balanceUsd = $allyFinancialService->getBalance($ally->id);

                if ($balanceUsd <= 0) {
                    return null;
                }

                return [
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
            ->filter()
            ->values();
    }

    /**
     * Un renglón por Repartidor con remuneraciones pendientes — la
     * tarifa fija por paquete entregado (DriverRemunerationRate), no
     * un porcentaje como el Aliado. Aquí "paquetes"/"producido" sí son
     * exactamente las guías detrás de los pagos pendientes, porque
     * DriverPayment sí tiene un estado por guía.
     *
     * @return Collection<int, array{
     *   doc: ?string, name: ?string, email: ?string, account_number: ?string,
     *   holder_id: ?string, packages: int, produced_usd: float,
     *   balance_usd: float, balance_ves: ?float,
     * }>
     */
    protected function driverRows(): Collection
    {
        $toVes = $this->toVesConverter();

        return Driver::query()
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
            ->filter()
            ->values();
    }

    protected function exportRowsFor(Collection $rows): Collection
    {
        $exportRows = $rows->map(fn (array $row) => [
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
        $totalVes = $rows->isNotEmpty() && $rows->every(fn (array $row) => $row['balance_ves'] !== null)
            ? $rows->sum('balance_ves')
            : null;

        $exportRows->push(['', '', '', '', '', '', '', '', '']);
        $exportRows->push([
            'TOTAL A PAGAR', '', '', '', '', '', '',
            number_format($totalUsd, 2, '.', ''),
            $totalVes !== null ? number_format($totalVes, 2, '.', '') : 'N/A',
        ]);

        return $exportRows;
    }

    protected function exportHeadings(): array
    {
        return [
            'RIF o Cédula', 'Nombre', 'Correo', 'Número de cuenta',
            'Cédula del titular', 'Paquetes', 'Producido USD', 'Saldo USD a pagar', 'Saldo Bs a pagar',
        ];
    }

    public function exportAlliesExcel(): BinaryFileResponse
    {
        return $this->excelDownload(
            'pagos-aliados-'.now()->format('Y-m-d').'.xlsx',
            $this->exportHeadings(),
            $this->exportRowsFor($this->allyRows()),
        );
    }

    public function exportDriversExcel(): BinaryFileResponse
    {
        return $this->excelDownload(
            'pagos-repartidores-'.now()->format('Y-m-d').'.xlsx',
            $this->exportHeadings(),
            $this->exportRowsFor($this->driverRows()),
        );
    }

    public function render()
    {
        $allyRows = $this->allyRows();
        $driverRows = $this->driverRows();

        $sumVesOrNull = function (Collection $rows) {
            if ($rows->isEmpty() || ! $rows->every(fn (array $row) => $row['balance_ves'] !== null)) {
                return null;
            }

            return $rows->sum('balance_ves');
        };

        return view('livewire.admin.remunerations-summary', [
            'allyRows' => $allyRows,
            'driverRows' => $driverRows,
            'allyTotalUsd' => $allyRows->sum('balance_usd'),
            'allyTotalVes' => $sumVesOrNull($allyRows),
            'allyTotalProducedUsd' => $allyRows->sum('produced_usd'),
            'driverTotalUsd' => $driverRows->sum('balance_usd'),
            'driverTotalVes' => $sumVesOrNull($driverRows),
            'bcvRate' => $this->currentBcvRate(),
        ]);
    }
}
