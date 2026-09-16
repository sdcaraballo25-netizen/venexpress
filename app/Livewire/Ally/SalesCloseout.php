<?php

namespace App\Livewire\Ally;

use App\Livewire\Concerns\ExportsSpreadsheet;
use App\Models\Ally;
use App\Models\Package;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Cierre del día: cuánto se vendió y por cuál forma de pago, para que
 * el negocio pueda cuadrar la caja física contra el sistema.
 *
 * - Aliado Administrador: ve el total del negocio completo y puede
 *   filtrar por una taquilla puntual (o "Tú" para lo que él mismo
 *   registró directamente).
 * - Taquilla: solo ve lo que ELLA registró, sin poder ver ni elegir
 *   otra taquilla — el filtro no se le ofrece.
 */
#[Layout('layouts.ally')]
class SalesCloseout extends Component
{
    use ExportsSpreadsheet;

    public string $date;

    /**
     * ID de usuario a filtrar, o 'all' para el negocio completo.
     * Solo el Aliado Administrador puede cambiar esto — para Taquilla
     * queda fijo en su propio ID (ver mount()).
     */
    public string $registeredBy = 'all';

    public function mount(): void
    {
        $this->date = now()->toDateString();

        if (Auth::user()->isAliadoTaquilla()) {
            $this->registeredBy = (string) Auth::id();
        }
    }

    protected function ally(): Ally
    {
        $ally = Auth::user()?->resolveAlly();

        abort_unless($ally, 403);

        return $ally;
    }

    public function updatedRegisteredBy(): void
    {
        // Blindaje: aunque el select estuviera oculto por CSS, Taquilla
        // nunca puede cambiar el filtro a otra persona vía wire:model.
        // updated() (no updating()) porque necesita corregir el valor
        // DESPUÉS de que Livewire ya lo asignó, no antes.
        if (Auth::user()->isAliadoTaquilla() && $this->registeredBy !== (string) Auth::id()) {
            $this->registeredBy = (string) Auth::id();
        }
    }

    /**
     * Mismo filtrado (fecha + taquilla, con el mismo blindaje para
     * Taquilla) que usan tanto render() como exportExcel() — para no
     * repetir esta lógica en dos lugares que podrían desincronizarse.
     */
    protected function baseQuery(): Builder
    {
        $ally = $this->ally();
        $user = Auth::user();
        $isPrincipal = $user->isAliado();

        $day = Carbon::parse($this->date);
        $range = [$day->copy()->startOfDay(), $day->copy()->endOfDay()];

        $query = Package::query()
            ->where('ally_id', $ally->id)
            ->whereBetween('created_at', $range);

        if (! $isPrincipal) {
            // Taquilla: sin excepción, solo lo suyo.
            $query->where('registered_by_user_id', $user->id);
        } elseif ($this->registeredBy !== 'all') {
            $query->where('registered_by_user_id', (int) $this->registeredBy);
        }

        return $query;
    }

    /**
     * Exporta exactamente lo que se ve en pantalla para la fecha/
     * taquilla elegida: el desglose por forma de pago. Reutiliza
     * baseQuery() para no calcular los totales con un criterio
     * distinto al que ve el usuario.
     */
    public function exportExcel(): BinaryFileResponse
    {
        $rows = (clone $this->baseQuery())
            ->whereNotNull('payment_method')
            ->selectRaw('payment_method, SUM(total_price_usd) as total, COUNT(*) as guides')
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                Package::PAYMENT_METHOD_LABELS[$row->payment_method] ?? $row->payment_method,
                $row->guides,
                number_format((float) $row->total, 2, '.', ''),
            ]);

        return $this->excelDownload(
            "cierre-{$this->date}.xlsx",
            ['Forma de pago', 'Guías', 'Total USD'],
            $rows,
        );
    }

    public function render()
    {
        $ally = $this->ally();
        $user = Auth::user();
        $isPrincipal = $user->isAliado();

        $baseQuery = $this->baseQuery();

        $totalUsd = (clone $baseQuery)->sum('total_price_usd');
        $totalGuides = (clone $baseQuery)->count();

        $byPaymentMethod = (clone $baseQuery)
            ->whereNotNull('payment_method')
            ->selectRaw('payment_method, SUM(total_price_usd) as total, COUNT(*) as guides')
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get();

        // Los COD no llevan payment_method al registrarse (el cobro lo
        // hace el repartidor al entregar, no la taquilla) — se listan
        // aparte para no mezclarlos con lo que sí hay que cuadrar hoy.
        $codPendingUsd = (clone $baseQuery)
            ->where('is_cod', true)
            ->sum('cod_amount_usd');
        $codPendingCount = (clone $baseQuery)->where('is_cod', true)->count();

        $staffOptions = $isPrincipal
            ? $ally->staffUsers()->orderBy('name')->get(['id', 'name'])
            : collect();

        return view('livewire.ally.sales-closeout', [
            'ally' => $ally,
            'isPrincipal' => $isPrincipal,
            'staffOptions' => $staffOptions,
            'totalUsd' => $totalUsd,
            'totalGuides' => $totalGuides,
            'byPaymentMethod' => $byPaymentMethod,
            'codPendingUsd' => $codPendingUsd,
            'codPendingCount' => $codPendingCount,
        ]);
    }
}
