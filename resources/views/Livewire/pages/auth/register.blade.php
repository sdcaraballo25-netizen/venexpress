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

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered($user = User::create($validated)));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="mb-8 lg:hidden">
        <x-venexpress-logo size="md" />
    </div>

    <h1 class="font-display text-2xl font-bold text-navy-900">Crea tu cuenta</h1>
    <p class="mt-1.5 text-sm text-slate-500">
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
            <x-input-label for="password" value="Contraseña" />
            <x-text-input wire:model="password" id="password" class="block mt-1.5 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Confirmar contraseña" />
            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1.5 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <x-primary-button class="w-full py-3">
            Crear cuenta
        </x-primary-button>
    </form>

    <p class="mt-8 text-center text-sm text-slate-500">
        ¿Ya tienes una cuenta?
        <a href="{{ route('login') }}" class="font-semibold text-navy-700 hover:text-navy-900" wire:navigate>Inicia sesión</a>
    </p>
</div>
