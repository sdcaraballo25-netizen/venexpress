<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        $user = Auth::user();

        // Los administradores no inician sesión por aquí: deben usar
        // el acceso privado (/admin/login).
        if ($user->isAdmin()) {
            Auth::logout();

            throw ValidationException::withMessages([
                'form.email' => 'Los administradores deben ingresar por el acceso correspondiente.',
            ]);
        }

        Session::regenerate();

        if ($user->isCliente()) {
    $this->redirect(route('cliente.dashboard', absolute: false), navigate: true);
    return;
}

if ($user->isChofer()) {
    $this->redirect(route('repartidor.dashboard', absolute: false), navigate: true);
    return;
}

if ($user->isAliado()) {
    $this->redirect(route('ally.dashboard', absolute: false), navigate: true);
    return;
}

$this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="mb-8 lg:hidden">
        <x-venexpress-logo size="md" />
    </div>

    <h1 class="font-display text-2xl font-bold text-blue-950">Iniciar sesión</h1>
    <p class="mt-1.5 text-sm text-gray-500">
        Ingresa tus credenciales para acceder a tu panel.
    </p>

    <x-auth-session-status class="mt-6" :status="session('status')" />

    <form wire:submit="login" class="mt-8 space-y-5">
        <div>
            <x-input-label for="email" value="Correo electrónico" />
            <x-text-input wire:model="form.email" id="email" class="block mt-1.5 w-full" type="email" name="email" required autofocus autocomplete="username" placeholder="tu@correo.com" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <div>
            <div class="flex items-center justify-between">
                <x-input-label for="password" value="Contraseña" />
                @if (Route::has('password.request'))
                    <a class="text-xs font-medium text-blue-700 hover:text-blue-950" href="{{ route('password.request') }}" wire:navigate>
                        ¿Olvidaste tu contraseña?
                    </a>
                @endif
            </div>

            <x-password-input wire:model="form.password" id="password" class="block mt-1.5"
                            name="password"
                            required autocomplete="current-password" placeholder="••••••••" />

            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <label for="remember" class="flex items-center gap-2 text-sm text-gray-600">
            <input wire:model="form.remember" id="remember" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-600" name="remember">
            Recordarme en este dispositivo
        </label>

        <x-primary-button class="w-full py-3">
            Iniciar sesión
        </x-primary-button>
    </form>

    @if (Route::has('register'))
        <p class="mt-8 text-center text-sm text-gray-500">
            ¿Todavía no tienes cuenta?
            <a href="{{ route('register') }}" class="font-semibold text-blue-700 hover:text-blue-950" wire:navigate>Regístrate</a>
        </p>
    @endif
</div>
