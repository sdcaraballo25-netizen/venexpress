<div class="space-y-6">

    {{-- Encabezado --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <h1 class="font-display text-2xl font-bold text-slate-900">
                Pagos pendientes
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Paquetes de pago contra entrega (COD) que todavía no has cancelado.
            </p>
        </div>

        @if ($packages->isNotEmpty())

            <div class="rounded-2xl bg-blue-900 px-5 py-3 text-white">
                <p class="text-xs uppercase tracking-wide text-blue-200">
                    Total pendiente
                </p>

                <p class="font-display text-xl font-bold">
                    ${{ number_format($totalPendingUsd, 2) }}
                </p>
            </div>

        @endif

    </div>

    {{-- Aviso: pagos en línea aún no habilitados --}}
    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
        El pago en línea (Pago Móvil / Pago inmediato) estará disponible próximamente. Por ahora, puedes cancelar
        estos paquetes en efectivo directamente al momento de la entrega.
    </div>

    {{-- Listado --}}
    <div class="space-y-4">

        @forelse ($packages as $package)

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">

                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                            Número de guía
                        </p>

                        <p class="mt-1 font-tracking text-lg font-bold text-slate-900">
                            {{ $package->tracking_number }}
                        </p>

                        <p class="mt-2 text-sm text-slate-500">
                            {{ $package->origin_city }} → {{ $package->destination_city }}
                        </p>

                        <span class="mt-2 inline-flex w-fit rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700">
                            {{ $package->statusLabel() }}
                        </span>
                    </div>

                    <div class="text-left lg:text-right">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                            Monto a pagar
                        </p>

                        <p class="mt-1 font-display text-2xl font-bold text-slate-900">
                            ${{ number_format((float) $package->cod_amount_usd, 2) }}
                        </p>
                    </div>

                </div>

                {{-- Botones de pago (deshabilitados por ahora) --}}
                <div class="mt-5 flex flex-wrap gap-3 border-t border-slate-100 pt-5">

                    <button
                        type="button"
                        disabled
                        title="Próximamente disponible"
                        class="cursor-not-allowed rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-400"
                    >
                        Pagar con Pago Móvil
                    </button>

                    <button
                        type="button"
                        disabled
                        title="Próximamente disponible"
                        class="cursor-not-allowed rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-400"
                    >
                        Pago inmediato
                    </button>

                </div>

            </div>

        @empty

            <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center">
                <p class="text-sm text-slate-500">
                    No tienes pagos pendientes. 🎉
                </p>
            </div>

        @endforelse

    </div>

</div>
