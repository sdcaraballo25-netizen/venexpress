<div class="space-y-6">
    <div><h1 class="font-display text-2xl font-bold text-slate-900">Recepción en agencia destino</h1><p class="mt-1 text-sm text-slate-500">Pistolea las guías que llegan físicamente a tu agencia.</p></div>
    @if($message)<div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">{{ $message }}</div>@endif
    @if($error)<div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">{{ $error }}</div>@endif
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <form wire:submit.prevent="search" class="flex gap-2"><input wire:model="trackingNumber" class="min-w-0 flex-1 rounded-xl border border-slate-300 px-4 py-3" placeholder="VEN-..."><button class="rounded-xl bg-blue-900 px-5 py-3 font-semibold text-white">Buscar</button></form>
        @if($package)<div class="mt-5 rounded-xl bg-slate-50 p-5"><div class="font-tracking font-semibold">{{ $package->tracking_number }}</div><div class="mt-1 text-sm text-slate-600">{{ $package->recipient_name }} · {{ $package->destination_city }}</div><div class="mt-2 text-xs text-slate-500">Estado: {{ $package->statusLabel() }}</div><button wire:click="receive" wire:loading.attr="disabled" class="mt-5 w-full rounded-xl bg-emerald-600 px-5 py-3 font-semibold text-white disabled:opacity-50">Registrar recepción</button></div>@endif
    </div>
</div>
