<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $password = '';

    /**
     * Confirm the current user's password.
     */
    public function confirmPassword(): void
    {
        $this->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('web')->validate([
            'email' => Auth::user()->email,
            'password' => $this->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        session(['auth.password_confirmed_at' => time()]);

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="mb-8 lg:hidden">
        <x-venexpress-logo size="md" />
    </div>

    <h1 class="font-display text-2xl font-bold text-blue-950">Confirma tu contraseña</h1>
    <p class="mt-1.5 text-sm text-gray-500">
        Esta es un área protegida. Confirma tu contraseña antes de continuar.
    </p>

    <form wire:submit="confirmPassword" class="mt-8 space-y-5">
        <div>
            <x-input-label for="password" value="Contraseña" />
            <x-password-input wire:model="password"
                          id="password"
                          class="block mt-1.5"
                          name="password"
                          required autocomplete="current-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <x-primary-button class="w-full py-3">
            Confirmar
        </x-primary-button>
    </form>
</div>
