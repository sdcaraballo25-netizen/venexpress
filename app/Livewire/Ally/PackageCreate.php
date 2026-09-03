<?php

namespace App\Livewire\Ally;

use App\Models\Customer;
use App\Models\Package;
use App\Services\PackageService;
use App\Services\TariffService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Validation\Rule;

#[Layout('layouts.ally')]
class PackageCreate extends Component
{
    /**
     * Tipos de documento de identidad soportados.
     */
    public const DOC_TYPES = ['V', 'E', 'J'];

    public const DOC_TYPE_LABELS = [
        'V' => 'Cédula (V)',
        'E' => 'Extranjero (E)',
        'J' => 'RIF / Empresa (J)',
    ];

    // Remitente
    public string $sender_doc_type = 'V';
    public string $sender_doc_number = '';
    public string $sender_id_doc = '';
    public string $sender_name = '';
    public string $sender_phone = '';
    public string $sender_email = '';

    // Destinatario
    public string $recipient_doc_type = 'V';
    public string $recipient_doc_number = '';
    public string $recipient_id_doc = '';
    public string $recipient_name = '';
    public string $recipient_phone = '';
    public string $recipient_email = '';

    // Ruta
    public string $origin_city = '';
    public string $origin_state = '';
    public string $destination_state = '';
    public string $destination_city = '';

    // Paquete
    public string $package_type = Package::TYPE_PAQUETE;
    public ?float $physical_weight_kg = null;
    public ?float $length_cm = null;
    public ?float $width_cm = null;
    public ?float $height_cm = null;
    public bool $is_fragile = false;
    public bool $has_insurance = false;
    public ?float $declared_value_usd = null;

    // Delivery
    public bool $requires_delivery = false;
    public string $delivery_address = '';
    public string $delivery_sector = '';
    public string $delivery_reference = '';

    // Cobro
    public string $payment_method = '';
    public bool $is_cod = false;
    public ?float $cod_amount_usd = null;

    // Autocompletado / registro de clientes por documento
    public bool $senderCustomerFound = false;
    public bool $recipientCustomerFound = false;
    public bool $showSenderCustomerModal = false;
    public bool $showRecipientCustomerModal = false;

    // Resultado tras registrar
    public ?int $createdPackageId = null;
    public ?string $createdTrackingNumber = null;
    public ?string $createdSecurityHash = null;
    public ?float $createdTotalUsd = null;
    public ?float $createdTotalVes = null;
    public ?float $createdDistanceKm = null;
    public ?float $createdDeliveryFeeUsd = null;

    /**
     * Copia de todos los datos ingresados, tomada justo antes de
     * limpiar el formulario (resetForm), para poder imprimir la
     * guía completa aun después de que el formulario se vacíe.
     */
    public array $printSnapshot = [];

    /**
     * Vista previa de tarifa, recalculada en vivo mientras se llena
     * el formulario. Null si aún no hay datos suficientes o si la
     * combinación de datos no es válida (ej. ciudad no encontrada).
     *
     * @var array{
     *   billable_weight_kg: float, volumetric_weight_kg: float,
     *   fragile_surcharge_usd: float, insurance_price_usd: float,
     *   total_price_usd: float, total_price_ves: float,
     * }|null
     */
    public ?array $pricePreview = null;

    public ?string $pricePreviewError = null;

    /**
     * Campos que, al cambiar, disparan un recálculo automático de
     * la tarifa (ya no existe un botón manual "Calcular precio").
     */
    protected array $pricingFields = [
        'destination_state', 'destination_city', 'physical_weight_kg',
        'length_cm', 'width_cm', 'height_cm', 'package_type',
        'is_fragile', 'has_insurance', 'declared_value_usd', 'requires_delivery',
    ];

    public function mount(): void
    {
        $ally = auth()->user()->resolveAlly();

        if (! $ally) {
            abort(403, 'Tu usuario no tiene una agencia aliada asociada.');
        }

        // La guía siempre se origina en la ciudad de la agencia.
        $this->origin_city = $ally->city;
        $this->origin_state = (string) ($ally->state ?? '');
    }

    protected function rules(): array
    {
        return [
            'sender_doc_type' => ['required', Rule::in(self::DOC_TYPES)],
            'sender_doc_number' => ['required', 'string', 'max:20'],
            'sender_id_doc' => ['required', 'string', 'max:30'],
            'sender_name' => ['required', 'string', 'max:150'],
            'sender_phone' => ['required', 'string', 'max:30'],
            'sender_email' => ['nullable', 'email', 'max:150'],

            'recipient_doc_type' => ['required', Rule::in(self::DOC_TYPES)],
            'recipient_doc_number' => ['required', 'string', 'max:20'],
            'recipient_id_doc' => ['required', 'string', 'max:30'],
            'recipient_name' => ['required', 'string', 'max:150'],
            'recipient_phone' => ['required', 'string', 'max:30'],
            'recipient_email' => ['nullable', 'email', 'max:150'],

            'destination_state' => ['required', 'string', Rule::in(array_keys(config('venezuela.states', [])))],
            'destination_city' => [
                'required',
                'string',
                Rule::in($this->citiesForSelectedState()),
            ],

            'requires_delivery' => ['boolean'],
            'delivery_address' => ['nullable', 'string', 'max:1000', 'required_if:requires_delivery,true'],
            'delivery_sector' => ['nullable', 'string', 'max:255', 'required_if:requires_delivery,true'],
            'delivery_reference' => ['nullable', 'string', 'max:1000'],

            'package_type' => ['required', 'in:' . implode(',', Package::TYPES)],
            'physical_weight_kg' => ['required', 'numeric', 'min:0.01'],
            'length_cm' => ['nullable', 'numeric', 'min:0'],
            'width_cm' => ['nullable', 'numeric', 'min:0'],
            'height_cm' => ['nullable', 'numeric', 'min:0'],

            'is_fragile' => ['boolean'],
            'has_insurance' => ['boolean'],
            'declared_value_usd' => ['nullable', 'required_if:has_insurance,true', 'numeric', 'min:0.01'],

            'payment_method' => [$this->is_cod ? 'nullable' : 'required', 'in:efectivo_usd,efectivo_ves,pago_movil,transferencia,zelle'],

            'is_cod' => ['boolean'],
            'cod_amount_usd' => ['nullable', 'required_if:is_cod,true', 'numeric', 'min:0.01'],
        ];
    }

    protected function messages(): array
    {
        return [
            'sender_doc_number.required' => 'Ingresa el número de documento del remitente.',
            'recipient_doc_number.required' => 'Ingresa el número de documento del destinatario.',
            'sender_email.email' => 'El correo del remitente no es válido.',
            'recipient_email.email' => 'El correo del destinatario no es válido.',
            'declared_value_usd.required_if' => 'Indica el valor declarado para asegurar el envío.',
            'cod_amount_usd.required_if' => 'Completa ciudad destino y peso para calcular el monto a cobrar.',
            'destination_state.required' => 'Selecciona el estado destino.',
            'destination_city.required' => 'Selecciona la ciudad destino.',
            'delivery_address.required_if' => 'Indica la dirección exacta de entrega.',
            'delivery_sector.required_if' => 'Indica el sector o urbanización.',
        ];
    }

    /**
     * Livewire llama esto cada vez que cambia una propiedad pública
     * con wire:model.live.
     */
    public function updated(string $property): void
    {
        if ($property === 'destination_state') {
            $this->destination_city = '';
        }

        if ($property === 'requires_delivery' && ! $this->requires_delivery) {
            $this->delivery_address = '';
            $this->delivery_sector = '';
            $this->delivery_reference = '';
        }

        if ($property === 'is_cod') {
            // Si es cobro contra entrega, no se define método de pago en taquilla.
            $this->payment_method = $this->is_cod ? '' : $this->payment_method;

            // Si el destino y el peso ya estaban completos, el monto
            // COD debe llenarse de inmediato con la tarifa ya
            // conocida, sin esperar a que cambie otro campo.
            $this->refreshPricePreview(app(TariffService::class));
        }

        if (in_array($property, ['sender_doc_type', 'sender_doc_number'], true)) {
            $this->refreshDocId('sender');
            $this->autofillCustomer('sender');
        }

        if (in_array($property, ['recipient_doc_type', 'recipient_doc_number'], true)) {
            $this->refreshDocId('recipient');
            $this->autofillCustomer('recipient');
        }

        // Ya no hay botón "Calcular precio": la tarifa se recalcula
        // sola apenas cambia cualquier dato que la afecte.
        if (in_array($property, $this->pricingFields, true)) {
            $this->refreshPricePreview(app(TariffService::class));
        }
    }

    /**
     * Reconstruye el documento completo (ej. "V-12345678") a partir
     * del tipo seleccionado y el número ingresado.
     */
    protected function refreshDocId(string $prefix): void
    {
        $type = strtoupper($prefix === 'sender' ? $this->sender_doc_type : $this->recipient_doc_type);
        $number = trim($prefix === 'sender' ? $this->sender_doc_number : $this->recipient_doc_number);

        $idDoc = $number !== '' ? "{$type}-{$number}" : '';

        if ($prefix === 'sender') {
            $this->sender_id_doc = $idDoc;
        } else {
            $this->recipient_id_doc = $idDoc;
        }
    }

    /**
     * Busca un cliente ya registrado por su documento completo.
     *
     * - Si existe: autocompleta nombre, teléfono y correo, y cierra
     *   el modal de registro si estaba abierto.
     * - Si no existe (y el número ya tiene longitud razonable):
     *   limpia los campos y abre el modal para capturar sus datos.
     */
    protected function autofillCustomer(string $prefix): void
    {
        $number = trim($prefix === 'sender' ? $this->sender_doc_number : $this->recipient_doc_number);
        $idDoc = $prefix === 'sender' ? $this->sender_id_doc : $this->recipient_id_doc;

        $foundProperty = $prefix === 'sender' ? 'senderCustomerFound' : 'recipientCustomerFound';
        $modalProperty = $prefix === 'sender' ? 'showSenderCustomerModal' : 'showRecipientCustomerModal';

        // Evita consultar la BD o abrir el modal con cada tecla
        // cuando aún no hay suficientes caracteres para un
        // documento real.
        if (strlen($number) < 5) {
            $this->$foundProperty = false;
            $this->$modalProperty = false;

            return;
        }

        $customer = Customer::where('id_doc', $idDoc)->first();

        if ($customer) {
            $this->$foundProperty = true;
            $this->$modalProperty = false;

            if ($prefix === 'sender') {
                $this->sender_name = $customer->name;
                $this->sender_phone = $customer->phone;
                $this->sender_email = (string) ($customer->email ?? '');
            } else {
                $this->recipient_name = $customer->name;
                $this->recipient_phone = $customer->phone;
                $this->recipient_email = (string) ($customer->email ?? '');
            }

            return;
        }

        // No existe: pedimos sus datos en el modal.
        $this->$foundProperty = false;
        $this->$modalProperty = true;

        if ($prefix === 'sender') {
            $this->sender_name = '';
            $this->sender_phone = '';
            $this->sender_email = '';
        } else {
            $this->recipient_name = '';
            $this->recipient_phone = '';
            $this->recipient_email = '';
        }
    }

    /**
     * Reabre el modal de registro para editar los datos del
     * remitente (ej. si ya está resuelto pero se equivocaron).
     */
    public function openSenderCustomerModal(): void
    {
        $this->showSenderCustomerModal = true;
    }

    public function openRecipientCustomerModal(): void
    {
        $this->showRecipientCustomerModal = true;
    }

    public function saveSenderCustomer(): void
    {
        $this->validate([
            'sender_name' => ['required', 'string', 'max:150'],
            'sender_phone' => ['required', 'string', 'max:30'],
            'sender_email' => ['nullable', 'email', 'max:150'],
        ]);

        $this->showSenderCustomerModal = false;
    }

    public function saveRecipientCustomer(): void
    {
        $this->validate([
            'recipient_name' => ['required', 'string', 'max:150'],
            'recipient_phone' => ['required', 'string', 'max:30'],
            'recipient_email' => ['nullable', 'email', 'max:150'],
        ]);

        $this->showRecipientCustomerModal = false;
    }

    protected function refreshPricePreview(TariffService $tariffService): void
    {
        $this->pricePreviewError = null;
        $this->pricePreview = null;

        if (empty($this->destination_city) || empty($this->physical_weight_kg)) {
            $this->cod_amount_usd = null;

            return;
        }

        try {
            $pricing = $tariffService->calculate(
                originCity: $this->origin_city,
                destinationCity: $this->destination_city,
                packageType: $this->package_type,
                physicalWeightKg: $this->physical_weight_kg,
                lengthCm: $this->length_cm,
                widthCm: $this->width_cm,
                heightCm: $this->height_cm,
                isFragile: $this->is_fragile,
                hasInsurance: $this->has_insurance,
                declaredValueUsd: $this->declared_value_usd,
                originState: $this->origin_state ?: null,
                destinationState: $this->destination_state ?: null,
                requiresDelivery: $this->requires_delivery,
            );

            $this->pricePreview = $pricing;

            // El monto a cobrar en destino (COD) es el mismo total
            // calculado; no se escribe a mano.
            if ($this->is_cod) {
                $this->cod_amount_usd = $pricing['total_price_usd'];
            }
        } catch (\Throwable $e) {
            // No interrumpimos el llenado del formulario; solo no
            // mostramos preview hasta que los datos sean válidos.
            $this->pricePreviewError = $e->getMessage();
            $this->cod_amount_usd = null;
        }
    }

    public function save(PackageService $packageService): void
    {
        $data = $this->validate();

        $ally = auth()->user()->resolveAlly();

        // Registramos o actualizamos al remitente y destinatario como
        // clientes conocidos, para que la próxima vez que se use su
        // documento se autocompleten sus datos.
        Customer::updateOrCreate(
            ['id_doc' => $this->sender_id_doc],
            [
                'name' => $this->sender_name,
                'phone' => $this->sender_phone,
                'email' => $this->sender_email ?: null,
            ],
        );

        Customer::updateOrCreate(
            ['id_doc' => $this->recipient_id_doc],
            [
                'name' => $this->recipient_name,
                'phone' => $this->recipient_phone,
                'email' => $this->recipient_email ?: null,
            ],
        );

        $package = $packageService->createPackage([
            ...$data,
            'ally_id' => $ally->id,
            'origin_city' => $this->origin_city,
            'origin_state' => $this->origin_state ?: null,
        ], auth()->id());

        $this->createdPackageId = $package->id;
        $this->createdTrackingNumber = $package->tracking_number;
        $this->createdSecurityHash = $package->security_hash;
        $this->createdTotalUsd = (float) $package->total_price_usd;
        $this->createdTotalVes = (float) $package->total_price_ves;
        $this->createdDistanceKm = isset($package->distance_km) ? (float) $package->distance_km : ($this->pricePreview['distance_km'] ?? null);
        $this->createdDeliveryFeeUsd = isset($package->delivery_fee_usd) ? (float) $package->delivery_fee_usd : ($this->pricePreview['delivery_fee_usd'] ?? 0);

        // Snapshot completo para el comprobante imprimible, tomado
        // antes de vaciar el formulario en resetForm().
        $this->printSnapshot = [
            'sender_name' => $this->sender_name,
            'sender_id_doc' => $this->sender_id_doc,
            'sender_phone' => $this->sender_phone,
            'sender_email' => $this->sender_email,
            'recipient_name' => $this->recipient_name,
            'recipient_id_doc' => $this->recipient_id_doc,
            'recipient_phone' => $this->recipient_phone,
            'recipient_email' => $this->recipient_email,
            'origin_city' => $this->origin_city,
            'origin_state' => $this->origin_state,
            'destination_state' => $this->destination_state,
            'destination_city' => $this->destination_city,
            'package_type' => $this->package_type,
            'physical_weight_kg' => $this->physical_weight_kg,
            'length_cm' => $this->length_cm,
            'width_cm' => $this->width_cm,
            'height_cm' => $this->height_cm,
            'is_fragile' => $this->is_fragile,
            'has_insurance' => $this->has_insurance,
            'declared_value_usd' => $this->declared_value_usd,
            'requires_delivery' => $this->requires_delivery,
            'delivery_address' => $this->delivery_address,
            'delivery_sector' => $this->delivery_sector,
            'delivery_reference' => $this->delivery_reference,
            'is_cod' => $this->is_cod,
            'payment_method' => $this->payment_method,
            'cod_amount_usd' => $this->cod_amount_usd,
        ];

        $this->dispatch('package-created', trackingNumber: $package->tracking_number);

        $this->resetForm();
    }

    public function registerAnother(): void
    {
        $this->createdPackageId = null;
        $this->createdTrackingNumber = null;
        $this->createdSecurityHash = null;
        $this->createdTotalUsd = null;
        $this->createdTotalVes = null;
        $this->createdDistanceKm = null;
        $this->createdDeliveryFeeUsd = null;
        $this->printSnapshot = [];
    }

    /**
     * Etiquetas legibles para los métodos de pago, usadas también
     * en el comprobante imprimible.
     *
     * @return array<string, string>
     */
    public static function paymentMethodLabels(): array
    {
        return [
            'efectivo_usd' => 'Efectivo (USD)',
            'efectivo_ves' => 'Efectivo (VES)',
            'pago_movil' => 'Pago móvil',
            'transferencia' => 'Transferencia',
            'zelle' => 'Zelle',
        ];
    }

    protected function resetForm(): void
    {
        $ally = auth()->user()->resolveAlly();

        $this->reset([
            'sender_doc_type', 'sender_doc_number', 'sender_id_doc',
            'sender_name', 'sender_phone', 'sender_email',
            'recipient_doc_type', 'recipient_doc_number', 'recipient_id_doc',
            'recipient_name', 'recipient_phone', 'recipient_email',
            'destination_state', 'destination_city',
            'requires_delivery', 'delivery_address', 'delivery_sector', 'delivery_reference',
            'physical_weight_kg', 'length_cm', 'width_cm', 'height_cm',
            'is_fragile', 'has_insurance', 'declared_value_usd',
            'payment_method', 'is_cod', 'cod_amount_usd',
            'senderCustomerFound', 'recipientCustomerFound',
            'showSenderCustomerModal', 'showRecipientCustomerModal',
        ]);

        $this->sender_doc_type = 'V';
        $this->recipient_doc_type = 'V';
        $this->package_type = Package::TYPE_PAQUETE;
        $this->origin_city = $ally->city;
        $this->origin_state = (string) ($ally->state ?? '');
        $this->pricePreview = null;
        $this->pricePreviewError = null;
    }

    protected function citiesForSelectedState(): array
    {
        return config('venezuela.states')[$this->destination_state] ?? [];
    }

    public function render()
    {
        return view('livewire.ally.package-create');
    }
}
