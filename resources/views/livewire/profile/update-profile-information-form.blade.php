<?php

use App\Models\User;
use App\Notifications\WelcomeVerificationToken;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phone = '';

    /**
     * El Cliente no puede cambiar su correo desde el perfil: varias
     * pantallas de Cliente (Dashboard, Incidents, PendingPayments)
     * siguen usando el correo para encontrar guías de familiares que
     * comparten cuenta, y un cambio accidental o malintencionado
     * podría desconectarlo de ese historial compartido.
     *
     * Tampoco Taquilla (su correo es técnico, lo genera AllyStaffService),
     * Almacén ni Franquiciado: sus cuentas las administra su dueño o un
     * Admin. Aliado, Repartidor y Emprendedor sí, pero el correo nuevo
     * debe confirmarse con el código de verificación (ver
     * updateProfileInformation()).
     */
    public bool $canChangeEmail = true;

    protected function userCanChangeEmail(User $user): bool
    {
        return $user->isAdmin()
            || in_array($user->role, [User::ROLE_ALIADO, User::ROLE_REPARTIDOR, User::ROLE_EMPRENDEDOR], true);
    }

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
        $this->phone = (string) Auth::user()->phone;
        $this->canChangeEmail = $this->userCanChangeEmail(Auth::user());
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();
        $canChangeEmail = $this->userCanChangeEmail($user);

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
        ];

        if ($canChangeEmail) {
            $rules['email'] = ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)];
        }

        $validated = $this->validate($rules);

        if (! $canChangeEmail) {
            // Se ignora cualquier valor que haya llegado en $this->email
            // (el campo está deshabilitado en la vista, pero un request
            // manipulado podría intentar enviarlo de todos modos).
            $this->email = $user->email;
        }

        $user->fill($validated);

        $emailChanged = $user->isDirty('email');

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        // SEGURIDAD: antes el correo nuevo quedaba activo sin probar que
        // era suyo. Ahora, en los roles con verificación por código, se
        // envía un código al correo nuevo y se cierra la sesión hasta
        // confirmarlo (mismo flujo que el registro; EnsureAccountIsVerified
        // y VerifyAccount).
        if ($emailChanged && $user->requiresAccountVerification()) {
            $user->forceFill(['account_verified_at' => null])->save();

            $user->notify(new WelcomeVerificationToken($user->generateVerificationToken()));

            Auth::guard('web')->logout();
            Session::invalidate();
            Session::regenerateToken();
            Session::put('pending_verification_user_id', $user->id);

            $this->redirect(route('verify-account'), navigate: false);

            return;
        }

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form wire:submit="updateProfileInformation" class="mt-6 space-y-6">
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input wire:model="name" id="name" name="name" type="text" class="mt-1 block w-full" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="email" id="email" name="email" type="email" class="mt-1 block w-full" required autocomplete="username" :disabled="! $canChangeEmail" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @unless ($canChangeEmail)
                <p class="text-xs text-gray-500 mt-1">
                    No puedes cambiar tu correo desde aquí. Si necesitas actualizarlo, contacta a soporte.
                </p>
            @endunless

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button wire:click.prevent="sendVerification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <x-input-label for="phone" value="Teléfono" />
            <x-text-input wire:model="phone" id="phone" name="phone" type="text" class="mt-1 block w-full" autocomplete="tel" placeholder="04XX-XXXXXXX" />
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            <x-action-message class="me-3" on="profile-updated">
                {{ __('Saved.') }}
            </x-action-message>
        </div>
    </form>
</section>
