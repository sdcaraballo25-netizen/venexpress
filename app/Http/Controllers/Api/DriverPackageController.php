<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DriverPackageResource;
use App\Models\Package;
use App\Services\LogisticsScanService;
use App\Services\PackageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DriverPackageController extends Controller
{
    /**
     * Repartidor autenticado. La ruta ya está protegida por
     * middleware ['auth:sanctum', 'ability:driver', 'role:repartidor'],
     * así que aquí solo resolvemos la relación.
     */
    protected function driver()
    {
        $driver = Auth::user()?->driver;

        if (! $driver) {
            abort(403, 'Tu usuario no tiene un perfil de repartidor asociado.');
        }

        return $driver;
    }

    /**
     * Escanea el QR/código de una guía para recolectarla en la
     * agencia aliada. Reutiliza tal cual LogisticsScanService, que ya
     * valida ruta activa, parada correspondiente y bloqueo de
     * concurrencia.
     */
    public function scan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tracking_number' => ['required', 'string'],
        ]);

        $driver = $this->driver();

        $package = Package::query()
            ->where('tracking_number', trim($validated['tracking_number']))
            ->with(['ally', 'driver', 'histories'])
            ->first();

        if (! $package) {
            return response()->json([
                'message' => "No existe una guía con número: {$validated['tracking_number']}",
            ], 404);
        }

        $securityWarning = $package->security_hash
            ? ! $package->verifySecurityHash()
            : false;

        try {
            $package = app(LogisticsScanService::class)->scanCollection(
                package: $package,
                driver: $driver,
                userId: (int) Auth::id(),
            );

            return response()->json([
                'message' => 'Salida registrada correctamente. El paquete quedó recolectado por Venexpress.',
                'security_warning' => $securityWarning,
                'package' => new DriverPackageResource($package),
            ]);
        } catch (RuntimeException $e) {
            // Si ya pertenece a este repartidor y no está en estado
            // inicial, se puede consultar sin que cuente como error
            // bloqueante (mismo comportamiento que el Scanner web).
            if (
                (int) $package->driver_id === (int) $driver->id
                && $package->current_status !== Package::STATUS_RECIBIDO_AGENCIA
            ) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'security_warning' => $securityWarning,
                    'package' => new DriverPackageResource($package),
                ], 200);
            }

            return response()->json([
                'message' => $e->getMessage(),
                'security_warning' => $securityWarning,
                'package' => new DriverPackageResource($package),
            ], 422);
        }
    }

    /**
     * Lista de pedidos del repartidor autenticado (todos los que ha
     * escaneado), con el mismo filtro por estado que ya usa el
     * portal web (all / pending / in_progress / delivered / incidents).
     */
    public function index(Request $request): JsonResponse
    {
        $driver = $this->driver();

        $status = $request->query('status', 'all');
        $search = trim((string) $request->query('search', ''));

        if (! in_array($status, ['all', 'pending', 'in_progress', 'delivered', 'incidents'], true)) {
            $status = 'all';
        }

        $query = Package::query()
            ->where('driver_id', $driver->id)
            ->with('ally')
            ->withCount('incidents')
            ->orderByDesc('updated_at');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('tracking_number', 'like', "%{$search}%")
                    ->orWhere('recipient_name', 'like', "%{$search}%")
                    ->orWhere('destination_city', 'like', "%{$search}%");
            });
        }

        match ($status) {
            'pending' => $query->whereIn('current_status', [
                Package::STATUS_RECIBIDO_AGENCIA,
                Package::STATUS_RECOLECTADO_VENEXPRESS,
            ]),
            'in_progress' => $query->whereIn('current_status', [
                Package::STATUS_EN_HUB,
                Package::STATUS_EN_TRANSITO_NACIONAL,
                Package::STATUS_LISTO_RETIRO,
            ]),
            'delivered' => $query->where('current_status', Package::STATUS_ENTREGADO),
            'incidents' => $query->whereHas('incidents'),
            default => null,
        };

        $paginated = $query->paginate(
            (int) $request->query('per_page', 15)
        );

        return response()->json([
            'data' => DriverPackageResource::collection($paginated->items()),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }

    /**
     * Detalle de un pedido, incluyendo historial e incidencias, para
     * la pantalla de "datos del remitente/destinatario" al entregar.
     */
    public function show(int $packageId): JsonResponse
    {
        $driver = $this->driver();

        $package = Package::query()
            ->where('driver_id', $driver->id)
            ->with(['ally', 'driver', 'histories', 'incidents'])
            ->withCount('incidents')
            ->findOrFail($packageId);

        return response()->json([
            'package' => new DriverPackageResource($package),
            'history' => $package->histories->map(fn ($h) => [
                'status' => $h->status,
                'event_type' => $h->event_type,
                'location_description' => $h->location_description,
                'created_at' => $h->created_at?->toIso8601String(),
            ]),
        ]);
    }

    /**
     * Confirma la entrega a domicilio. Guarda los datos de quien
     * recibió el paquete y, opcionalmente, una foto como evidencia.
     * Dispara automáticamente la generación de la remuneración
     * pendiente del repartidor (DriverPaymentService, vía
     * PackageService::completeDelivery).
     */
    public function completeDelivery(Request $request, int $packageId): JsonResponse
    {
        $driver = $this->driver();

        $validated = $request->validate([
            'receiver_name' => ['required', 'string', 'max:150'],
            'receiver_id_doc' => ['required', 'string', 'max:30'],
            'receiver_phone' => ['nullable', 'string', 'max:30'],
            'delivery_confirmation_method' => ['required', 'in:firma,foto,cedula'],
            'photo' => ['nullable', 'image', 'max:5120'],
        ]);

        $package = Package::query()
            ->where('driver_id', $driver->id)
            ->findOrFail($packageId);

        $photoPath = null;

        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('delivery-evidence', 'public');
        }

        try {
            $package = app(PackageService::class)->completeDelivery(
                package: $package,
                driver: $driver,
                locationDescription: 'Entrega confirmada desde la app del repartidor',
                receiverName: $validated['receiver_name'],
                receiverIdDoc: $validated['receiver_id_doc'],
                receiverPhone: $validated['receiver_phone'] ?? null,
                deliveryConfirmationMethod: $validated['delivery_confirmation_method'],
                deliveryPhotoPath: $photoPath,
            );

            return response()->json([
                'message' => 'La entrega fue confirmada correctamente.',
                'package' => new DriverPackageResource($package),
            ]);
        } catch (RuntimeException $e) {
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }

            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Registra el cobro en destino (COD) para un pedido ya entregado.
     */
    public function collectCod(int $packageId): JsonResponse
    {
        $driver = $this->driver();

        $package = Package::query()
            ->where('driver_id', $driver->id)
            ->findOrFail($packageId);

        try {
            $package = app(PackageService::class)->collectCod(
                package: $package,
                userId: (int) Auth::id(),
                driver: $driver,
            );

            return response()->json([
                'message' => 'El cobro COD fue registrado correctamente.',
                'package' => new DriverPackageResource($package),
            ]);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
