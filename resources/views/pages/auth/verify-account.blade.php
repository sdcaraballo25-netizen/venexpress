<div>

    <div class="mb-8 lg:hidden">
        <x-venexpress-logo size="md" />
    </div>

    <h1 class="font-display text-2xl font-bold text-blue-950">
        Verifica tu cuenta
    </h1>

    <p class="mt-1.5 text-sm text-gray-500">
        Te enviamos un código de 6 dígitos por correo electrónico.
        Ingrésalo para activar tu cuenta.
    </p>

    @if (session('resend_success'))
        <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
            {{ session('resend_success') }}
        </div>
    @endif

    <form wire:submit="verify" class="mt-8 space-y-5">

        <div>
            <x-input-label
                for="code"
                value="Código de verificación"
            />

            <x-text-input
                wire:model="code"
                id="code"
                class="block mt-1.5 w-full text-center text-2xl font-tracking tracking-[0.5em]"
                type="text"
                inputmode="numeric"
                maxlength="6"
                autofocus
                autocomplete="one-time-code"
                placeholder="000000"
            />

            <x-input-error
                :messages="$errors->get('code')"
                class="mt-2"
            />
        </div>

        <x-primary-button class="w-full py-3">
            Verificar cuenta
        </x-primary-button>

    </form>

    <div class="mt-6 text-center text-sm text-gray-500">

        ¿No recibiste el código?

        @if ($canResend)

            <button
                type="button"
                wire:click="resend"
                wire:loading.attr="disabled"
                class="font-semibold text-blue-700 hover:text-blue-950"
            >
                Reenviar código
            </button>

        @else

            <span
                wire:poll.1s="refreshResendState"
                class="font-semibold text-gray-400"
            >
                Podrás reenviarlo en {{ $resendCooldown }}s
            </span>

        @endif

    </div>

</div>
