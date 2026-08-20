<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
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

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="mb-8 lg:hidden">
        <x-venexpress-logo size="md" />
    </div>

    <h1 class="font-display text-2xl font-bold text-navy-900">Iniciar sesión</h1>
    <p class="mt-1.5 text-sm text-slate-500">
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
                    <a class="text-xs font-medium text-navy-500 hover:text-navy-700" href="{{ route('password.request') }}" wire:navigate>
                        ¿Olvidaste tu contraseña?
                    </a>
                @endif
            </div>

            <x-text-input wire:model="form.password" id="password" class="block mt-1.5 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" placeholder="••••••••" />

            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <label for="remember" class="flex items-center gap-2 text-sm text-slate-600">
            <input wire:model="form.remember" id="remember" type="checkbox" class="rounded border-navy-200 text-navy-600 shadow-sm focus:ring-navy-400" name="remember">
            Recordarme en este dispositivo
        </label>

        <x-primary-button class="w-full py-3">
            Iniciar sesión
        </x-primary-button>
    </form>

    @if (Route::has('register'))
        <p class="mt-8 text-center text-sm text-slate-500">
            ¿Todavía no tienes cuenta?
            <a href="{{ route('register') }}" class="font-semibold text-navy-700 hover:text-navy-900" wire:navigate>Regístrate</a>
        </p>
    @endif
</div>
