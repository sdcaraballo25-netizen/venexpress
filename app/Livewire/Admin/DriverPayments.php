<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\ExportsSpreadsheet;
use App\Models\Driver;
use App\Models\DriverPayment;
use App\Services\DriverPaymentService;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Remuneración de repartidores: en vez de una lista plana de pagos
 * individuales (uno por cada guía entregada), muestra un resumen tipo
 * nómina — cuánto se le debe en total a cada repartidor — con el
 * detalle de guías expandible por si hace falta cancelar una puntual.
 */
#[Layout('layouts.admin')]
class DriverPayments extends Component
{
    use ExportsSpreadsheet;
    use WithPagination;

    public string $status = 'pendiente';

    public string $search = '';

    /**
     * Repartidor cuyo detalle de guías está expandido, o null si
     * ninguno.
     */
    public ?int $expandedDriverId = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function toggleDriver(int $driverId): void
    {
        $this->expandedDriverId = $this->expandedDriverId === $driverId ? null : $driverId;
    }

    public function markPaid(int $id): void {
        try { app(DriverPaymentService::class)->markPaid(DriverPayment::findOrFail($id),(int)auth()->id()); session()->flash('success','Remuneración marcada como pagada.'); }
        catch(RuntimeException $e){ session()->flash('error',$e->getMessage()); }
    }
    public function cancelPayment(int $id): void {
        try { app(DriverPaymentService::class)->cancel(DriverPayment::findOrFail($id),(int)auth()->id()); session()->flash('success','Remuneración cancelada.'); }
        catch(RuntimeException $e){ session()->flash('error',$e->getMessage()); }
    }

    /**
     * Paga de una vez todo lo pendiente de un repartidor (la acción
     * "nómina" de esta pantalla).
     */
    public function markAllPaidForDriver(int $driverId): void
    {
        try {
            $count = app(DriverPaymentService::class)->markAllPaidForDriver($driverId, (int) auth()->id());
            session()->flash('success', "Se marcaron {$count} remuneración(es) como pagadas.");
        } catch (RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    /**
     * Mismo filtro (estatus + búsqueda) para el resumen, el detalle
     * expandido y la exportación — así los tres siempre coinciden.
     */
    protected function filteredPayments(): Builder
    {
        $q = DriverPayment::query();

        if ($this->status !== 'all') {
            $q->where('status', $this->status);
        }

        if (trim($this->search) !== '') {
            $s = trim($this->search);
            $q->where(function ($x) use ($s) {
                $x->whereHas('package', fn ($p) => $p->where('tracking_number', 'like', "%$s%"))
                    ->orWhereHas('driver.user', fn ($u) => $u->where('name', 'like', "%$s%"));
            });
        }

        return $q;
    }

    /**
     * Un renglón por repartidor: cuánto se le debe en total (según el
     * filtro de estatus elegido) y en cuántas guías.
     */
    protected function summaryQuery(): Builder
    {
        return $this->filteredPayments()
            ->select('driver_id')
            ->selectRaw('SUM(amount_usd) as total_usd, COUNT(*) as payments_count')
            ->groupBy('driver_id')
            ->with('driver.user')
            ->orderByDesc('total_usd');
    }

    public function exportExcel(): BinaryFileResponse
    {
        $rows = $this->summaryQuery()
            ->get()
            ->map(fn ($row) => [
                $row->driver?->user?->name,
                $row->payments_count,
                number_format((float) $row->total_usd, 2, '.', ''),
            ]);

        return $this->excelDownload(
            'remuneraciones-'.now()->format('Y-m-d').'.xlsx',
            ['Repartidor', 'Guías', 'Total USD'],
            $rows,
        );
    }

    public function render(){
        $summary = $this->summaryQuery()->paginate(15);

        $expandedPayments = $this->expandedDriverId
            ? $this->filteredPayments()
                ->where('driver_id', $this->expandedDriverId)
                ->with('package')
                ->latest()
                ->get()
            : collect();

        return view('livewire.admin.driver-payments', [
            'summary' => $summary,
            'expandedPayments' => $expandedPayments,
        ]);
    }
}
