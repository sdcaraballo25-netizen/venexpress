<?php

use App\Models\Ally;
use App\Models\Driver;
use App\Models\User;
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
    public string $city = '';
    public string $address = '';

    /**
     * Datos adicionales para repartidores.
     */
    public string $vehicle_plate = '';
    public string $vehicle_type = '';
    public string $phone = '';

    /**
     * Maneja el registro.
     */
    public function register(): void
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],

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

                'city' => [
                    'required',
                    'string',
                    'max:255',
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
        | VALIDACIÓN DE CHOFER
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
                'city' => $validated['city'],
                'address' => $validated['address'],
                'commission_percentage' => 10.00,

                // IMPORTANTE:
                // Un aliado nuevo comienza como PENDIENTE.
                'status' => Ally::STATUS_PENDING,
            ]);

            Auth::login($user);

            $this->redirect(
                route('aliado.dashboard', absolute: false),
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

        Auth::login($user);

        $this->redirect(
            route('cliente.dashboard', absolute: false),
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

            {{-- CIUDAD --}}
            <div>
                <x-input-label
                    for="city"
                    value="Ciudad"
                />

                <x-text-input
                    wire:model="city"
                    id="city"
                    class="block mt-1.5 w-full"
                    type="text"
                    placeholder="Caracas"
                />

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
        {{-- DATOS DEL CHOFER --}}
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