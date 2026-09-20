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

// Taquilla no tiene acceso al Dashboard general del negocio (ve solo
// lo que ella misma registra) — la mandamos directo a registrar
// pedidos, su tarea principal.
if ($user->isAliadoTaquilla()) {
    $this->redirect(route('ally.packages.create', absolute: false), navigate: true);
    return;
}

if ($user->isAlmacen()) {
    $this->redirect(route('almacen.dashboard', absolute: false), navigate: true);
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

    @if (config('services.google.client_id'))

        <a
            href="{{ route('auth.google.redirect') }}"
            class="mt-6 flex items-center justify-center gap-2 rounded-lg border border-[#E5E5E0] bg-white px-4 py-2.5 text-sm font-semibold text-[#111111] transition hover:bg-[#F7F7F4]"
        >
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg"><path fill="#4285F4" d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844a4.14 4.14 0 01-1.796 2.716v2.259h2.908c1.702-1.567 2.684-3.874 2.684-6.615z"/><path fill="#34A853" d="M9 18c2.43 0 4.467-.806 5.956-2.184l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 009 18z"/><path fill="#FBBC05" d="M3.964 10.706A5.41 5.41 0 013.682 9c0-.593.102-1.17.282-1.706V4.962H.957A8.996 8.996 0 000 9c0 1.452.348 2.827.957 4.038l3.007-2.332z"/><path fill="#EA4335" d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.581C13.463.891 11.426 0 9 0A8.997 8.997 0 00.957 4.962L3.964 7.294C4.672 5.167 6.656 3.58 9 3.58z"/></svg>
            Continuar con Google
        </a>

        <div class="mt-6 flex items-center gap-3 text-xs text-gray-400">
            <span class="h-px flex-1 bg-[#E5E5E0]"></span>
            o inicia sesión con tu correo
            <span class="h-px flex-1 bg-[#E5E5E0]"></span>
        </div>

    @endif

    <form wire:submit="login" class="mt-6 space-y-5">
        <div>
            <x-input-label for="email" value="Correo o usuario" />
            <x-text-input wire:model="form.email" id="email" class="block mt-1.5 w-full" type="text" name="email" required autofocus autocomplete="username" placeholder="tu@correo.com o tu usuario" />
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
