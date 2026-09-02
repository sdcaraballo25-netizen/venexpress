<div class="space-y-6">
    <div><h1 class="font-display text-2xl font-bold text-slate-900">Retiro en agencia</h1><p class="mt-1 text-sm text-slate-500">Verifica la guía y el documento del destinatario antes de entregar.</p></div>
    @if($message)<div class="rounded-xl bg-emerald-50 p-4 text-sm text-emerald-700">{{ $message }}</div>@endif
    @if($error)<div class="rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ $error }}</div>@endif
    <div class="rounded-2xl border bg-white p-6 shadow-sm">
        <form wire:submit.prevent="search" class="flex flex-col gap-3 sm:flex-row"><input wire:model="trackingNumber" class="flex-1 rounded-xl border px-4 py-3" placeholder="Número de guía"><button class="rounded-xl bg-blue-900 px-5 py-3 font-semibold text-white">Buscar</button></form>
        @if($package)
            <div class="mt-5 rounded-xl bg-slate-50 p-5"><div class="font-tracking font-semibold">{{ $package->tracking_number }}</div><div class="mt-1 text-sm">{{ $package->recipient_name }} · {{ $package->destination_city }}</div><div class="mt-2 text-xs text-slate-500">COD: {{ $package->is_cod ? '$'.number_format((float)$package->cod_amount_usd,2) : 'No' }}</div>
            <form wire:submit.prevent="deliver" class="mt-5 space-y-3"><input wire:model="recipientIdDoc" class="w-full rounded-xl border px-4 py-3" placeholder="Documento del destinatario"><button wire:loading.attr="disabled" wire:confirm="¿Confirmas la identidad y entrega del paquete?" class="w-full rounded-xl bg-emerald-600 px-5 py-3 font-semibold text-white">Confirmar retiro</button></form></div>
        @endif
    </div>
</div>
