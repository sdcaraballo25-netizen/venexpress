<div class="space-y-6">
    <div>
        <h2 class="font-display text-2xl font-semibold text-slate-900">Corte de caja y liquidaciones</h2>
        <p class="text-sm text-slate-500">Solicita el pago de tu saldo disponible y consulta el historial financiero.</p>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-5"><p class="text-sm text-slate-500">Saldo disponible</p><p class="mt-1 text-2xl font-semibold text-emerald-700">${{ number_format($balance, 2) }}</p></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5"><p class="text-sm text-slate-500">Comisiones generadas</p><p class="mt-1 text-2xl font-semibold text-slate-900">${{ number_format($generated, 2) }}</p></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5"><p class="text-sm text-slate-500">Total pagado</p><p class="mt-1 text-2xl font-semibold text-slate-900">${{ number_format($paid, 2) }}</p></div>
    </div>

    <form wire:submit="requestSettlement" class="rounded-2xl border border-slate-200 bg-white p-5 space-y-4">
        <div><h3 class="font-semibold text-slate-900">Solicitar liquidación</h3><p class="text-sm text-slate-500">La solicitud queda pendiente hasta que administración confirme el pago.</p></div>
        <div class="grid gap-4 md:grid-cols-2">
            <label class="text-sm text-slate-700">Monto USD<input wire:model="amountUsd" type="number" step="0.01" min="0.01" class="mt-1 w-full rounded-xl border-slate-300" placeholder="0.00"></label>
            <label class="text-sm text-slate-700">Método de pago<select wire:model="paymentMethod" class="mt-1 w-full rounded-xl border-slate-300"><option value="">Sin especificar</option>@foreach (\App\Models\AllySettlement::PAYMENT_METHODS as $method)<option value="{{ $method }}">{{ ucfirst(str_replace('_', ' ', $method)) }}</option>@endforeach</select></label>
            <label class="text-sm text-slate-700">Referencia<input wire:model="paymentReference" type="text" class="mt-1 w-full rounded-xl border-slate-300" maxlength="120"></label>
            <label class="text-sm text-slate-700">Notas<textarea wire:model="notes" rows="2" class="mt-1 w-full rounded-xl border-slate-300"></textarea></label>
        </div>
        @error('amountUsd')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Solicitar liquidación</button>
    </form>

    <div class="rounded-2xl border border-slate-200 bg-white p-5"><h3 class="mb-4 font-semibold text-slate-900">Solicitudes recientes</h3><div class="overflow-x-auto"><table class="min-w-full text-sm"><thead><tr class="border-b text-left text-slate-500"><th class="py-2">Fecha</th><th class="py-2">Monto</th><th class="py-2">Estado</th><th class="py-2">Método</th></tr></thead><tbody>@forelse ($settlements as $settlement)<tr class="border-b last:border-0"><td class="py-2">{{ optional($settlement->created_at)->format('d/m/Y H:i') }}</td><td class="py-2">${{ number_format((float) $settlement->amount_usd, 2) }}</td><td class="py-2">{{ ucfirst($settlement->status) }}</td><td class="py-2">{{ $settlement->payment_method ? str_replace('_', ' ', $settlement->payment_method) : '—' }}</td></tr>@empty<tr><td colspan="4" class="py-4 text-slate-500">No hay solicitudes registradas.</td></tr>@endforelse</tbody></table></div>{{ $settlements->links() }}</div>
</div>
