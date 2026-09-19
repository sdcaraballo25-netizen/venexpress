<div class="max-w-2xl">

    <div class="mb-8">
        <h1 class="font-display text-3xl font-bold text-[#0F172A]">
            Mi Perfil
        </h1>

        <p class="text-sm text-[#64748B] mt-1">
            Actualiza tus datos personales y tu contraseña.
        </p>
    </div>

    <div class="bg-white border border-[#E2E8F0] rounded-2xl p-6 shadow-sm mb-6">
        <livewire:profile.update-profile-information-form />
    </div>

    <div class="bg-white border border-[#E2E8F0] rounded-2xl p-6 shadow-sm mb-6">
        <livewire:profile.update-password-form />
    </div>

    @php
        $payoutEligible = (auth()->user()->isAliado() && auth()->user()->ally?->status === \App\Models\Ally::STATUS_ACTIVE)
            || (auth()->user()->isRepartidor() && auth()->user()->driver?->status === \App\Models\Driver::STATUS_ACTIVE);
    @endphp

    @if ($payoutEligible)
        <div class="bg-white border border-[#E2E8F0] rounded-2xl p-6 shadow-sm">
            <livewire:profile.payout-account-form />
        </div>
    @endif

</div>
