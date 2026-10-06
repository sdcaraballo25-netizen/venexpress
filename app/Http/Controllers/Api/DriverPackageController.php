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

        $scanService = app(LogisticsScanService::class);
        $canAccess = $scanService->canDriverAccessPackage($package, $driver);

        $securityWarning = $package->security_hash
            ? ! $package->verifySecurityHash()
            : false;

        try {
            $package = $scanService->scanCollection(
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

            // No se filtra la PII del paquete (remitente/destinatario,
            // COD) en una respuesta de error a menos que el paquete
            // realmente le toque a este repartidor.
            return response()->json(array_filter([
                'message' => $e->getMessage(),
                'security_warning' => $securityWarning,
                'package' => $canAccess ? new DriverPackageResource($package) : null,
            ], fn ($v) => $v !== null), 422);
        }
    }

    /**
     * Recepción física en el HUB de un paquete ya recolectado
     * (segunda mitad de "Aliado -> HUB" en una ruta hub_transfer).
     * Reutiliza LogisticsScanService::scanHubReception() tal cual,
     * igual que scan() reutiliza scanCollection() — pero, a
     * diferencia de scan(), aquí NO hay un caso "silencioso" para un
     * doble escaneo: scanHubReception() limpia driver_id al recibir
     * el paquete en HUB, así que un segundo escaneo ya no pertenece
     * a nadie en particular y simplemente falla con un 422 que dice
     * el estado actual (ya no es RECOLECTADO_VENEXPRESS), sin
     * necesidad de un caso especial.
     */
    public function hubReception(Request $request): JsonResponse
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

        $scanService = app(LogisticsScanService::class);
        $canAccess = $scanService->canDriverAccessPackage($package, $driver);

        try {
            $package = $scanService->scanHubReception(
                package: $package,
                driver: $driver,
                userId: (int) Auth::id(),
            );

            return response()->json([
                'message' => 'Recepción en HUB registrada correctamente. El paquete quedó EN_HUB.',
                'package' => new DriverPackageResource($package),
            ]);
        } catch (RuntimeException $e) {
            return response()->json(array_filter([
                'message' => $e->getMessage(),
                'package' => $canAccess ? new DriverPackageResource($package) : null,
            ], fn ($v) => $v !== null), 422);
        }
    }

    /**
     * Identifica una guía por número de tracking sin ejecutar ningún
     * movimiento (equivalente de solo-lectura a
     * Scanner::searchPackage() del portal web). La app la usa para
     * decidir qué operación proponer (recolección / recepción en HUB
     * / salida / llegada) antes de pedirle confirmación al
     * repartidor, en vez de ejecutar a ciegas en el primer escaneo.
     */
    public function lookup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tracking_number' => ['required', 'string'],
        ]);

        $driver = $this->driver();

        $package = Package::query()
            ->where('tracking_number', trim($validated['tracking_number']))
            ->with(['ally', 'driver', 'histories'])
            ->first();

        if (! $package || ! app(LogisticsScanService::class)->canDriverAccessPackage($package, $driver)) {
            return response()->json([
                'message' => "No existe una guía con número: {$validated['tracking_number']}",
            ], 404);
        }

        return response()->json([
            'package' => new DriverPackageResource($package),
        ]);
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
                Package::STATUS_EN_RUTA,
                Package::STATUS_ENTREGA_FALLIDA,
            ]),
            'delivered' => $query->where('current_status', Package::STATUS_ENTREGADO),
            'incidents' => $query->whereHas('incidents'),
            default => null,
        };

        // Acotado: sin límite, ?per_page=100000 devolvía toda la tabla
        // de un solo golpe.
        $paginated = $query->paginate(
            min(max((int) $request->query('per_page', 15), 1), 50)
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
     * Confirma la entrega a domicilio (solo desde EN_RUTA). Con el PIN
     * que el destinatario recibió por correo (delivery_pin) o, sin él,
     * con la cédula del destinatario (receiver_id_doc) y una foto de la
     * entrega (photo). Un COD sin cobrar exige la forma de pago y, si es
     * electrónica, la referencia (cod_payment_reference); el comprobante
     * (cod_payment_proof) es opcional. Dispara la remuneración pendiente
     * del repartidor (DriverPaymentService, vía
     * PackageService::completeDelivery).
     *
     * delivery_confirmation_method ya no se usa (lo decide el servidor
     * según haya PIN o no); se sigue aceptando para no romper versiones
     * anteriores de la app.
     */
    public function completeDelivery(Request $request, int $packageId): JsonResponse
    {
        $driver = $this->driver();

        $package = Package::query()
            ->where('driver_id', $driver->id)
            ->findOrFail($packageId);

        $codPending = $package->is_cod && ! $package->cod_collected_at;
        $withPin = $request->filled('delivery_pin');

        $validated = $request->validate([
            'receiver_name' => ['required', 'string', 'max:150'],
            'receiver_id_doc' => [$withPin ? 'nullable' : 'required', 'string', 'max:30'],
            'receiver_phone' => ['nullable', 'string', 'max:30'],
            'delivery_pin' => ['nullable', 'digits:6'],
            'delivery_confirmation_method' => ['nullable', 'string', 'max:30'],
            'photo' => [$withPin ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'cod_payment_method' => [
                $codPending ? 'required' : 'nullable',
                'in:'.implode(',', Package::PAYMENT_METHODS),
            ],
            'cod_payment_reference' => [
                $codPending && in_array($request->input('cod_payment_method'), Package::PAYMENT_METHODS_REQUIRING_REFERENCE, true)
                    ? 'required'
                    : 'nullable',
                'string',
                'max:100',
            ],
            'cod_payment_proof' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'receiver_id_doc.required' => 'Sin PIN, indica la cédula del destinatario.',
            'photo.required' => 'Sin PIN, adjunta una foto de la entrega.',
            'delivery_pin.digits' => 'El PIN tiene 6 dígitos.',
            'cod_payment_method.required' => 'Este pedido es contra entrega (COD): indica la forma de pago con la que te cancelaron.',
            'cod_payment_reference.required' => 'Indica el número de referencia del pago.',
        ]);

        $photoPath = $request->hasFile('photo')
            ? $request->file('photo')->store('delivery-evidence', 'documents')
            : null;

        $proofPath = $codPending && $request->hasFile('cod_payment_proof')
            ? $request->file('cod_payment_proof')->store('cod-payment-proofs', 'documents')
            : null;

        try {
            $package = app(PackageService::class)->completeDelivery(
                package: $package,
                driver: $driver,
                locationDescription: 'Entrega confirmada desde la app del repartidor',
                receiverName: $validated['receiver_name'],
                receiverIdDoc: $validated['receiver_id_doc'] ?? null,
                receiverPhone: $validated['receiver_phone'] ?? null,
                deliveryPin: $validated['delivery_pin'] ?? null,
                deliveryPhotoPath: $photoPath,
                codPaymentMethod: $validated['cod_payment_method'] ?? null,
                codPaymentReference: $validated['cod_payment_reference'] ?? null,
                codPaymentProofPath: $proofPath,
            );

            return response()->json([
                'message' => 'La entrega fue confirmada correctamente.',
                'package' => new DriverPackageResource($package),
            ]);
        } catch (RuntimeException $e) {
            foreach (array_filter([$photoPath, $proofPath]) as $path) {
                Storage::disk('documents')->delete($path);
            }

            return response()->json([
                'message' => $e->getMessage(),
                'package' => new DriverPackageResource($package->fresh()),
            ], 422);
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
