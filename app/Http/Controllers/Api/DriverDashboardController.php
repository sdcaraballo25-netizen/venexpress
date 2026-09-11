<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DriverPackageResource;
use App\Http\Resources\DriverPaymentResource;
use App\Models\DriverPayment;
use App\Models\Package;
use App\Services\DriverPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DriverDashboardController extends Controller
{
    protected function driver()
    {
        $driver = Auth::user()?->driver;

        if (! $driver) {
            abort(403, 'Tu usuario no tiene un perfil de repartidor asociado.');
        }

        return $driver;
    }

    /**
     * Pantalla principal: totales de pedidos por entregar/entregados
     * y el resumen de comisiones. Todo scoped al repartidor
     * autenticado.
     */
    public function summary(): JsonResponse
    {
        $driver = $this->driver();

        $baseQuery = Package::query()->where('driver_id', $driver->id);

        $assignedCount = (clone $baseQuery)->count();

        $pendingCount = (clone $baseQuery)
            ->where('current_status', '!=', Package::STATUS_ENTREGADO)
            ->count();

        $deliveredCount = (clone $baseQuery)
            ->where('current_status', Package::STATUS_ENTREGADO)
            ->count();

        $deliveredTodayCount = (clone $baseQuery)
            ->where('current_status', Package::STATUS_ENTREGADO)
            ->whereDate('delivery_completed_at', today())
            ->count();

        $codPendingCount = (clone $baseQuery)
            ->where('is_cod', true)
            ->where('cod_status', Package::COD_PENDIENTE)
            ->where('current_status', Package::STATUS_ENTREGADO)
            ->count();

        $pendingCommissionUsd = (float) DriverPayment::query()
            ->where('driver_id', $driver->id)
            ->where('status', DriverPayment::STATUS_PENDING)
            ->sum('amount_usd');

        $paidCommissionUsd = (float) DriverPayment::query()
            ->where('driver_id', $driver->id)
            ->where('status', DriverPayment::STATUS_PAID)
            ->sum('amount_usd');

        $pendingPackages = Package::query()
            ->where('driver_id', $driver->id)
            ->where('current_status', '!=', Package::STATUS_ENTREGADO)
            ->orderByDesc('distance_km')
            ->orderBy('id')
            ->limit(10)
            ->get();

        return response()->json([
            'driver' => [
                'id' => $driver->id,
                'status' => $driver->status,
                'driver_type' => $driver->driver_type,
            ],
            'packages' => [
                'assigned' => $assignedCount,
                'pending_to_deliver' => $pendingCount,
                'delivered' => $deliveredCount,
                'delivered_today' => $deliveredTodayCount,
                'cod_pending' => $codPendingCount,
            ],
            'commissions' => [
                'rate_per_package_usd' => app(DriverPaymentService::class)->amount(),
                'pending_usd' => round($pendingCommissionUsd, 2),
                'paid_usd' => round($paidCommissionUsd, 2),
            ],
            'pending_packages_preview' => DriverPackageResource::collection($pendingPackages),
        ]);
    }

    /**
     * Historial completo de comisiones (para una pantalla dedicada
     * de "Mis comisiones", separada del resumen del dashboard).
     */
    public function commissions(): JsonResponse
    {
        $driver = $this->driver();

        $payments = DriverPayment::query()
            ->where('driver_id', $driver->id)
            ->with('package')
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => DriverPaymentResource::collection($payments->items()),
            'meta' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'total' => $payments->total(),
            ],
            'totals' => [
                'pending_usd' => round((float) DriverPayment::query()
                    ->where('driver_id', $driver->id)
                    ->where('status', DriverPayment::STATUS_PENDING)
                    ->sum('amount_usd'), 2),
                'paid_usd' => round((float) DriverPayment::query()
                    ->where('driver_id', $driver->id)
                    ->where('status', DriverPayment::STATUS_PAID)
                    ->sum('amount_usd'), 2),
            ],
        ]);
    }
}
