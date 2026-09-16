<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\ExportsSpreadsheet;
use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Bitácora de auditoría: muestra los AuditLog generados por acciones
 * administrativas sensibles (creación/edición/borrado de usuarios,
 * cambios de estado, etc.). Solo lectura.
 *
 * No incluye las acciones de AuditLog::NOISY_CLIENT_ACTIONS (aceptar/
 * rechazar entrega a domicilio desde el panel de Cliente): son
 * acciones del cliente, no administrativas, y pasan una vez por cada
 * paquete con entrega a domicilio — con el tiempo terminan siendo la
 * mayoría de las filas y ahogan lo que sí hay que revisar aquí.
 */
#[Layout('layouts.admin')]
#[Title('Bitácora de Auditoría')]
class AuditLogViewer extends Component
{
    use ExportsSpreadsheet;
    use WithPagination;

    public const RANGE_3_DAYS = '3d';

    public const RANGE_WEEK = '7d';

    public const RANGE_MONTH = '30d';

    public const RANGE_6_MONTHS = '6m';

    public const RANGE_CUSTOM = 'custom';

    public const RANGE_ALL = 'all';

    public string $search = '';

    public string $actionFilter = '';

    public string $dateRange = self::RANGE_ALL;

    public string $customFrom = '';

    public string $customTo = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingActionFilter(): void
    {
        $this->resetPage();
    }

    public function updatingDateRange(): void
    {
        $this->resetPage();
    }

    public function updatingCustomFrom(): void
    {
        $this->resetPage();
    }

    public function updatingCustomTo(): void
    {
        $this->resetPage();
    }

    /**
     * Mismo filtro (búsqueda + acción + rango de fecha) que usan
     * render() y exportExcel().
     */
    protected function baseQuery(): Builder
    {
        return AuditLog::query()
            ->with('actor')
            ->whereNotIn('action', AuditLog::NOISY_CLIENT_ACTIONS)
            ->when($this->search !== '', function ($query) {
                $term = '%' . $this->search . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('description', 'like', $term)
                        ->orWhereHas('actor', fn ($a) => $a->where('name', 'like', $term));
                });
            })
            ->when($this->actionFilter !== '', fn ($query) => $query->where('action', $this->actionFilter))
            ->when(...$this->dateRangeConstraint())
            ->latest();
    }

    /**
     * Traduce el preset elegido (o el rango personalizado) a los
     * argumentos de un ->when(), para no repetir el cálculo de fechas
     * en dos sitios. Devuelve [condición, callback] listo para spread.
     *
     * @return array{0: bool, 1: \Closure}
     */
    protected function dateRangeConstraint(): array
    {
        if ($this->dateRange === self::RANGE_CUSTOM) {
            return [
                $this->customFrom !== '' || $this->customTo !== '',
                function ($query) {
                    if ($this->customFrom !== '') {
                        $query->whereDate('created_at', '>=', $this->customFrom);
                    }

                    if ($this->customTo !== '') {
                        $query->whereDate('created_at', '<=', $this->customTo);
                    }
                },
            ];
        }

        $days = match ($this->dateRange) {
            self::RANGE_3_DAYS => 3,
            self::RANGE_WEEK => 7,
            self::RANGE_MONTH => 30,
            self::RANGE_6_MONTHS => 182,
            default => null,
        };

        return [
            $days !== null,
            fn ($query) => $query->where('created_at', '>=', now()->subDays($days ?? 0)),
        ];
    }

    public function exportExcel(): BinaryFileResponse
    {
        $rows = (function () {
            foreach ($this->baseQuery()->cursor() as $log) {
                yield [
                    $log->created_at?->format('d/m/Y'),
                    $log->created_at?->format('H:i'),
                    $log->actor?->name ?? 'Sistema',
                    $log->actionLabel(),
                    $log->description,
                    $log->ip_address,
                ];
            }
        })();

        return $this->excelDownload(
            'bitacora-'.now()->format('Y-m-d').'.xlsx',
            ['Fecha', 'Hora', 'Usuario', 'Acción', 'Descripción', 'IP'],
            $rows,
        );
    }

    public function render()
    {
        $logs = $this->baseQuery()->paginate(20);

        $actions = AuditLog::query()
            ->whereNotIn('action', AuditLog::NOISY_CLIENT_ACTIONS)
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view('livewire.admin.audit-log-viewer', [
            'logs' => $logs,
            'actions' => $actions,
        ]);
    }
}
