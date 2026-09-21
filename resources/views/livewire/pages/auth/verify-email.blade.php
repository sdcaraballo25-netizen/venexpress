<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    /**
     * Send an email verification notification to the user.
     */
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);

            return;
        }

        Auth::user()->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div>
    <div class="mb-8 lg:hidden">
        <x-venexpress-logo size="md" />
    </div>

    <h1 class="font-display text-2xl font-bold text-blue-950">Verifica tu correo</h1>
    <p class="mt-3 text-sm leading-relaxed text-gray-500">
        Gracias por registrarte. Antes de comenzar, confirma tu correo electrónico haciendo clic en el enlace que te acabamos de enviar. Si no lo recibiste, con gusto te enviamos otro.
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="mt-6 flex items-center gap-2 rounded-lg bg-emerald-50 px-3 py-2 font-medium text-sm text-emerald-700">
            <svg viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 shrink-0"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-7.4 7.4a1 1 0 0 1-1.4 0L3.3 9.5a1 1 0 1 1 1.4-1.4l3.9 3.9 6.7-6.7a1 1 0 0 1 1.4 0Z" clip-rule="evenodd"/></svg>
            Hemos enviado un nuevo enlace de verificación al correo que usaste al registrarte.
        </div>
    @endif

    <div class="mt-8 flex items-center justify-between gap-4">
        <x-primary-button wire:click="sendVerification">
            Reenviar correo de verificación
        </x-primary-button>

        <button wire:click="logout" type="submit" class="text-sm font-medium text-gray-500 hover:text-blue-950">
            Cerrar sesión
        </button>
    </div>
</div>
