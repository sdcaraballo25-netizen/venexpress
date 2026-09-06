<?php

use App\Models\Ally;
use App\Models\Driver;
use App\Models\User;
use App\Notifications\WelcomeVerificationToken;
use App\Services\VenezuelaLocationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
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

    public array $states = [];

    public array $cities = [];

    /**
     * Datos adicionales para repartidores.
     */
    public string $vehicle_plate = '';

    public string $vehicle_type = '';

    public string $phone = '';

    /**
     * Carga los estados disponibles.
     */
    public function mount(
        VenezuelaLocationService $locationService
    ): void {
        $this->states = $locationService->states();
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
        | VALIDACIÃ“N DE ALIADO
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
        | VALIDACIÃ“N DE CHOFER
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

        $validated = $this->validate($rules);

        /*
        |--------------------------------------------------------------------------
        | CREAR USUARIO
        |--------------------------------------------------------------------------
        */

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
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
            Ally::create([
                'user_id' => $user->id,
                'business_name' => $validated['business_name'],
                'rif' => $validated['rif'],
                'state' => $validated['state'],
                'city' => $validated['city'],
                'address' => $validated['address'],
                'commission_percentage' => 10.00,

                // Un aliado nuevo comienza como PENDIENTE.
                'status' => Ally::STATUS_PENDING,
            ]);

            Auth::login($user);

            $this->redirect(
                route('ally.dashboard', absolute: false),
                navigate: true
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | CREAR CHOFER
        |--------------------------------------------------------------------------
        */

        if ($user->isChofer()) {
            Driver::create([
                'user_id' => $user->id,
                'vehicle_plate' => $validated['vehicle_plate'],
                'vehicle_type' => $validated['vehicle_type'],
                'phone' => $validated['phone'],
                'status' => Driver::STATUS_ACTIVE,
                'driver_type' => Driver::TYPE_DELIVERY,
            ]);

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
}; ?>

<div>

    <div class="mb-8 lg:hidden">
        <x-venexpress-logo size="md" />
    </div>

    <h1 class="font-display text-2xl font-bold text-blue-950">
        Crea tu cuenta
    </h1>

    <p class="mt-1.5 text-sm text-gray-500">
        RegÃ­strate para gestionar tus guÃ­as, tarifas o entregas en VenExpress.
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
                value="Correo electrÃ³nico"
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
        {{-- DATOS DEL ALIADO --}}
        {{-- ====================================================== --}}

        @if ($role === 'aliado')

            <div class="border-t border-gray-200 pt-5">

                <h2 class="text-sm font-semibold text-blue-950">
                    InformaciÃ³n del punto aliado
                </h2>

                <p class="mt-1 text-xs text-gray-500">
                    Estos datos serÃ¡n revisados por VenExpress antes de activar el comercio.
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

            {{-- DIRECCIÃ“N --}}
            <div>
                <x-input-label
                    for="address"
                    value="DirecciÃ³n"
                />

                <x-text-input
                    wire:model="address"
                    id="address"
                    class="block mt-1.5 w-full"
                    type="text"
                    placeholder="DirecciÃ³n del establecimiento"
                />

                <x-input-error
                    :messages="$errors->get('address')"
                    class="mt-2"
                />
            </div>

        @endif

        {{-- ====================================================== --}}
        {{-- DATOS DEL CHOFER --}}
        {{-- ====================================================== --}}

        @if ($role === 'repartidor')

            <div class="border-t border-gray-200 pt-5">

                <h2 class="text-sm font-semibold text-blue-950">
                    InformaciÃ³n del repartidor
                </h2>

            </div>

            {{-- PLACA --}}
            <div>
                <x-input-label
                    for="vehicle_plate"
                    value="Placa del vehÃ­culo"
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

            {{-- VEHÃCULO --}}
            <div>
                <x-input-label
                    for="vehicle_type"
                    value="Tipo de vehÃ­culo"
                />

                <x-text-input
                    wire:model="vehicle_type"
                    id="vehicle_type"
                    class="block mt-1.5 w-full"
                    type="text"
                    placeholder="Moto, automÃ³vil, camioneta..."
                />

                <x-input-error
                    :messages="$errors->get('vehicle_type')"
                    class="mt-2"
                />
            </div>

            {{-- TELÃ‰FONO --}}
            <div>
                <x-input-label
                    for="phone"
                    value="TelÃ©fono"
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

        {{-- ====================================================== --}}
        {{-- CONTRASEÃ‘A --}}
        {{-- ====================================================== --}}

        <div>
            <x-input-label
                for="password"
                value="ContraseÃ±a"
            />

            <x-password-input
                wire:model="password"
                id="password"
                class="block mt-1.5"
                name="password"
                required
                autocomplete="new-password"
                placeholder="â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢"
            />

            <x-input-error
                :messages="$errors->get('password')"
                class="mt-2"
            />
        </div>

        {{-- CONFIRMAR CONTRASEÃ‘A --}}
        <div>
            <x-input-label
                for="password_confirmation"
                value="Confirmar contraseÃ±a"
            />

            <x-password-input
                wire:model="password_confirmation"
                id="password_confirmation"
                class="block mt-1.5"
                name="password_confirmation"
                required
                autocomplete="new-password"
                placeholder="â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢"
            />

            <x-input-error
                :messages="$errors->get('password_confirmation')"
                class="mt-2"
            />
        </div>

        {{-- BOTÃ“N --}}
        <x-primary-button class="w-full py-3">
            Crear cuenta
        </x-primary-button>

    </form>

    <p class="mt-8 text-center text-sm text-gray-500">
        Â¿Ya tienes una cuenta?

        <a
            href="{{ route('login') }}"
            class="font-semibold text-blue-700 hover:text-blue-950"
            wire:navigate
        >
            Inicia sesiÃ³n
        </a>
    </p>

</div>
