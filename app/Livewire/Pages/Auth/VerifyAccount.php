<?php

namespace App\Livewire\Pages\Auth;

use App\Models\User;
use App\Notifications\WelcomeVerificationToken;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Component;

class VerifyAccount extends Component
{
    /**
     * Máximo de intentos de código incorrecto permitidos antes de
     * bloquear temporalmente (mismo criterio que LoginForm).
     */
    protected const MAX_ATTEMPTS = 5;

    public string $code = '';

    #[Locked]
    public ?int $userId = null;

    public bool $canResend = true;

    public int $resendCooldown = 0;

    public function mount(): void
    {
        $this->userId = session('pending_verification_user_id');

        if (! $this->userId || ! $this->pendingUser()) {
            $this->redirect(route('register', absolute: false), navigate: true);

            return;
        }

        $this->refreshResendState();
    }

    protected function pendingUser(): ?User
    {
        if (! $this->userId) {
            return null;
        }

        return User::find($this->userId);
    }

    public function refreshResendState(): void
    {
        $user = $this->pendingUser();

        $this->canResend = $user?->canResendVerificationToken() ?? true;
        $this->resendCooldown = $user?->secondsUntilCanResendVerificationToken() ?? 0;
    }

    public function verify(): void
    {
        $this->validate([
            'code' => ['required', 'string', 'size:6'],
        ], [
            'code.size' => 'El código debe tener 6 dígitos.',
        ]);

        $user = $this->pendingUser();

        if (! $user) {
            $this->redirect(route('register', absolute: false), navigate: true);

            return;
        }

        if (RateLimiter::tooManyAttempts($this->throttleKey(), self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($this->throttleKey());

            $this->addError(
                'code',
                "Demasiados intentos. Espera {$seconds} segundos antes de volver a intentarlo."
            );

            return;
        }

        if (! $user->verificationTokenIsValid($this->code)) {
            RateLimiter::hit($this->throttleKey());

            $this->addError(
                'code',
                'El código es incorrecto o ya venció. Solicita uno nuevo.'
            );

            return;
        }

        RateLimiter::clear($this->throttleKey());

        $user->markAccountAsVerified();

        session()->forget('pending_verification_user_id');

        Auth::login($user);

        session()->regenerate();

        $this->redirect(
            route('cliente.dashboard', absolute: false),
            navigate: true
        );
    }

    public function resend(): void
    {
        $user = $this->pendingUser();

        if (! $user) {
            $this->redirect(route('register', absolute: false), navigate: true);

            return;
        }

        if (! $user->canResendVerificationToken()) {
            $this->refreshResendState();

            return;
        }

        $plainToken = $user->generateVerificationToken();

        $user->notify(new WelcomeVerificationToken($plainToken));

        RateLimiter::clear($this->throttleKey());

        $this->code = '';

        $this->refreshResendState();

        session()->flash(
            'resend_success',
            'Te enviamos un nuevo código a tu correo.'
        );
    }

    /**
     * Clave de rate limiting para los intentos de verificación,
     * combinando el usuario pendiente con la IP (igual criterio que
     * LoginForm::throttleKey()).
     */
    protected function throttleKey(): string
    {
        return Str::transliterate('verify-account|'.$this->userId.'|'.request()->ip());
    }

    public function render()
    {
        return view('pages.auth.verify-account')
            ->layout('layouts.guest');
    }
}
