<?php

use App\Models\Ally;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\User;
use App\Notifications\AccountPendingApproval;
use App\Notifications\WelcomeVerificationToken;
use App\Services\VenezuelaLocationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.guest')] class extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $role = 'cliente';

    /**
     * Datos adicionales para aliados.
     */
    public string $business_name = '';
    public string $rif = '';
    public string $state = '';
    public string $city = '';
    public string $address = '';
    public $storefront_photo = null;
    public $rif_document = null;
    public $mercantile_registry_document = null;
    public $owner_id_document = null;
    public ?float $latitude = null;
    public ?float $longitude = null;

    public array $states = [];
    public array $cities = [];

    /**
     * Datos adicionales para repartidores.
     */
    public string $vehicle_plate = '';
    public string $vehicle_type = '';
    public string $phone = '';
    public $license_photo = null;
    public $id_photo = null;
    public $vehicle_registration_photo = null;

    /**
     * Datos adicionales para clientes.
     *
     * id_doc es la clave que vincula este usuario con su(s) guía(s):
     * los paquetes se buscan por recipient_id_doc, así que sin este
     * dato el panel de Cliente nunca podría encontrar sus envíos.
     */
    public string $id_doc = '';

    /**
     * Carga los estados disponibles.
     */
    public function mount(
        VenezuelaLocationService $locationService
    ): void {
        $this->states = $locationService->states();

        $requestedRole = request()->query('role');

        if (in_array($requestedRole, ['cliente', 'repartidor', 'aliado'], true)) {
            $this->role = $requestedRole;
        }
    }

    /**
     * Actualiza las ciudades cuando cambia el estado.
     */
    public function updatedState(
        VenezuelaLocationService $locationService
    ): void {
        $this->city = '';

        $this->cities = $this->state !== ''
            ? $locationService->citiesByState($this->state)
            : [];
    }

    /**
     * Recibe la posición elegida por clic en el mapa (evento de Alpine/Leaflet).
     */
    public function setLocationFromMap(float $lat, float $lng): void
    {
        $this->latitude = round($lat, 7);
        $this->longitude = round($lng, 7);
    }

    /**
     * Maneja el registro.
     */
    public function register(): void
    {
        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:' . User::class,
            ],

            'role' => [
                'required',
                'in:cliente,repartidor,aliado',
            ],

            'password' => [
                'required',
                'string',
                'confirmed',
                Rules\Password::defaults(),
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | VALIDACIÓN DE ALIADO
        |--------------------------------------------------------------------------
        */

        if ($this->role === User::ROLE_ALIADO) {
            $rules = array_merge($rules, [
                'business_name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'rif' => [
                    'required',
                    'string',
                    'max:20',
                    'unique:allies,rif',
                ],

                'state' => [
                    'required',
                    'string',
                ],

                'city' => [
                    'required',
                    'string',
                ],

                'address' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'storefront_photo' => [
                    'required',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:4096',
                ],

                /*
                 * Documentos de verificación (RIF, registro
                 * mercantil, cédula del titular). No forzamos
                 * 'image' aquí: el aliado puede subir una foto o un
                 * PDF/documento escaneado, según lo que tenga a
                 * mano. 'mimes' es una lista blanca, así que
                 * cualquier otro tipo de archivo (.exe, .zip, .rar,
                 * etc.) queda rechazado automáticamente sin
                 * necesidad de una lista negra.
                 */
                'rif_document' => [
                    'required',
                    'file',
                    'mimes:jpg,jpeg,png,webp,pdf,doc,docx',
                    'max:8192',
                ],

                'mercantile_registry_document' => [
                    'required',
                    'file',
                    'mimes:jpg,jpeg,png,webp,pdf,doc,docx',
                    'max:8192',
                ],

                'owner_id_document' => [
                    'required',
                    'file',
                    'mimes:jpg,jpeg,png,webp,pdf,doc,docx',
                    'max:8192',
                ],

                'latitude' => [
                    'required',
                    'numeric',
                    'between:-90,90',
                ],

                'longitude' => [
                    'required',
                    'numeric',
                    'between:-180,180',
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDACIÓN DE REPARTIDOR
        |--------------------------------------------------------------------------
        */

        if ($this->role === User::ROLE_REPARTIDOR) {
            $rules = array_merge($rules, [
                'vehicle_plate' => [
                    'required',
                    'string',
                    'max:20',
                    'unique:drivers,vehicle_plate',
                ],

                'vehicle_type' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'phone' => [
                    'required',
                    'string',
                    'max:30',
                ],

                'license_photo' => [
                    'required',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:4096',
                ],

                'id_photo' => [
                    'required',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:4096',
                ],

                'vehicle_registration_photo' => [
                    'required',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:4096',
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDACIÓN DE CLIENTE
        |--------------------------------------------------------------------------
        */

        if ($this->role === User::ROLE_CLIENTE) {
            $rules = array_merge($rules, [
                'id_doc' => [
                    'required',
                    'string',
                    'max:30',

                    /*
                     * SEGURIDAD:
                     * El panel de Cliente concede acceso al historial
                     * de paquetes de una cédula únicamente por
                     * coincidencia de id_doc. Si permitiéramos que
                     * cualquier persona "reclame" una cédula ya
                     * asociada a un customer con contacto real, un
                     * atacante podría ver guías, direcciones de
                     * entrega y aceptar/rechazar entregas de otra
                     * persona con solo conocer o adivinar su cédula,
                     * además de sobrescribir su nombre/teléfono/email.
                     *
                     * Antes esto se decidía mirando si el Customer ya
                     * tenía un email — pero un aliado puede haber
                     * tecleado el email real de esa persona al
                     * despachar una guía sin que nadie haya
                     * "reclamado" la cédula todavía, lo que bloqueaba
                     * el registro de su verdadero dueño. Ahora se
                     * decide por user_id: solo bloqueamos si otra
                     * cuenta ya demostró ser dueña de esta cédula
                     * registrándose con ella.
                     */
                    function (string $attribute, mixed $value, \Closure $fail) {
                        $existing = Customer::where('id_doc', $value)->first();

                        if ($existing && $existing->user_id !== null) {
                            $fail(
                                'Ya existe una cuenta de cliente registrada '
                                . 'con esta cédula. Si es tuya, inicia sesión '
                                . 'o contacta a soporte.'
                            );
                        }
                    },
                ],

                'phone' => [
                    'required',
                    'string',
                    'max:30',
                ],
            ]);
        }

        $validated = $this->validate($rules);

        /*
        |--------------------------------------------------------------------------
        | CREAR USUARIO
        |--------------------------------------------------------------------------
        */

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        event(new Registered($user));

        /*
        |--------------------------------------------------------------------------
        | CREAR ALIADO
        |--------------------------------------------------------------------------
        */

        if ($user->isAliado()) {
            /*
             * Si Ally::create() falla a mitad de camino (ej. un error
             * al guardar alguno de los documentos), sin esta
             * transacción quedaría un User con rol "aliado" ya creado
             * pero sin su Ally correspondiente: una cuenta huérfana
             * que podría iniciar sesión normalmente después. Si eso
             * pasa, deshacemos también la creación del User en vez de
             * dejarlo a medio registrar.
             */
            try {
                DB::transaction(function () use ($user, $validated) {
                    $storefrontPhotoPath = $this->storefront_photo->store('allies', 'documents');

                    Ally::create([
                        'user_id' => $user->id,
                        'business_name' => $validated['business_name'],
                        'rif' => $validated['rif'],
                        'state' => $validated['state'],
                        'city' => $validated['city'],
                        'address' => $validated['address'],
                        'storefront_photo_path' => $storefrontPhotoPath,
                        'rif_document_path' => $this->rif_document->store('allies', 'documents'),
                        'mercantile_registry_document_path' => $this->mercantile_registry_document->store('allies', 'documents'),
                        'owner_id_document_path' => $this->owner_id_document->store('allies', 'documents'),
                        'latitude' => $validated['latitude'],
                        'longitude' => $validated['longitude'],
                        'commission_percentage' => 10.00,

                        // Un aliado nuevo comienza como PENDIENTE.
                        'status' => Ally::STATUS_PENDING,
                    ]);
                });
            } catch (\Throwable $e) {
                $user->delete();

                throw $e;
            }

            $user->notify(new AccountPendingApproval('Aliado'));

            Auth::login($user);

            $this->redirect(
                route('ally.dashboard', absolute: false),
                navigate: true
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | CREAR REPARTIDOR
        |--------------------------------------------------------------------------
        */

        if ($user->isChofer()) {
            // Ver comentario equivalente en la rama de Aliado: sin
            // esta transacción, un fallo a mitad de camino dejaría un
            // User con rol "repartidor" sin su Driver correspondiente.
            try {
                DB::transaction(function () use ($user, $validated) {
                    Driver::create([
                        'user_id' => $user->id,
                        'vehicle_plate' => $validated['vehicle_plate'],
                        'vehicle_type' => $validated['vehicle_type'],
                        'phone' => $validated['phone'],
                        'driver_type' => Driver::TYPE_DELIVERY,
                        'license_photo_path' => $this->license_photo->store('drivers', 'documents'),
                        'id_photo_path' => $this->id_photo->store('drivers', 'documents'),
                        'vehicle_registration_photo_path' => $this->vehicle_registration_photo->store('drivers', 'documents'),

                        // Un repartidor nuevo comienza como PENDIENTE, igual
                        // que un aliado, hasta que un admin lo apruebe.
                        'status' => Driver::STATUS_PENDING,
                    ]);
                });
            } catch (\Throwable $e) {
                $user->delete();

                throw $e;
            }

            $user->notify(new AccountPendingApproval('Repartidor'));

            Auth::login($user);

            $this->redirect(
                route('repartidor.dashboard', absolute: false),
                navigate: true
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | CLIENTE
        |--------------------------------------------------------------------------
        */

        /*
         * El panel de Cliente busca sus guías por recipient_id_doc a
         * través de este registro en customers. Si un aliado ya había
         * registrado a esta persona como destinatario de una guía
         * anterior, el customer ya existe con este id_doc: lo
         * actualizamos en vez de duplicarlo, para que el historial de
         * paquetes previos también quede visible.
         *
         * user_id queda fijado a ESTE usuario: la validación de arriba
         * ya garantizó que nadie más lo había reclamado todavía.
         */
        Customer::updateOrCreate(
            ['id_doc' => $validated['id_doc']],
            [
                'user_id' => $user->id,
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'email' => $validated['email'],
            ]
        );

        // Generar y guardar el código de verificación.
        $plainToken = $user->generateVerificationToken();

        // Enviar el código al correo del cliente.
        $user->notify(new WelcomeVerificationToken($plainToken));

        // Guardar temporalmente el usuario pendiente de verificación.
        session([
            'pending_verification_user_id' => $user->id,
        ]);

        // El cliente debe verificar su cuenta antes de entrar al panel.
        $this->redirect(
            route('verify-account', absolute: false),
            navigate: true
        );
    }
};
?>

<div>
    <div class="mb-8 lg:hidden">
        <x-venexpress-logo size="md" />
    </div>

    <h1 class="font-display text-2xl font-bold text-blue-950">
        Crea tu cuenta
    </h1>

    <p class="mt-1.5 text-sm text-gray-500">
        Regístrate para gestionar tus guías, tarifas o entregas en VenExpress.
    </p>

    <form wire:submit="register" class="mt-8 space-y-5">

        {{-- NOMBRE --}}
        <div>
            <x-input-label
                for="name"
                value="Nombre completo"
            />

            <x-text-input
                wire:model="name"
                id="name"
                class="block mt-1.5 w-full"
                type="text"
                name="name"
                required
                autofocus
                autocomplete="name"
                placeholder="Tu nombre"
            />

            <x-input-error
                :messages="$errors->get('name')"
                class="mt-2"
            />
        </div>

        {{-- EMAIL --}}
        <div>
            <x-input-label
                for="email"
                value="Correo electrónico"
            />

            <x-text-input
                wire:model="email"
                id="email"
                class="block mt-1.5 w-full"
                type="email"
                name="email"
                required
                autocomplete="username"
                placeholder="tu@correo.com"
            />

            <x-input-error
                :messages="$errors->get('email')"
                class="mt-2"
            />
        </div>

        {{-- ROL --}}
        <div>
            <x-input-label
                for="role"
                value="Tipo de usuario"
            />

            <select
                wire:model.live="role"
                id="role"
                name="role"
                class="block mt-1.5 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                required
            >
                <option value="cliente">
                    Cliente
                </option>

                <option value="repartidor">
                    Repartidor
                </option>

                <option value="aliado">
                    Punto aliado
                </option>
            </select>

            <x-input-error
                :messages="$errors->get('role')"
                class="mt-2"
            />
        </div>

        {{-- ====================================================== --}}
        {{-- DATOS DEL CLIENTE --}}
        {{-- ====================================================== --}}

        @if ($role === 'cliente')

            <div class="border-t border-gray-200 pt-5">
                <h2 class="text-sm font-semibold text-blue-950">
                    Datos de contacto
                </h2>

                <p class="mt-1 text-xs text-gray-500">
                    Usamos tu cédula/RIF para mostrarte los envíos donde apareces como destinatario.
                </p>
            </div>

            {{-- CÉDULA / RIF --}}
            <div>
                <x-input-label
                    for="id_doc"
                    value="Cédula o RIF"
                />

                <x-text-input
                    wire:model="id_doc"
                    id="id_doc"
                    class="block mt-1.5 w-full"
                    type="text"
                    placeholder="V-12345678"
                />

                <x-input-error
                    :messages="$errors->get('id_doc')"
                    class="mt-2"
                />
            </div>

            {{-- TELÉFONO --}}
            <div>
                <x-input-label
                    for="client_phone"
                    value="Teléfono"
                />

                <x-text-input
                    wire:model="phone"
                    id="client_phone"
                    class="block mt-1.5 w-full"
                    type="text"
                    placeholder="+58 412 1234567"
                />

                <x-input-error
                    :messages="$errors->get('phone')"
                    class="mt-2"
                />
            </div>

        @endif

        {{-- ====================================================== --}}
        {{-- DATOS DEL ALIADO --}}
        {{-- ====================================================== --}}

        @if ($role === 'aliado')

            <div class="border-t border-gray-200 pt-5">
                <h2 class="text-sm font-semibold text-blue-950">
                    Información del punto aliado
                </h2>

                <p class="mt-1 text-xs text-gray-500">
                    Estos datos serán revisados por VenExpress antes de activar el comercio.
                </p>
            </div>

            {{-- EMPRESA --}}
            <div>
                <x-input-label
                    for="business_name"
                    value="Nombre comercial"
                />

                <x-text-input
                    wire:model="business_name"
                    id="business_name"
                    class="block mt-1.5 w-full"
                    type="text"
                    placeholder="Ej. Inversiones ABC C.A."
                />

                <x-input-error
                    :messages="$errors->get('business_name')"
                    class="mt-2"
                />
            </div>

            {{-- RIF --}}
            <div>
                <x-input-label
                    for="rif"
                    value="RIF"
                />

                <x-text-input
                    wire:model="rif"
                    id="rif"
                    class="block mt-1.5 w-full"
                    type="text"
                    placeholder="J-12345678-9"
                />

                <x-input-error
                    :messages="$errors->get('rif')"
                    class="mt-2"
                />
            </div>

            {{-- ESTADO --}}
            <div>
                <x-input-label
                    for="state"
                    value="Estado"
                />

                <select
                    wire:model.live="state"
                    id="state"
                    name="state"
                    class="block mt-1.5 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    required
                >
                    <option value="">
                        Seleccionar estado
                    </option>

                    @foreach ($states as $stateOption)
                        <option value="{{ $stateOption }}">
                            {{ $stateOption }}
                        </option>
                    @endforeach
                </select>

                <x-input-error
                    :messages="$errors->get('state')"
                    class="mt-2"
                />
            </div>

            {{-- CIUDAD --}}
            <div>
                <x-input-label
                    for="city"
                    value="Ciudad"
                />

                <select
                    wire:model.live="city"
                    id="city"
                    name="city"
                    class="block mt-1.5 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    @disabled($state === '')
                    required
                >
                    <option value="">
                        {{ $state === ''
                            ? 'Primero selecciona un estado'
                            : 'Seleccionar ciudad' }}
                    </option>

                    @foreach ($cities as $cityOption)
                        <option value="{{ $cityOption }}">
                            {{ $cityOption }}
                        </option>
                    @endforeach
                </select>

                <x-input-error
                    :messages="$errors->get('city')"
                    class="mt-2"
                />
            </div>

            {{-- DIRECCIÓN --}}
            <div>
                <x-input-label
                    for="address"
                    value="Dirección"
                />

                <x-text-input
                    wire:model="address"
                    id="address"
                    class="block mt-1.5 w-full"
                    type="text"
                    placeholder="Dirección del establecimiento"
                />

                <x-input-error
                    :messages="$errors->get('address')"
                    class="mt-2"
                />
            </div>

            {{--
                DOCUMENTOS DE VERIFICACIÓN (RIF, registro mercantil,
                cédula del titular). A diferencia de la foto de
                fachada, aquí el aliado puede subir una foto o un
                PDF/documento escaneado según lo que tenga a mano, así
                que no forzamos accept="image/*" ni mostramos una
                vista previa de imagen para cualquier archivo (un PDF
                no se puede previsualizar como <img>).
            --}}
            <div>
                <p class="text-sm font-medium text-gray-700">
                    Documentos de verificación
                </p>
                <p class="mt-1 text-xs text-gray-500">
                    Foto o documento escaneado (imagen, PDF o Word). Máx. 8MB por archivo.
                </p>
            </div>

            {{-- RIF --}}
            <div>
                <x-input-label
                    for="rif_document"
                    value="RIF"
                />

                <input
                    type="file"
                    wire:model="rif_document"
                    id="rif_document"
                    accept="image/*,.pdf,.doc,.docx"
                    class="block mt-1.5 w-full text-sm text-gray-600
                           file:mr-4 file:py-2 file:px-4 file:rounded-md
                           file:border-0 file:text-sm file:font-semibold
                           file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                />

                <p class="mt-1 text-xs text-gray-500" wire:loading wire:target="rif_document">
                    Subiendo archivo...
                </p>

                @if ($rif_document)
                    @if (str_starts_with($rif_document->getMimeType(), 'image/'))
                        <img src="{{ $rif_document->temporaryUrl() }}" class="mt-2 h-24 rounded-lg object-cover" alt="Vista previa">
                    @else
                        <p class="mt-2 text-xs text-gray-600">📄 {{ $rif_document->getClientOriginalName() }}</p>
                    @endif
                @endif

                <x-input-error
                    :messages="$errors->get('rif_document')"
                    class="mt-2"
                />
            </div>

            {{-- REGISTRO MERCANTIL --}}
            <div>
                <x-input-label
                    for="mercantile_registry_document"
                    value="Registro mercantil"
                />

                <input
                    type="file"
                    wire:model="mercantile_registry_document"
                    id="mercantile_registry_document"
                    accept="image/*,.pdf,.doc,.docx"
                    class="block mt-1.5 w-full text-sm text-gray-600
                           file:mr-4 file:py-2 file:px-4 file:rounded-md
                           file:border-0 file:text-sm file:font-semibold
                           file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                />

                <p class="mt-1 text-xs text-gray-500" wire:loading wire:target="mercantile_registry_document">
                    Subiendo archivo...
                </p>

                @if ($mercantile_registry_document)
                    @if (str_starts_with($mercantile_registry_document->getMimeType(), 'image/'))
                        <img src="{{ $mercantile_registry_document->temporaryUrl() }}" class="mt-2 h-24 rounded-lg object-cover" alt="Vista previa">
                    @else
                        <p class="mt-2 text-xs text-gray-600">📄 {{ $mercantile_registry_document->getClientOriginalName() }}</p>
                    @endif
                @endif

                <x-input-error
                    :messages="$errors->get('mercantile_registry_document')"
                    class="mt-2"
                />
            </div>

            {{-- CÉDULA DEL TITULAR --}}
            <div>
                <x-input-label
                    for="owner_id_document"
                    value="Cédula del titular"
                />

                <input
                    type="file"
                    wire:model="owner_id_document"
                    id="owner_id_document"
                    accept="image/*,.pdf,.doc,.docx"
                    class="block mt-1.5 w-full text-sm text-gray-600
                           file:mr-4 file:py-2 file:px-4 file:rounded-md
                           file:border-0 file:text-sm file:font-semibold
                           file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                />

                <p class="mt-1 text-xs text-gray-500" wire:loading wire:target="owner_id_document">
                    Subiendo archivo...
                </p>

                @if ($owner_id_document)
                    @if (str_starts_with($owner_id_document->getMimeType(), 'image/'))
                        <img src="{{ $owner_id_document->temporaryUrl() }}" class="mt-2 h-24 rounded-lg object-cover" alt="Vista previa">
                    @else
                        <p class="mt-2 text-xs text-gray-600">📄 {{ $owner_id_document->getClientOriginalName() }}</p>
                    @endif
                @endif

                <x-input-error
                    :messages="$errors->get('owner_id_document')"
                    class="mt-2"
                />
            </div>

            {{-- FOTO DE FACHADA --}}
            <div>
                <x-input-label
                    for="storefront_photo"
                    value="Foto de la fachada del local"
                />

                <input
                    type="file"
                    wire:model="storefront_photo"
                    id="storefront_photo"
                    accept="image/*"
                    class="block mt-1.5 w-full text-sm text-gray-600
                           file:mr-4 file:py-2 file:px-4 file:rounded-md
                           file:border-0 file:text-sm file:font-semibold
                           file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                />

                <p class="mt-1 text-xs text-gray-500" wire:loading wire:target="storefront_photo">
                    Subiendo foto...
                </p>

                @if ($storefront_photo)
                    <img src="{{ $storefront_photo->temporaryUrl() }}" class="mt-2 h-24 rounded-lg object-cover" alt="Vista previa">
                @endif

                <x-input-error
                    :messages="$errors->get('storefront_photo')"
                    class="mt-2"
                />
            </div>

            {{-- UBICACIÓN EN EL MAPA --}}
            <div>
                <x-input-label value="Ubicación exacta en el mapa" />

                <p class="mt-1 text-xs text-gray-500">
                    Haz clic en el mapa sobre la ubicación exacta del establecimiento.
                </p>

                <div
                    x-data="registerLocationMap({
                        lat: @js($latitude ?? 10.4806),
                        lng: @js($longitude ?? -66.9036),
                        hasPoint: @js((bool) $latitude),
                    })"
                    x-init="init($el)"
                    wire:ignore
                    class="mt-2 rounded-xl overflow-hidden border border-gray-300"
                    style="height: 240px;"
                ></div>

                <x-input-error
                    :messages="$errors->get('latitude')"
                    class="mt-2"
                />
            </div>

        @endif

        {{-- ====================================================== --}}
        {{-- DATOS DEL REPARTIDOR --}}
        {{-- ====================================================== --}}

        @if ($role === 'repartidor')

            <div class="border-t border-gray-200 pt-5">
                <h2 class="text-sm font-semibold text-blue-950">
                    Información del repartidor
                </h2>
            </div>

            {{-- PLACA --}}
            <div>
                <x-input-label
                    for="vehicle_plate"
                    value="Placa del vehículo"
                />

                <x-text-input
                    wire:model="vehicle_plate"
                    id="vehicle_plate"
                    class="block mt-1.5 w-full"
                    type="text"
                    placeholder="ABC123"
                />

                <x-input-error
                    :messages="$errors->get('vehicle_plate')"
                    class="mt-2"
                />
            </div>

            {{-- VEHÍCULO --}}
            <div>
                <x-input-label
                    for="vehicle_type"
                    value="Tipo de vehículo"
                />

                <x-text-input
                    wire:model="vehicle_type"
                    id="vehicle_type"
                    class="block mt-1.5 w-full"
                    type="text"
                    placeholder="Moto, automóvil, camioneta..."
                />

                <x-input-error
                    :messages="$errors->get('vehicle_type')"
                    class="mt-2"
                />
            </div>

            {{-- TELÉFONO --}}
            <div>
                <x-input-label
                    for="phone"
                    value="Teléfono"
                />

                <x-text-input
                    wire:model="phone"
                    id="phone"
                    class="block mt-1.5 w-full"
                    type="text"
                    placeholder="+58 412 1234567"
                />

                <x-input-error
                    :messages="$errors->get('phone')"
                    class="mt-2"
                />
            </div>

            <div class="border-t border-gray-200 pt-5">
                <h2 class="text-sm font-semibold text-blue-950">
                    Documentos
                </h2>

                <p class="mt-1 text-xs text-gray-500">
                    Estos documentos serán revisados por VenExpress antes de aprobar tu cuenta.
                </p>
            </div>

            {{-- FOTO DE LA LICENCIA --}}
            <div>
                <x-input-label
                    for="license_photo"
                    value="Foto de la licencia de conducir"
                />

                <input
                    type="file"
                    wire:model="license_photo"
                    id="license_photo"
                    accept="image/*"
                    class="block mt-1.5 w-full text-sm text-gray-600
                           file:mr-4 file:py-2 file:px-4 file:rounded-md
                           file:border-0 file:text-sm file:font-semibold
                           file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                />

                <p class="mt-1 text-xs text-gray-500" wire:loading wire:target="license_photo">
                    Subiendo foto...
                </p>

                @if ($license_photo)
                    <img src="{{ $license_photo->temporaryUrl() }}" class="mt-2 h-24 rounded-lg object-cover" alt="Vista previa">
                @endif

                <x-input-error
                    :messages="$errors->get('license_photo')"
                    class="mt-2"
                />
            </div>

            {{-- FOTO DE LA CÉDULA --}}
            <div>
                <x-input-label
                    for="id_photo"
                    value="Foto de la cédula de identidad"
                />

                <input
                    type="file"
                    wire:model="id_photo"
                    id="id_photo"
                    accept="image/*"
                    class="block mt-1.5 w-full text-sm text-gray-600
                           file:mr-4 file:py-2 file:px-4 file:rounded-md
                           file:border-0 file:text-sm file:font-semibold
                           file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                />

                <p class="mt-1 text-xs text-gray-500" wire:loading wire:target="id_photo">
                    Subiendo foto...
                </p>

                @if ($id_photo)
                    <img src="{{ $id_photo->temporaryUrl() }}" class="mt-2 h-24 rounded-lg object-cover" alt="Vista previa">
                @endif

                <x-input-error
                    :messages="$errors->get('id_photo')"
                    class="mt-2"
                />
            </div>

            {{-- CARNET DE CIRCULACIÓN --}}
            <div>
                <x-input-label
                    for="vehicle_registration_photo"
                    value="Foto del carnet de circulación"
                />

                <p class="mt-1 text-xs text-gray-500">
                    Debe corresponder a la placa {{ $vehicle_plate !== '' ? $vehicle_plate : 'indicada arriba' }}.
                </p>

                <input
                    type="file"
                    wire:model="vehicle_registration_photo"
                    id="vehicle_registration_photo"
                    accept="image/*"
                    class="block mt-1.5 w-full text-sm text-gray-600
                           file:mr-4 file:py-2 file:px-4 file:rounded-md
                           file:border-0 file:text-sm file:font-semibold
                           file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                />

                <p class="mt-1 text-xs text-gray-500" wire:loading wire:target="vehicle_registration_photo">
                    Subiendo foto...
                </p>

                @if ($vehicle_registration_photo)
                    <img src="{{ $vehicle_registration_photo->temporaryUrl() }}" class="mt-2 h-24 rounded-lg object-cover" alt="Vista previa">
                @endif

                <x-input-error
                    :messages="$errors->get('vehicle_registration_photo')"
                    class="mt-2"
                />
            </div>

        @endif

        {{-- ====================================================== --}}
        {{-- CONTRASEÑA --}}
        {{-- ====================================================== --}}

        <div>
            <x-input-label
                for="password"
                value="Contraseña"
            />

            <x-password-input
                wire:model="password"
                id="password"
                class="block mt-1.5"
                name="password"
                required
                autocomplete="new-password"
                placeholder="••••••••"
            />

            <x-input-error
                :messages="$errors->get('password')"
                class="mt-2"
            />
        </div>

        {{-- CONFIRMAR CONTRASEÑA --}}
        <div>
            <x-input-label
                for="password_confirmation"
                value="Confirmar contraseña"
            />

            <x-password-input
                wire:model="password_confirmation"
                id="password_confirmation"
                class="block mt-1.5"
                name="password_confirmation"
                required
                autocomplete="new-password"
                placeholder="••••••••"
            />

            <x-input-error
                :messages="$errors->get('password_confirmation')"
                class="mt-2"
            />
        </div>

        {{-- BOTÓN --}}
        <x-primary-button class="w-full py-3">
            Crear cuenta
        </x-primary-button>

    </form>

    <p class="mt-8 text-center text-sm text-gray-500">
        ¿Ya tienes una cuenta?

        <a
            href="{{ route('login') }}"
            class="font-semibold text-blue-700 hover:text-blue-950"
            wire:navigate
        >
            Inicia sesión
        </a>
    </p>
</div>
