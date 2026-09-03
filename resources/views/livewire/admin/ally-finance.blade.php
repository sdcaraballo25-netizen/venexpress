<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-slate-900">
            Finanzas de aliados
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Control de comisiones, saldos, liquidaciones, ajustes y reversos.
        </p>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

            <div>
                <h2 class="font-semibold text-slate-900">
                    Aliados
                </h2>

                <p class="text-sm text-slate-500">
                    Selecciona una agencia para administrar su cuenta.
                </p>
            </div>

            <input
                type="text"
                wire:model.live.debounce.400ms="search"
                placeholder="Buscar aliado, RIF o ciudad..."
                class="w-full rounded-xl border-slate-300 md:w-80"
            >

        </div>

        <div class="mt-5 overflow-x-auto">

            <table class="min-w-full text-sm">

                <thead>
                    <tr class="border-b border-slate-200 text-left text-slate-500">
                        <th class="px-3 py-3">Aliado</th>
                        <th class="px-3 py-3">Ciudad</th>
                        <th class="px-3 py-3">Estado</th>
                        <th class="px-3 py-3">Acción</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($allies as $ally)

                        <tr class="border-b border-slate-100">

                            <td class="px-3 py-3 font-medium text-slate-900">
                                {{ $ally->business_name }}

                                <div class="text-xs text-slate-500">
                                    {{ $ally->rif }}
                                </div>
                            </td>

                            <td class="px-3 py-3">
                                {{ $ally->city }}
                            </td>

                            <td class="px-3 py-3">
                                {{ $ally->status }}
                            </td>

                            <td class="px-3 py-3">
                                <button
                                    type="button"
                                    wire:click="selectAlly({{ $ally->id }})"
                                    class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-700"
                                >
                                    Administrar
                                </button>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="4"
                                class="px-3 py-8 text-center text-slate-500"
                            >
                                No se encontraron aliados.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="mt-4">
            {{ $allies->links() }}
        </div>

    </div>


    @if ($selectedAlly)

        <div class="grid gap-4 md:grid-cols-3">

            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
                <p class="text-sm text-emerald-700">
                    Saldo disponible
                </p>

                <p class="mt-2 text-3xl font-bold text-emerald-900">
                    ${{ number_format($balance, 2) }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <p class="text-sm text-slate-500">
                    Comisiones generadas
                </p>

                <p class="mt-2 text-3xl font-bold text-slate-900">
                    ${{ number_format($generated, 2) }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <p class="text-sm text-slate-500">
                    Total pagado
                </p>

                <p class="mt-2 text-3xl font-bold text-slate-900">
                    ${{ number_format($paid, 2) }}
                </p>
            </div>

        </div>


        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <h2 class="text-lg font-semibold text-slate-900">
                {{ $selectedAlly->business_name }}
            </h2>

            <p class="text-sm text-slate-500">
                {{ $selectedAlly->city }} · {{ $selectedAlly->state }}
            </p>

            <div class="mt-6 grid gap-6 lg:grid-cols-2">

                <form
                    wire:submit="createSettlement"
                    class="rounded-xl border border-slate-200 p-4"
                >

                    <h3 class="font-semibold text-slate-900">
                        Nueva liquidación
                    </h3>

                    <div class="mt-4 space-y-4">

                        <div>
                            <label class="text-sm font-medium">
                                Monto USD
                            </label>

                            <input
                                type="number"
                                step="0.01"
                                min="0.01"
                                wire:model="settlementAmount"
                                class="mt-1 w-full rounded-xl border-slate-300"
                            >

                            @error('settlementAmount')
                                <p class="mt-1 text-xs text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium">
                                Método
                            </label>

                            <select
                                wire:model="paymentMethod"
                                class="mt-1 w-full rounded-xl border-slate-300"
                            >
                                <option value="">
                                    Seleccionar
                                </option>

                                @foreach (\App\Models\AllySettlement::PAYMENT_METHODS as $method)
                                    <option value="{{ $method }}">
                                        {{ ucfirst(str_replace('_', ' ', $method)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="text-sm font-medium">
                                Referencia
                            </label>

                            <input
                                type="text"
                                wire:model="paymentReference"
                                class="mt-1 w-full rounded-xl border-slate-300"
                            >
                        </div>

                        <div>
                            <label class="text-sm font-medium">
                                Notas
                            </label>

                            <textarea
                                wire:model="settlementNotes"
                                rows="3"
                                class="mt-1 w-full rounded-xl border-slate-300"
                            ></textarea>
                        </div>

                        <button
                            type="submit"
                            class="w-full rounded-xl bg-slate-900 px-4 py-3 font-semibold text-white hover:bg-slate-700"
                        >
                            Crear liquidación
                        </button>

                    </div>

                </form>


                <form
                    wire:submit="createAdjustment"
                    class="rounded-xl border border-slate-200 p-4"
                >

                    <h3 class="font-semibold text-slate-900">
                        Ajuste manual
                    </h3>

                    <div class="mt-4 space-y-4">

                        <div>
                            <label class="text-sm font-medium">
                                Tipo
                            </label>

                            <select
                                wire:model="adjustmentDirection"
                                class="mt-1 w-full rounded-xl border-slate-300"
                            >
                                <option value="credit">
                                    Crédito (+)
                                </option>

                                <option value="debit">
                                    Débito (-)
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="text-sm font-medium">
                                Monto USD
                            </label>

                            <input
                                type="number"
                                step="0.01"
                                min="0.01"
                                wire:model="adjustmentAmount"
                                class="mt-1 w-full rounded-xl border-slate-300"
                            >

                            @error('adjustmentAmount')
                                <p class="mt-1 text-xs text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium">
                                Motivo
                            </label>

                            <textarea
                                wire:model="adjustmentDescription"
                                rows="3"
                                class="mt-1 w-full rounded-xl border-slate-300"
                            ></textarea>

                            @error('adjustmentDescription')
                                <p class="mt-1 text-xs text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium">
                                Referencia
                            </label>

                            <input
                                type="text"
                                wire:model="adjustmentReference"
                                class="mt-1 w-full rounded-xl border-slate-300"
                            >
                        </div>

                        <button
                            type="submit"
                            class="w-full rounded-xl bg-amber-600 px-4 py-3 font-semibold text-white hover:bg-amber-700"
                        >
                            Registrar ajuste
                        </button>

                    </div>

                </form>

            </div>

        </div>


        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <h2 class="text-lg font-semibold text-slate-900">
                Liquidaciones
            </h2>

            <div class="mt-4 overflow-x-auto">

                <table class="min-w-full text-sm">

                    <thead>
                        <tr class="border-b border-slate-200 text-left text-slate-500">
                            <th class="px-3 py-3">ID</th>
                            <th class="px-3 py-3">Monto</th>
                            <th class="px-3 py-3">Estado</th>
                            <th class="px-3 py-3">Método</th>
                            <th class="px-3 py-3">Referencia</th>
                            <th class="px-3 py-3">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($settlements as $settlement)

                            <tr class="border-b border-slate-100">

                                <td class="px-3 py-3">
                                    #{{ $settlement->id }}
                                </td>

                                <td class="px-3 py-3 font-semibold">
                                    ${{ number_format($settlement->amount_usd, 2) }}
                                </td>

                                <td class="px-3 py-3">
                                    {{ ucfirst($settlement->status) }}
                                </td>

                                <td class="px-3 py-3">
                                    {{ $settlement->payment_method ?: '—' }}
                                </td>

                                <td class="px-3 py-3">
                                    {{ $settlement->payment_reference ?: '—' }}
                                </td>

                                <td class="px-3 py-3">

                                    <div class="flex flex-wrap gap-2">

                                        @if ($settlement->isPending())

                                            <button
                                                type="button"
                                                wire:click="markPaid({{ $settlement->id }})"
                                                class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white"
                                            >
                                                Marcar pagada
                                            </button>

                                            <button
                                                type="button"
                                                wire:click="cancelSettlement({{ $settlement->id }})"
                                                class="rounded-lg bg-slate-200 px-3 py-2 text-xs font-semibold text-slate-700"
                                            >
                                                Cancelar
                                            </button>

                                        @elseif ($settlement->isPaid())

                                            <button
                                                type="button"
                                                wire:click="openReversal({{ $settlement->id }})"
                                                class="rounded-lg bg-red-600 px-3 py-2 text-xs font-semibold text-white"
                                            >
                                                Revertir
                                            </button>

                                        @else

                                            <span class="text-xs text-slate-400">
                                                Sin acciones
                                            </span>

                                        @endif

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td
                                    colspan="6"
                                    class="px-3 py-8 text-center text-slate-500"
                                >
                                    No hay liquidaciones registradas.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>


        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <h2 class="text-lg font-semibold text-slate-900">
                Historial financiero
            </h2>

            <div class="mt-4 overflow-x-auto">

                <table class="min-w-full text-sm">

                    <thead>
                        <tr class="border-b border-slate-200 text-left text-slate-500">
                            <th class="px-3 py-3">Fecha</th>
                            <th class="px-3 py-3">Tipo</th>
                            <th class="px-3 py-3">Movimiento</th>
                            <th class="px-3 py-3">Monto</th>
                            <th class="px-3 py-3">Referencia</th>
                            <th class="px-3 py-3">Descripción</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($transactions as $transaction)

                            <tr class="border-b border-slate-100">

                                <td class="px-3 py-3">
                                    {{ $transaction->created_at?->format('d/m/Y H:i') }}
                                </td>

                                <td class="px-3 py-3">
                                    {{ ucfirst($transaction->type) }}
                                </td>

                                <td class="px-3 py-3">
                                    @if ($transaction->direction === 'credit')
                                        <span class="font-semibold text-emerald-600">
                                            Crédito
                                        </span>
                                    @else
                                        <span class="font-semibold text-red-600">
                                            Débito
                                        </span>
                                    @endif
                                </td>

                                <td class="px-3 py-3 font-semibold">
                                    ${{ number_format($transaction->amount_usd, 2) }}
                                </td>

                                <td class="px-3 py-3">
                                    {{ $transaction->reference ?: '—' }}
                                </td>

                                <td class="px-3 py-3">
                                    {{ $transaction->description }}
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td
                                    colspan="6"
                                    class="px-3 py-8 text-center text-slate-500"
                                >
                                    No hay movimientos financieros.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>


        @if ($reversingSettlementId)

            <div class="rounded-2xl border border-red-200 bg-red-50 p-5">

                <h2 class="font-semibold text-red-900">
                    Revertir liquidación #{{ $reversingSettlementId }}
                </h2>

                <p class="mt-1 text-sm text-red-700">
                    El reverso creará un nuevo crédito y conservará
                    intacto el movimiento original.
                </p>

                <textarea
                    wire:model="reversalReason"
                    rows="3"
                    placeholder="Indica el motivo del reverso..."
                    class="mt-4 w-full rounded-xl border-red-300"
                ></textarea>

                @error('reversalReason')
                    <p class="mt-1 text-xs text-red-600">
                        {{ $message }}
                    </p>
                @enderror

                <div class="mt-4 flex gap-2">

                    <button
                        type="button"
                        wire:click="reverseSettlement"
                        class="rounded-xl bg-red-600 px-4 py-3 font-semibold text-white"
                    >
                        Confirmar reverso
                    </button>

                    <button
                        type="button"
                        wire:click="$set('reversingSettlementId', null)"
                        class="rounded-xl bg-white px-4 py-3 font-semibold text-slate-700"
                    >
                        Cancelar
                    </button>

                </div>

            </div>

        @endif

    @endif

</div>
