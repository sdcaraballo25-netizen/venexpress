<?php

use App\Models\Ally;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Emprendedor;
use App\Models\User;
use App\Notifications\AccountPendingApproval;
use App\Notifications\WelcomeVerificationToken;
use App\Services\VenezuelaLocationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $role = 'cliente';

    /**
     * Cuando viene de "Continuar con Google" (ver mount()): nombre y
     * correo ya están confirmados por Google, así que se ocultan los
     * campos de nombre/correo/contraseña y se completa el resto del
     * formulario (rol + datos que Google no entrega) normalmente.
     *
     * #[Locked] impide que el cliente pueda enviar un valor propio
     * para estas dos props en la petición de Livewire que dispara
     * register() (sin esto, alguien podría forzar viaGoogle=true con
     * un googleId inventado). $name y $email no se bloquean aquí
     * porque siguen siendo editables en el registro manual; su
     * verificación va dentro de register() releyendo la sesión.
     */
    #[Locked]
    public bool $viaGoogle = false;

    #[Locked]
    public ?string $googleId = null;

    /**
     * Datos adicionales para aliados.
     *
     * Documentos de verificación (RIF, registro mercantil, cédula del
     * titular, fachada) y ubicación en el mapa ya NO se piden aquí:
     * se completan después desde "Mi Verificación"
     * (App\Livewire\Ally\Verificacion) una vez la cuenta existe, y la
     * ubicación exacta la fija un Admin desde su propio mapa
     * (App\Livewire\Admin\AlliesManager::editLocation()).
     */
    public string $business_name = '';
    public string $rif = '';
    public string $state = '';
    public string $city = '';
    public string $address = '';

    public array $states = [];
    public array $cities = [];

    /**
     * Datos adicionales para repartidores.
     *
     * Documentos de verificación (licencia, cédula, carnet de
     * circulación) ya NO se piden aquí: se completan después desde
     * "Mi Verificación" (App\Livewire\Driver\Verificacion) una vez la
     * cuenta existe.
     */
    public string $vehicle_plate = '';
    public string $vehicle_type = '';
    public string $phone = '';

    /**
     * Datos adicionales para clientes.
     *
     * id_doc es la clave que vincula este usuario con su(s) guía(s):
     * los paquetes se buscan por recipient_id_doc, así que sin este
     * dato el panel de Cliente nunca podría encontrar sus envíos.
     */
    public string $id_doc = '';

    /**
     * Datos adicionales para emprendedores (módulo de marketplace).
     * business_name se reutiliza tal cual de la sección de Aliado
     * (mismo concepto: nombre del negocio).
     */
    public string $document_id = '';

    public ?int $pickup_ally_id = null;

    /**
     * Agencias aliadas activas, para que el emprendedor elija dónde
     * entregará su mercancía (no hay recolección a domicilio).
     */
    public array $allies = [];

    /**
     * Carga los estados disponibles.
     */
    public function mount(
        VenezuelaLocationService $locationService
    ): void {
        $this->states = $locationService->states();

        $this->allies = Ally::query()
            ->where('status', Ally::STATUS_ACTIVE)
            ->orderBy('business_name')
            ->get(['id', 'business_name', 'city', 'state'])
            ->toArray();

        $requestedRole = request()->query('role');

        if (in_array($requestedRole, ['cliente', 'repartidor', 'aliado', 'emprendedor'], true)) {
            $this->role = $requestedRole;
        }

        $googlePending = session('google_pending');

        if (is_array($googlePending)) {
            $this->viaGoogle = true;
            $this->googleId = $googlePending['google_id'];
            $this->name = $googlePending['name'];
            $this->email = $googlePending['email'];
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
     * Maneja el registro.
     */
    public function register(): void
    {
        // El cliente puede tener modificado $email/$name antes de
        // enviar el formulario (son props públicas editables en el
        // registro manual). Cuando viene de Google, la identidad
        // confirmada es la que quedó en sesión al volver del OAuth
        // callback (ver GoogleAuthController), así que se restaura
        // aquí para que no se pueda crear la cuenta con un correo
        // distinto al que Google realmente verificó.
        if ($this->viaGoogle) {
            $googlePending = session('google_pending');

            if (! is_array($googlePending) || ($googlePending['google_id'] ?? null) !== $this->googleId) {
                abort(403);
            }

            $this->email = $googlePending['email'];
            $this->name = $googlePending['name'];
        }

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
                'in:cliente,repartidor,aliado,emprendedor',
            ],
        ];

        // Una cuenta que llega por "Continuar con Google" no crea
        // contraseña propia (ver GoogleAuthController y mount()): la
        // que se guarda es una aleatoria que nadie usa para entrar.
        if (! $this->viaGoogle) {
            $rules['password'] = [
                'required',
                'string',
                'confirmed',
                Rules\Password::defaults(),
            ];
        }

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
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDACIÓN DE EMPRENDEDOR
        |--------------------------------------------------------------------------
        */

        if ($this->role === User::ROLE_EMPRENDEDOR) {
            $rules = array_merge($rules, [
                'business_name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'document_id' => [
                    'required',
                    'string',
                    'max:20',
                ],

                'pickup_ally_id' => [
                    'required',
                    'integer',
                    Rule::exists('allies', 'id')->where('status', Ally::STATUS_ACTIVE),
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
            'password' => Hash::make($this->viaGoogle ? Str::random(40) : $validated['password']),
            'google_id' => $this->viaGoogle ? $this->googleId : null,
            'email_verified_at' => $this->viaGoogle ? now() : null,
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
                    Ally::create([
                        'user_id' => $user->id,
                        'business_name' => $validated['business_name'],
                        'rif' => $validated['rif'],
                        'state' => $validated['state'],
                        'city' => $validated['city'],
                        'address' => $validated['address'],
                        'commission_percentage' => 10.00,

                        // Un aliado nuevo comienza como PENDIENTE. Los
                        // documentos de verificación y la ubicación en
                        // el mapa se completan después, desde "Mi
                        // Verificación" y desde el panel de Admin
                        // respectivamente — no aquí.
                        'status' => Ally::STATUS_PENDING,
                    ]);
                });
            } catch (\Throwable $e) {
                $user->delete();

                throw $e;
            }

            $user->notify(new AccountPendingApproval('Aliado'));

            Auth::login($user);

            session()->forget('google_pending');

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

                        // Un repartidor nuevo comienza como PENDIENTE, igual
                        // que un aliado, hasta que un admin lo apruebe. Los
                        // documentos de verificación se completan después,
                        // desde "Mi Verificación" — no aquí.
                        'status' => Driver::STATUS_PENDING,
                    ]);
                });
            } catch (\Throwable $e) {
                $user->delete();

                throw $e;
            }

            $user->notify(new AccountPendingApproval('Repartidor'));

            Auth::login($user);

            session()->forget('google_pending');

            $this->redirect(
                route('repartidor.dashboard', absolute: false),
                navigate: true
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | CREAR EMPRENDEDOR
        |--------------------------------------------------------------------------
        */

        if ($user->isEmprendedor()) {
            // Ver comentario equivalente en la rama de Aliado: sin
            // esta transacción, un fallo a mitad de camino dejaría un
            // User con rol "emprendedor" sin su Emprendedor correspondiente.
            try {
                DB::transaction(function () use ($user, $validated) {
                    Emprendedor::create([
                        'user_id' => $user->id,
                        'pickup_ally_id' => $validated['pickup_ally_id'],
                        'business_name' => $validated['business_name'],
                        'document_id' => $validated['document_id'],

                        // Un emprendedor nuevo comienza como PENDIENTE,
                        // igual que un aliado o repartidor, hasta que
                        // un admin lo apruebe.
                        'status' => Emprendedor::STATUS_PENDING,
                    ]);
                });
            } catch (\Throwable $e) {
                $user->delete();

                throw $e;
            }

            $user->notify(new AccountPendingApproval('Emprendedor'));

            Auth::login($user);

            session()->forget('google_pending');

            $this->redirect(
                route('emprendedor.dashboard', absolute: false),
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
        $customer = Customer::firstOrNew(['id_doc' => $validated['id_doc']]);

        $customer->user_id = $user->id;

        // Si un aliado ya había registrado a esta persona, se conservan
        // los datos de contacto que tecleó en taquilla y solo se
        // completan los que faltaban: registrarse con una cédula no
        // debe poder reescribir el nombre/teléfono/correo que ya
        // estaban asociados a ella.
        $customer->name = $customer->name ?: $validated['name'];
        $customer->phone = $customer->phone ?: $validated['phone'];
        $customer->email = $customer->email ?: $validated['email'];

        $customer->save();

        // Un correo de Google ya viene verificado por Google (y
        // email_verified_at ya quedó marcado al crear el User arriba),
        // así que el código de 6 dígitos por correo sería redundante.
        if ($this->viaGoogle) {
            session()->forget('google_pending');

            // Sin esto account_verified_at quedaba null y
            // EnsureAccountIsVerified sacaba al cliente de su panel
            // hacia /verify-account, sin ningún código enviado.
            $user->markAccountAsVerified();

            Auth::login($user);

            $this->redirect(
                route('cliente.dashboard', absolute: false),
                navigate: true
            );

            return;
        }

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

    @if ($viaGoogle)

        {{-- Nombre/correo ya confirmados por Google: solo falta el
             resto de datos que Google no entrega. --}}
        <div class="mt-6 flex items-center gap-3 rounded-xl border border-[#E5E5E0] bg-[#F7F7F4] px-4 py-3">
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg"><path fill="#4285F4" d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844a4.14 4.14 0 01-1.796 2.716v2.259h2.908c1.702-1.567 2.684-3.874 2.684-6.615z"/><path fill="#34A853" d="M9 18c2.43 0 4.467-.806 5.956-2.184l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 009 18z"/><path fill="#FBBC05" d="M3.964 10.706A5.41 5.41 0 013.682 9c0-.593.102-1.17.282-1.706V4.962H.957A8.996 8.996 0 000 9c0 1.452.348 2.827.957 4.038l3.007-2.332z"/><path fill="#EA4335" d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.581C13.463.891 11.426 0 9 0A8.997 8.997 0 00.957 4.962L3.964 7.294C4.672 5.167 6.656 3.58 9 3.58z"/></svg>
            <div class="text-sm">
                <p class="font-semibold text-[#111111]">{{ $name }}</p>
                <p class="text-gray-500">{{ $email }}</p>
            </div>
        </div>

    @elseif (config('services.google.client_id'))

        <a
            href="{{ route('auth.google.redirect') }}"
            class="mt-6 flex items-center justify-center gap-2 rounded-lg border border-[#E5E5E0] bg-white px-4 py-2.5 text-sm font-semibold text-[#111111] transition hover:bg-[#F7F7F4]"
        >
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg"><path fill="#4285F4" d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844a4.14 4.14 0 01-1.796 2.716v2.259h2.908c1.702-1.567 2.684-3.874 2.684-6.615z"/><path fill="#34A853" d="M9 18c2.43 0 4.467-.806 5.956-2.184l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 009 18z"/><path fill="#FBBC05" d="M3.964 10.706A5.41 5.41 0 013.682 9c0-.593.102-1.17.282-1.706V4.962H.957A8.996 8.996 0 000 9c0 1.452.348 2.827.957 4.038l3.007-2.332z"/><path fill="#EA4335" d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.581C13.463.891 11.426 0 9 0A8.997 8.997 0 00.957 4.962L3.964 7.294C4.672 5.167 6.656 3.58 9 3.58z"/></svg>
            Continuar con Google
        </a>

        <div class="mt-6 flex items-center gap-3 text-xs text-gray-400">
            <span class="h-px flex-1 bg-[#E5E5E0]"></span>
            o regístrate con tu correo
            <span class="h-px flex-1 bg-[#E5E5E0]"></span>
        </div>

    @endif

    <form wire:submit="register" class="mt-6 space-y-5">

        @unless ($viaGoogle)

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

        @endunless

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

                <option value="emprendedor">
                    Emprendedor
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

        @endif

        @if ($role === 'emprendedor')

            <div class="border-t border-gray-200 pt-5">
                <h2 class="text-sm font-semibold text-blue-950">
                    Información del negocio
                </h2>

                <p class="mt-1 text-xs text-gray-500">
                    Tú entregas la mercancía en la agencia aliada que elijas — Venexpress no recolecta a domicilio.
                </p>
            </div>

            {{-- NOMBRE DEL NEGOCIO --}}
            <div>
                <x-input-label
                    for="business_name"
                    value="Nombre del negocio"
                />

                <x-text-input
                    wire:model="business_name"
                    id="business_name"
                    class="block mt-1.5 w-full"
                    type="text"
                    placeholder="Mi Tienda"
                />

                <x-input-error
                    :messages="$errors->get('business_name')"
                    class="mt-2"
                />
            </div>

            {{-- CÉDULA O RIF --}}
            <div>
                <x-input-label
                    for="document_id"
                    value="Cédula o RIF"
                />

                <x-text-input
                    wire:model="document_id"
                    id="document_id"
                    class="block mt-1.5 w-full"
                    type="text"
                    placeholder="V-12345678 o J-12345678-9"
                />

                <x-input-error
                    :messages="$errors->get('document_id')"
                    class="mt-2"
                />
            </div>

            {{-- AGENCIA DE RETIRO --}}
            <div>
                <x-input-label
                    for="pickup_ally_id"
                    value="Agencia aliada donde entregarás tu mercancía"
                />

                <select
                    wire:model="pickup_ally_id"
                    id="pickup_ally_id"
                    class="block mt-1.5 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
                    <option value="">Selecciona una agencia</option>

                    @foreach ($allies as $ally)
                        <option value="{{ $ally['id'] }}">
                            {{ $ally['business_name'] }} — {{ $ally['city'] }}, {{ $ally['state'] }}
                        </option>
                    @endforeach
                </select>

                @if (count($allies) === 0)
                    <p class="mt-1 text-xs text-amber-600">
                        Todavía no hay agencias aliadas activas disponibles.
                    </p>
                @endif

                <x-input-error
                    :messages="$errors->get('pickup_ally_id')"
                    class="mt-2"
                />
            </div>

        @endif

        @unless ($viaGoogle)

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

        @endunless

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
