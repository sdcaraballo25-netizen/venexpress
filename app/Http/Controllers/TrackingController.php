<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\Package;
use App\Services\LogisticsResolutionService;
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
    public function __construct(
        private readonly LogisticsResolutionService $logisticsResolutionService,
    ) {
    }

    /**
     * Pasos de la línea de tiempo pública. Cada paquete ve los pasos de
     * su modalidad (ver stepsFor()): retiro en persona (LISTO_RETIRO) o
     * entrega a domicilio (PENDIENTE_ENTREGA -> EN_RUTA). Los estados
     * fuera de la línea normal (ENTREGA_FALLIDA, EN_DEVOLUCION,
     * DEVUELTO) se explican con un aviso aparte en la vista; junto con
     * estos suman los 11 estados de Package::STATUSES.
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

        'PENDIENTE_ENTREGA' => [
            'label' => 'Pendiente de Entrega a Domicilio',
            'icon'  => 'fa-box',
        ],

        'EN_RUTA' => [
            'label' => 'En Ruta de Entrega',
            'icon'  => 'fa-motorcycle',
        ],

        'ENTREGADO' => [
            'label' => 'Entregado al Cliente',
            'icon'  => 'fa-house-circle-check',
        ],
    ];

    private const COMMON_STEPS = [
        'RECIBIDO_AGENCIA',
        'RECOLECTADO_VENEXPRESS',
        'EN_HUB',
        'EN_TRANSITO_NACIONAL',
    ];

    private const PICKUP_STEPS = [
        'LISTO_RETIRO',
        'ENTREGADO',
    ];

    private const HOME_DELIVERY_STEPS = [
        'PENDIENTE_ENTREGA',
        'EN_RUTA',
        'ENTREGADO',
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

        $currentStatusLabel = null;

        if ($package) {
            [$statusSteps, $progressPercent, $statusIsKnown, $currentStatusLabel] =
                $this->buildTimeline($package);

            // Hallazgo de auditoría #5: no hay un estado "con
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
            'currentStatusLabel' => $currentStatusLabel,
        ]);
    }

    /**
     * @return list<string>
     */
    private function stepsFor(Package $package): array
    {
        return [
            ...self::COMMON_STEPS,
            ...($package->requires_delivery ? self::HOME_DELIVERY_STEPS : self::PICKUP_STEPS),
        ];
    }

    /**
     * Calcula los pasos de la línea de tiempo pública y el porcentaje
     * de avance para un paquete dado.
     *
     * La línea de tiempo nunca retrocede: avanza hasta el paso más
     * lejano que el paquete haya alcanzado (su estado actual o
     * cualquiera de su historial). Así, un paquete que vuelve a EN_HUB
     * en el HUB destino después de EN_TRANSITO_NACIONAL, o uno que
     * vuelve a quedar pendiente de entrega porque se canceló la ruta
     * de reparto, no "desanda" pasos ya mostrados al cliente.
     *
     * Devuelve también el texto del badge de la cabecera: el mismo del
     * paso "actual" del timeline cuando lo hay (nunca dos textos
     * distintos en pantalla); si no — fallida, devolución, o un paso
     * que el timeline ya dejó atrás, como EN_HUB en el HUB destino
     * después de EN_TRANSITO_NACIONAL —, el del estado real.
     *
     * @return array{0: array, 1: float, 2: bool, 3: string}
     */
    private function buildTimeline(Package $package): array
    {
        $keys = $this->stepsFor($package);

        // Cualquiera de los 11 estados del sistema es conocido; los que
        // no están en la línea de tiempo de su modalidad
        // (ENTREGA_FALLIDA, EN_DEVOLUCION, DEVUELTO) se explican con un
        // aviso aparte en la vista.
        $statusIsKnown = in_array($package->current_status, Package::STATUSES, true);

        $currentIndex = array_search($package->current_status, $keys, true);
        $currentInLine = $currentIndex !== false;

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

        $furthestIndex = $currentInLine ? $currentIndex : -1;

        foreach ($keys as $i => $key) {
            if ($latestHistoryByStatus->has($key)) {
                $furthestIndex = max($furthestIndex, $i);
            }
        }

        // El paso "actual" (resaltado) solo existe si el estado actual
        // está en la línea y es el más lejano alcanzado. Si el paquete
        // está fuera de la línea (fallida/devolución) o volvió a un paso
        // anterior, todo lo alcanzado se muestra como completado.
        $currentStepIndex = $currentInLine && $currentIndex === $furthestIndex
            ? $furthestIndex
            : null;

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

            $label = self::STATUS_ORDER[$key]['label'];

            // Solo el paso ACTUAL usa el texto del estado real (p. ej.
            // "Llegó al HUB de destino"); los pasados, el genérico.
            if ($currentStepIndex === $i) {
                $label = $this->currentStatusLabel($package);
            }

            $statusSteps[] = [

                'label' => $label,

                'icon' => self::STATUS_ORDER[$key]['icon'],

                'done' => $currentStepIndex === null
                    ? $i <= $furthestIndex
                    : $i < $currentStepIndex,

                'current' => $currentStepIndex === $i,

                'timestamp' => $timestamp,
            ];
        }

        $progressPercent = $furthestIndex <= 0
            ? 8
            : (
                $furthestIndex
                / (count($keys) - 1)
            ) * 100;

        return [$statusSteps, $progressPercent, $statusIsKnown, $this->currentStatusLabel($package)];
    }

    /**
     * Texto público del estado actual. EN_HUB no distingue, por sí
     * solo, si el paquete sigue pendiente de otra transferencia HUB ->
     * HUB o si ya llegó a su HUB destino final: se reutiliza
     * LogisticsResolutionService::isAtDestinationWarehouse() tal cual
     * (si no se puede resolver, queda la etiqueta genérica).
     */
    private function currentStatusLabel(Package $package): string
    {
        if (
            $package->current_status === Package::STATUS_EN_HUB
            && $this->logisticsResolutionService->isAtDestinationWarehouse($package)
        ) {
            return 'Llegó al HUB de destino';
        }

        return self::STATUS_ORDER[$package->current_status]['label']
            ?? $package->status_label;
    }
}
