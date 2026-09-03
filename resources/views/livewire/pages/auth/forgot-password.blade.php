<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $status = Password::sendResetLink(
            $this->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
    }
}; ?>

<div>
    <div class="mb-8 lg:hidden">
        <x-venexpress-logo size="md" />
    </div>

    <h1 class="font-display text-2xl font-bold text-blue-950">Recuperar contraseña</h1>
    <p class="mt-1.5 text-sm text-gray-500">
        Ingresa tu correo y te enviaremos un enlace para elegir una nueva contraseña.
    </p>

    <x-auth-session-status class="mt-6" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink" class="mt-8 space-y-5">
        <div>
            <x-input-label for="email" value="Correo electrónico" />
            <x-text-input wire:model="email" id="email" class="block mt-1.5 w-full" type="email" name="email" required autofocus placeholder="tu@correo.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <x-primary-button class="w-full py-3">
            Enviar enlace de recuperación
        </x-primary-button>
    </form>

    <p class="mt-8 text-center text-sm text-gray-500">
        ¿Recordaste tu contraseña?
        <a href="{{ route('login') }}" class="font-semibold text-blue-700 hover:text-blue-950" wire:navigate>Inicia sesión</a>
    </p>
</div>
