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
     * Handle an incoming authentication request for the admin panel.
     *
     * Esta página no se enlaza desde ningún lugar de la UI pública:
     * solo se accede escribiendo /admin/login directamente.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        // Solo cuentas con role = admin pueden entrar por esta puerta.
        if (! Auth::user()->isAdmin()) {
            Auth::logout();

            throw ValidationException::withMessages([
                'form.email' => 'Estas credenciales no corresponden a una cuenta de administrador.',
            ]);
        }

        Session::regenerate();

        $this->redirectIntended(default: route('admin.dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="mb-8 lg:hidden">
        <x-venexpress-logo size="md" />
    </div>

    <h1 class="font-display text-2xl font-bold text-blue-950">Acceso administrador</h1>
    <p class="mt-1.5 text-sm text-gray-500">
        Panel exclusivo para el equipo VenExpress.
    </p>

    <x-auth-session-status class="mt-6" :status="session('status')" />

    <form wire:submit="login" class="mt-8 space-y-5">
        <div>
            <x-input-label for="email" value="Correo electrónico" />
            <x-text-input wire:model="form.email" id="email" class="block mt-1.5 w-full" type="email" name="email" required autofocus autocomplete="username" placeholder="admin@venexpress.com" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" value="Contraseña" />
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
            Entrar al panel
        </x-primary-button>
    </form>
</div>
