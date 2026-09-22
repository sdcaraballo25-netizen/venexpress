<?php

use App\Notifications\PasswordChangeCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Máximo de intentos de código incorrecto permitidos antes de
     * bloquear temporalmente (mismo criterio que VerifyAccount).
     */
    protected const MAX_ATTEMPTS = 5;

    public string $step = 'request';

    public string $code = '';
    public string $password = '';
    public string $password_confirmation = '';

    public bool $canResend = true;
    public int $resendCooldown = 0;

    public function mount(): void
    {
        $this->refreshResendState();
    }

    protected function refreshResendState(): void
    {
        $user = Auth::user();

        $this->canResend = $user->canResendPasswordChangeCode();
        $this->resendCooldown = $user->secondsUntilCanResendPasswordChangeCode();
    }

    /**
     * Envía (o reenvía) el código de 6 dígitos al correo del usuario
     * autenticado. No pide la contraseña actual: quien ya inició
     * sesión solo necesita demostrar que también tiene acceso a su
     * correo antes de poder cambiarla.
     */
    public function sendCode(): void
    {
        $user = Auth::user();

        if (! $user->canResendPasswordChangeCode()) {
            $this->refreshResendState();

            return;
        }

        $plainCode = $user->generatePasswordChangeCode();

        $user->notify(new PasswordChangeCode($plainCode));

        RateLimiter::clear($this->throttleKey());

        $this->step = 'verify';
        $this->code = '';

        $this->refreshResendState();

        session()->flash('password_code_sent', 'Te enviamos un código a tu correo.');
    }

    public function cancel(): void
    {
        $this->reset('code', 'password', 'password_confirmation');
        $this->step = 'request';
    }

    public function updatePassword(): void
    {
        $user = Auth::user();

        if (RateLimiter::tooManyAttempts($this->throttleKey(), self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($this->throttleKey());

            $this->addError(
                'code',
                "Demasiados intentos. Espera {$seconds} segundos antes de volver a intentarlo."
            );

            return;
        }

        $this->validate([
            'code' => ['required', 'string', 'size:6'],
            'password' => ['required', 'string', Password::defaults(), 'confirmed'],
        ], [
            'code.size' => 'El código debe tener 6 dígitos.',
        ]);

        if (! $user->passwordChangeCodeIsValid($this->code)) {
            RateLimiter::hit($this->throttleKey());

            $this->addError(
                'code',
                'El código es incorrecto o ya venció. Solicita uno nuevo.'
            );

            return;
        }

        RateLimiter::clear($this->throttleKey());

        $user->forceFill([
            'password' => Hash::make($this->password),
        ])->save();

        $user->clearPasswordChangeCode();

        $this->reset('code', 'password', 'password_confirmation');
        $this->step = 'request';

        $this->dispatch('password-updated');
    }

    protected function throttleKey(): string
    {
        return Str::transliterate('password-change-code|' . Auth::id());
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Update Password') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Por seguridad, para cambiar tu contraseña te enviamos un código a tu correo registrado.
        </p>
    </header>

    @if (session('password_code_sent'))
        <p class="mt-4 text-sm font-medium text-green-600">
            {{ session('password_code_sent') }}
        </p>
    @endif

    @if ($step === 'request')
        <div class="mt-6">
            <x-primary-button wire:click="sendCode">
                Enviar código para cambiar contraseña
            </x-primary-button>
        </div>
    @else
        <form wire:submit="updatePassword" class="mt-6 space-y-6">
            <div>
                <x-input-label for="password_change_code" value="Código de verificación" />
                <x-text-input wire:model="code" id="password_change_code" name="code" type="text" inputmode="numeric" maxlength="6" class="mt-1 block w-full" autocomplete="one-time-code" autofocus />
                <x-input-error :messages="$errors->get('code')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="update_password_password" :value="__('New Password')" />
                <x-text-input wire:model="password" id="update_password_password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" />
                <x-text-input wire:model="password_confirmation" id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <div class="flex items-center gap-4">
                <x-primary-button>{{ __('Save') }}</x-primary-button>

                <button
                    type="button"
                    wire:click="sendCode"
                    @if (! $canResend) disabled @endif
                    class="text-sm text-gray-600 hover:text-gray-900 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    @if ($canResend)
                        Reenviar código
                    @else
                        Reenviar en {{ $resendCooldown }}s
                    @endif
                </button>

                <button type="button" wire:click="cancel" class="text-sm text-gray-500 hover:text-gray-900">
                    Cancelar
                </button>

                <x-action-message class="me-3" on="password-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>
    @endif
</section>
