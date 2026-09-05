<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\Package;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Rastreo público de guías.
 *
 * Esta lógica vivía antes como closures directamente en routes/web.php.
 * Se movió aquí para seguir el mismo patrón que el resto del sistema
 * (PackageService, TariffService, etc.) y para poder testearla sin
 * tener que arrancar el router completo.
 */
class TrackingController extends Controller
{
    /**
     * Línea de tiempo pública de estados. El orden de este array define
     * el orden de los pasos que ve el cliente; no necesariamente coincide
     * 1:1 con todos los estados internos del sistema (por ejemplo, no
     * incluye estados de incidencia/devolución a propósito).
     */
    private const STATUS_ORDER = [

        'RECIBIDO_AGENCIA' => [
            'label' => 'Recibido en Agencia Aliada',
            'icon'  => 'fa-warehouse',
        ],

        'RECOLECTADO_VENEXPRESS' => [
            'label' => 'Recolectado por Venexpress',
            'icon'  => 'fa-truck',
        ],

        'EN_HUB' => [
            'label' => 'En Hub de Clasificación',
            'icon'  => 'fa-warehouse',
        ],

        'EN_TRANSITO_NACIONAL' => [
            'label' => 'En Tránsito Nacional',
            'icon'  => 'fa-truck-fast',
        ],

        'LISTO_RETIRO' => [
            'label' => 'Listo para Retiro en Agencia Destino',
            'icon'  => 'fa-truck-ramp-box',
        ],

        'ENTREGADO' => [
            'label' => 'Entregado al Cliente',
            'icon'  => 'fa-house-circle-check',
        ],
    ];

    public function index(): View
    {
        return view('tracking.index');
    }

    public function show(Request $request): View
    {
        $guia = trim((string) $request->query('guia'));

        $package = Package::query()
            ->where('tracking_number', $guia)
            ->first();

        $statusSteps = [];
        $progressPercent = 0;
        $statusIsKnown = true;
        $hasOpenIncident = false;

        if ($package) {
            [$statusSteps, $progressPercent, $statusIsKnown] =
                $this->buildTimeline($package);

            // Hallazgo de auditoría #5: los 6 estados de
            // Package::STATUSES no incluyen "devuelto"/"con
            // incidencia", así que un paquete con una incidencia
            // abierta se ve congelado en su último paso conocido sin
            // explicación. En vez de inventar un estado falso en la
            // línea de tiempo, avisamos aparte que hay una incidencia
            // en revisión.
            $hasOpenIncident = $package->incidents()
                ->whereIn('status', [Incident::STATUS_OPEN, Incident::STATUS_IN_PROGRESS])
                ->exists();
        }

        return view('tracking.show', [
            'guia' => $guia,
            'package' => $package,
            'statusSteps' => $statusSteps,
            'progressPercent' => $progressPercent,
            'statusIsKnown' => $statusIsKnown,
            'hasOpenIncident' => $hasOpenIncident,
        ]);
    }

    /**
     * Calcula los pasos de la línea de tiempo pública y el porcentaje
     * de avance para un paquete dado.
     *
     * @return array{0: array, 1: float, 2: bool}
     */
    private function buildTimeline(Package $package): array
    {
        $keys = array_keys(self::STATUS_ORDER);

        $currentIndex = array_search(
            $package->current_status,
            $keys,
            true
        );

        // Si el estado actual del paquete no está en la línea de
        // tiempo pública (ej. un estado nuevo agregado a futuro que
        // aún no se refleja aquí), NO debemos tratarlo como si fuera
        // el primer paso: eso mostraría "Recibido en Agencia" cuando
        // en realidad podría estar, por ejemplo, devuelto o con una
        // incidencia. En ese caso dejamos todos los pasos como
        // pendientes (ningún índice actual) en vez de mentir sobre el
        // progreso.
        $statusIsKnown = $currentIndex !== false;

        $currentIndex = $statusIsKnown
            ? $currentIndex
            : -1;

        /*
         * Historial ordenado cronológicamente.
         *
         * No usamos pluck('created_at', 'status') porque
         * eso elimina estados repetidos.
         */
        $history = $package->histories()
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        /*
         * Para la línea de progreso utilizamos la última
         * ocurrencia conocida de cada estado.
         */
        $latestHistoryByStatus = $history
            ->groupBy('status')
            ->map(function ($events) {
                return $events->sortBy([
                    ['created_at', 'desc'],
                    ['id', 'desc'],
                ])->first();
            });

        // Si el estado actual no está en la línea de tiempo pública,
        // no inventamos un índice: usamos el último paso conocido que
        // sí quedó registrado en el histórico. Así un paquete con un
        // estado especial (ej. devuelto/incidencia) sigue mostrando
        // correctamente lo que sí completó, sin marcar nada como "paso
        // actual" de la línea de tiempo estándar.
        $lastKnownIndex = -1;

        foreach ($keys as $i => $key) {
            if ($latestHistoryByStatus->has($key)) {
                $lastKnownIndex = $i;
            }
        }

        $effectiveIndex = $statusIsKnown ? $currentIndex : $lastKnownIndex;

        $statusSteps = [];

        foreach ($keys as $i => $key) {

            $timestamp = null;

            $event = $latestHistoryByStatus->get($key);

            if ($event) {

                $date = Carbon::parse(
                    $event->created_at
                );

                $timestamp =
                    $date->format('d/m/Y')
                    . '<br>'
                    . $date->format('h:i a');
            }

            $statusSteps[] = [

                'label' => self::STATUS_ORDER[$key]['label'],

                'icon' => self::STATUS_ORDER[$key]['icon'],

                'done' => $i < $effectiveIndex,

                'current' => $statusIsKnown && $i === $effectiveIndex,

                'timestamp' => $timestamp,
            ];
        }

        $progressPercent = $effectiveIndex <= 0
            ? 8
            : (
                $effectiveIndex
                / (count($keys) - 1)
            ) * 100;

        return [$statusSteps, $progressPercent, $statusIsKnown];
    }
}
