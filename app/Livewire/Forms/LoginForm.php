<?php

namespace App\Livewire\Forms;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class LoginForm extends Form
{
    protected const LOCKOUT_EMAIL_COOLDOWN_SECONDS = 3600;

    /**
     * Acepta un correo o un "usuario" simple (ej. "taquilla1") — las
     * cuentas de Taquilla no tienen un correo real que el Aliado
     * Administrador tenga que inventarse (ver AllyStaffService).
     */
    #[Validate('required|string')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    #[Validate('boolean')]
    public bool $remember = false;

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // str_contains('@') es suficiente para distinguir: un
        // username válido (ver validación en AllyStaffService/
        // StaffManager) nunca lleva "@".
        $field = str_contains($this->email, '@') ? 'email' : 'username';

        $credentials = [$field => $this->email, 'password' => $this->password];

        if (! Auth::attempt($credentials, $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'form.email' => trans('auth.failed'),
            ]);
        }

        if (method_exists(Auth::user(), 'isActive') && ! Auth::user()->isActive()) {
            Auth::logout();

            throw ValidationException::withMessages([
                'form.email' => 'Esta cuenta está inactiva. Contacta a un administrador.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $this->sendRecoveryEmailOnLockout();

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'form.email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]).' Te enviamos un correo para que puedas restablecer tu contraseña.',
        ]);
    }

    /**
     * Al agotar los 5 intentos, se envía automáticamente un correo de
     * recuperación de contraseña a la cuenta (mismo enlace único y
     * seguro que genera Password::sendResetLink() en "Olvidé mi
     * contraseña" — un token aleatorio de un solo uso, hasheado en
     * la base de datos, que expira).
     *
     * Cache::add() garantiza como mucho un envío por cuenta por hora
     * (LOCKOUT_EMAIL_COOLDOWN_SECONDS), aunque se siga reintentando
     * desde una o varias IPs.
     */
    protected function sendRecoveryEmailOnLockout(): void
    {
        $field = str_contains($this->email, '@') ? 'email' : 'username';

        $user = User::where($field, $this->email)->first();

        if (! $user?->email) {
            return;
        }

        // Como mucho un correo por CUENTA por hora. Antes la clave
        // incluía la IP (throttleKey()), así que alguien rotando IPs
        // podía forzar fallos de login y llenar de correos de
        // recuperación el buzón de cualquier usuario.
        if (! Cache::add('login-lockout-email:user:'.$user->id, true, self::LOCKOUT_EMAIL_COOLDOWN_SECONDS)) {
            return;
        }

        Password::sendResetLink(['email' => $user->email]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }
}
