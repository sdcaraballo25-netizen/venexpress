<?php

namespace App\Livewire\Driver;

use App\Models\Driver;
use App\Models\Package;
use App\Models\Route;
use App\Services\IncidentService;
use App\Services\PackageService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use RuntimeException;

#[Layout('layouts.driver')]
class PackageDetail extends Component
{
    use WithFileUploads;

    public int $packageId;

    public Package $package;

    public bool $isHub = false;

    /**
     * Forma de pago con la que el destinatario canceló el COD.
     * Requerida por completeDelivery() cuando el paquete es COD y
     * todavía no se había cobrado; si es electrónica también la
     * referencia, y el comprobante es opcional.
     */
    public string $codPaymentMethod = '';

    public string $codPaymentReference = '';

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $codPaymentProof = null;

    /*
     * Datos de quién recibió el paquete: los mismos que ya exige la app
     * (DriverPackageController::completeDelivery), para que confirmar
     * desde el panel web deje el mismo registro que desde Flutter.
     */
    public string $receiverName = '';

    public string $receiverIdDoc = '';

    public string $receiverPhone = '';

    /** PIN de entrega que el destinatario recibió por correo. */
    public string $deliveryPin = '';

    /**
     * El destinatario no tiene el PIN: se confirma con su cédula y una
     * foto de la entrega.
     */
    public bool $deliverWithoutPin = false;

    /**
     * Sin PIN, lo recibe un tercero autorizado por el destinatario: su
     * cédula (receiverIdDoc), una foto de ella y una de la copia de la
     * cédula del destinatario.
     */
    public bool $receivedByThirdParty = false;

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $thirdPartyIdPhoto = null;

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $recipientIdCopy = null;

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $deliveryPhoto = null;

    /** Formulario de "No se pudo entregar" (EN_RUTA -> ENTREGA_FALLIDA). */
    public bool $showFailedForm = false;

    public string $failedReason = '';

    public string $failedNotes = '';

    /** Formulario de incidencia (p. ej. no se pudo entregar). */
    public bool $showIncidentForm = false;

    public string $incidentType = '';

    public string $incidentDescription = '';

    public function mount(int $packageId): void
    {
        $user = Auth::user();

$driver = $user?->driver;

        if (! $driver) {
            abort(
                403,
                'Tu usuario no tiene un perfil de repartidor asociado.'
            );
        }

        $this->isHub = $driver->driver_type === Driver::TYPE_HUB;

        $this->package = Package::query()
            ->where(
                'driver_id',
                $driver->id
            )
            ->with([
                'ally',
                'driver',
                'histories',
                'incidents',
            ])
            ->withCount('incidents')
            ->findOrFail($packageId);
    }

    /**
     * El repartidor inicia el proceso de entrega.
     */
    public function startDelivery(): void
    {
        try {

            $user = Auth::user();

$driver = $user?->driver;

            if (! $driver) {
                abort(
                    403,
                    'Tu usuario no tiene un perfil de repartidor asociado.'
                );
            }

            if ($driver->driver_type === Driver::TYPE_HUB) {
                throw new RuntimeException(
                    'Esta acción es exclusiva de repartidores de entrega (Delivery).'
                );
            }

            $this->package->refresh();

            if (
                (int) $this->package->driver_id
                !== (int) $driver->id
            ) {
                throw new RuntimeException(
                    'Este paquete no está asignado a tu ruta.'
                );
            }

            if (
                $this->package->current_status
                !== Package::STATUS_RECOLECTADO_VENEXPRESS
            ) {
                throw new RuntimeException(
                    'El paquete debe estar recolectado antes de iniciar la entrega.'
                );
            }

            // Sale a reparto (EN_RUTA) y se genera el PIN de entrega.
            $this->package =
                app(PackageService::class)->sendOutForDelivery(
                    package: $this->package,
                    userId: (int) Auth::id(),
                    locationDescription: 'Salió a reparto con el repartidor',
                    originLocation: 'Agencia de origen',
                );

            session()->flash(
                'success',
                'La entrega ha sido iniciada correctamente.'
            );

        } catch (RuntimeException $e) {

            session()->flash(
                'error',
                $e->getMessage()
            );
        }
    }

    /**
     * El repartidor confirma que la entrega a domicilio fue realizada.
     */
    public function completeDelivery(): void
    {
        try {

            $user = Auth::user();

$driver = $user?->driver;

            if (! $driver) {
                abort(
                    403,
                    'Tu usuario no tiene un perfil de repartidor asociado.'
                );
            }

            if ($driver->driver_type === Driver::TYPE_HUB) {
                throw new RuntimeException(
                    'Esta acción es exclusiva de repartidores de entrega (Delivery).'
                );
            }

            $this->package->refresh();

            $codPending = $this->package->is_cod && ! $this->package->cod_collected_at;
            $withPin = $this->package->acceptsDeliveryPin() && ! $this->deliverWithoutPin;
            $thirdParty = ! $withPin && $this->receivedByThirdParty;

            // Mismas reglas que la app (DriverPackageController::completeDelivery).
            $this->validate([
                'receiverName' => ['required', 'string', 'max:150'],
                'receiverIdDoc' => [$withPin ? 'nullable' : 'required', 'string', 'max:30'],
                'receiverPhone' => ['nullable', 'string', 'max:30'],
                'deliveryPin' => [$withPin ? 'required' : 'nullable', 'digits:6'],
                'deliveryPhoto' => [$withPin || $thirdParty ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
                'thirdPartyIdPhoto' => [$thirdParty ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
                'recipientIdCopy' => [$thirdParty ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
                'codPaymentMethod' => [$codPending ? 'required' : 'nullable', 'in:'.implode(',', Package::PAYMENT_METHODS)],
                'codPaymentReference' => [
                    $codPending && in_array($this->codPaymentMethod, Package::PAYMENT_METHODS_REQUIRING_REFERENCE, true) ? 'required' : 'nullable',
                    'string',
                    'max:100',
                ],
                'codPaymentProof' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            ], [
                'receiverName.required' => 'Indica el nombre de quien recibe.',
                'receiverIdDoc.required' => 'Sin PIN, indica la cédula del destinatario.',
                'deliveryPin.required' => 'Pídele al destinatario el PIN de entrega.',
                'deliveryPin.digits' => 'El PIN tiene 6 dígitos.',
                'deliveryPhoto.required' => 'Sin PIN, toma una foto de la entrega.',
                'thirdPartyIdPhoto.required' => 'Toma una foto de la cédula de quien recibe.',
                'recipientIdCopy.required' => 'Toma una foto de la copia de la cédula del destinatario.',
                'codPaymentMethod.required' => 'Este pedido es contra entrega (COD): indica la forma de pago con la que te cancelaron.',
                'codPaymentReference.required' => 'Indica el número de referencia del pago.',
            ]);

            $photoPath = $this->deliveryPhoto
                ? $this->deliveryPhoto->store('delivery-evidence', 'documents')
                : null;

            $proofPath = $codPending && $this->codPaymentProof
                ? $this->codPaymentProof->store('cod-payment-proofs', 'documents')
                : null;

            $thirdPartyIdPath = $thirdParty
                ? $this->thirdPartyIdPhoto->store('third-party-ids', 'documents')
                : null;

            $recipientIdCopyPath = $thirdParty
                ? $this->recipientIdCopy->store('third-party-ids', 'documents')
                : null;

            try {
                $this->package =
                    app(PackageService::class)->completeDelivery(
                        package: $this->package,
                        driver: $driver,
                        locationDescription:
                            'Entrega confirmada por el repartidor',
                        receiverName: $this->receiverName,
                        receiverIdDoc: $this->receiverIdDoc,
                        receiverPhone: $this->receiverPhone !== '' ? $this->receiverPhone : null,
                        deliveryPin: $withPin ? $this->deliveryPin : null,
                        deliveryPhotoPath: $photoPath,
                        codPaymentMethod: $this->codPaymentMethod !== '' ? $this->codPaymentMethod : null,
                        codPaymentReference: $this->codPaymentReference,
                        codPaymentProofPath: $proofPath,
                        receivedByThirdParty: $thirdParty,
                        thirdPartyIdPhotoPath: $thirdPartyIdPath,
                        recipientIdCopyPath: $recipientIdCopyPath,
                    );
            } catch (RuntimeException $e) {
                foreach (array_filter([$photoPath, $proofPath, $thirdPartyIdPath, $recipientIdCopyPath]) as $path) {
                    Storage::disk('documents')->delete($path);
                }

                // Un PIN incorrecto se cuenta: refresca para mostrar si
                // ya no se acepta y hay que pasar a cédula + foto.
                $this->package->refresh();

                throw $e;
            }

            $this->reset([
                'codPaymentMethod',
                'codPaymentReference',
                'codPaymentProof',
                'receiverName',
                'receiverIdDoc',
                'receiverPhone',
                'deliveryPin',
                'deliverWithoutPin',
                'deliveryPhoto',
                'receivedByThirdParty',
                'thirdPartyIdPhoto',
                'recipientIdCopy',
            ]);

            session()->flash(
                'success',
                'La entrega fue confirmada correctamente.'
            );

        } catch (RuntimeException $e) {

            session()->flash(
                'error',
                $e->getMessage()
            );
        }
    }

    /**
     * No se pudo entregar: queda ENTREGA_FALLIDA con el motivo y se
     * cuenta el intento (PackageService::markDeliveryFailed()). El
     * repartidor debe devolver el paquete al almacén.
     */
    public function markDeliveryFailed(): void
    {
        $this->validate([
            'failedReason' => ['required', 'in:'.implode(',', array_keys(Package::FAILED_DELIVERY_REASON_LABELS))],
            'failedNotes' => [$this->failedReason === 'OTRO' ? 'required' : 'nullable', 'string', 'max:1000'],
        ], [
            'failedReason.required' => 'Selecciona por qué no se pudo entregar.',
            'failedNotes.required' => 'Describe brevemente qué pasó.',
        ]);

        $driver = Auth::user()?->driver;

        if (! $driver) {
            abort(403, 'Tu usuario no tiene un perfil de repartidor asociado.');
        }

        try {
            $this->package = app(PackageService::class)->markDeliveryFailed(
                package: $this->package,
                driver: $driver,
                reason: $this->failedReason,
                notes: $this->failedNotes,
            );

            $this->reset(['showFailedForm', 'failedReason', 'failedNotes']);

            session()->flash('success', 'Entrega marcada como fallida. Devuelve el paquete al almacén.');
        } catch (RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    /**
     * Reporta un problema con la entrega (cliente ausente, dirección
     * incorrecta, etc.). Igual que en la app: no cambia el estado del
     * paquete, solo deja la incidencia para que Admin la gestione.
     * Reutiliza IncidentService::reportByDriver().
     */
    public function reportIncident(): void
    {
        $this->validate([
            'incidentType' => ['required', 'in:'.implode(',', IncidentService::DRIVER_TYPES)],
            'incidentDescription' => ['required', 'string', 'max:1000'],
        ], [
            'incidentType.required' => 'Selecciona el motivo.',
            'incidentDescription.required' => 'Describe brevemente lo ocurrido.',
        ]);

        $driver = Auth::user()?->driver;

        if (! $driver) {
            abort(403, 'Tu usuario no tiene un perfil de repartidor asociado.');
        }

        try {
            $this->package->refresh();

            app(IncidentService::class)->reportByDriver(
                package: $this->package,
                driver: $driver,
                type: $this->incidentType,
                description: $this->incidentDescription,
                userId: (int) Auth::id(),
            );

            $this->reset(['showIncidentForm', 'incidentType', 'incidentDescription']);

            $this->package->load('incidents')->loadCount('incidents');

            session()->flash('success', 'Incidencia reportada. El equipo administrativo la revisará.');
        } catch (RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    /**
     * El repartidor registra el cobro en destino (COD).
     */
    public function collectCod(): void
    {
        try {

            $user = Auth::user();

            $driver = $user?->driver;

            if (! $driver) {
                abort(
                    403,
                    'Tu usuario no tiene un perfil de repartidor asociado.'
                );
            }

            if ($driver->driver_type === Driver::TYPE_HUB) {
                throw new RuntimeException(
                    'Esta acción es exclusiva de repartidores de entrega (Delivery).'
                );
            }

            $this->package->refresh();

            $this->package =
    app(PackageService::class)->collectCod(
        package: $this->package,
        userId: (int) Auth::id(),
        driver: $driver,
    );

            session()->flash(
                'success',
                'El cobro COD fue registrado correctamente.'
            );

        } catch (RuntimeException $e) {

            session()->flash(
                'error',
                $e->getMessage()
            );
        }
    }

    public function render()
    {
        $activeRouteId = null;

        if ($this->isHub) {
            $activeRouteId = Route::query()
                ->where('driver_id', $this->package->driver_id)
                ->whereIn('status', [
                    Route::STATUS_ASSIGNED,
                    Route::STATUS_IN_PROGRESS,
                ])
                ->latest('created_at')
                ->value('id');
        }

        return view(
            'livewire.driver.package-detail',
            [
                'activeRouteId' => $activeRouteId,
            ]
        );
    }
}
