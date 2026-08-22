<?php

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
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'role' => ['required', 'in:cliente,chofer,aliado'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered($user = User::create($validated)));

        Auth::login($user);

        if ($user->isCliente()) {
    $this->redirect(route('cliente.dashboard', absolute: false), navigate: true);
    return;
}

if ($user->isChofer()) {
    $this->redirect(route('repartidor.dashboard', absolute: false), navigate: true);
    return;
}

if ($user->isAliado()) {
    $this->redirect(route('aliado.dashboard', absolute: false), navigate: true);
    return;
}

$this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="mb-8 lg:hidden">
        <x-venexpress-logo size="md" />
    </div>

    <h1 class="font-display text-2xl font-bold text-blue-950">Crea tu cuenta</h1>
    <p class="mt-1.5 text-sm text-gray-500">
        Regístrate para gestionar tus guías, tarifas o entregas en VenExpress.
    </p>

    <form wire:submit="register" class="mt-8 space-y-5">
        <div>
            <x-input-label for="name" value="Nombre completo" />
            <x-text-input wire:model="name" id="name" class="block mt-1.5 w-full" type="text" name="name" required autofocus autocomplete="name" placeholder="Tu nombre" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" value="Correo electrónico" />
            <x-text-input wire:model="email" id="email" class="block mt-1.5 w-full" type="email" name="email" required autocomplete="username" placeholder="tu@correo.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
    <x-input-label for="role" value="Tipo de usuario" />

    <select
        wire:model="role"
        id="role"
        name="role"
        class="block mt-1.5 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
        required
    >
        <option value="cliente">Cliente</option>
        <option value="chofer">Repartidor</option>
        <option value="aliado">Punto aliado</option>
    </select>

    <x-input-error :messages="$errors->get('role')" class="mt-2" />
</div>

        <div>
            <x-input-label for="password" value="Contraseña" />
            <x-password-input wire:model="password" id="password" class="block mt-1.5"
                            name="password"
                            required autocomplete="new-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Confirmar contraseña" />
            <x-password-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1.5"
                            name="password_confirmation" required autocomplete="new-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <x-primary-button class="w-full py-3">
            Crear cuenta
        </x-primary-button>
    </form>

    <p class="mt-8 text-center text-sm text-gray-500">
        ¿Ya tienes una cuenta?
        <a href="{{ route('login') }}" class="font-semibold text-blue-700 hover:text-blue-950" wire:navigate>Inicia sesión</a>
    </p>
</div>
