<x-app-layout>

    <x-slot name="header">

        <div class="flex flex-col gap-1">

            <h2 class="font-display text-xl font-semibold text-slate-900">
                Mi cuenta
            </h2>

            <p class="text-sm text-slate-500">
                Consulta tus envíos y confirma tus entregas.
            </p>

        </div>

    </x-slot>


    <div class="py-8">

        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">


            {{-- Mensajes --}}
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


            {{-- Encabezado --}}
            <div class="rounded-2xl bg-white p-6 shadow-sm">

                <h1 class="font-display text-2xl font-bold text-slate-900">
                    Hola, {{ auth()->user()->name }}
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Aquí puedes consultar tus paquetes y gestionar las entregas a domicilio.
                </p>

            </div>


            {{-- Paquetes --}}
            <div class="space-y-4">

                <h2 class="font-display text-xl font-semibold text-slate-900">
                    Mis paquetes
                </h2>


                @if ($packages->isEmpty())

                    <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center">

                        <p class="text-sm text-slate-500">
                            No tienes paquetes registrados todavía.
                        </p>

                    </div>

                @else

                    @foreach ($packages as $package)

                        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">

                                <div>

                                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                                        Número de guía
                                    </p>

                                    <p class="mt-1 text-lg font-bold text-slate-900">
                                        {{ $package->tracking_number }}
                                    </p>

                                    <p class="mt-2 text-sm text-slate-500">
                                        {{ $package->origin_city }}
                                        →
                                        {{ $package->destination_city }}
                                    </p>

                                </div>


                                <span class="inline-flex w-fit rounded-full bg-blue-50 px-3 py-1 text-sm font-medium text-blue-700">
                                    {{ $package->statusLabel() }}
                                </span>

                            </div>


                            {{-- Entrega --}}
                            @if ($package->requires_delivery)

                                <div class="mt-5 rounded-xl border border-blue-100 bg-blue-50 p-4">

                                    <p class="text-sm font-semibold text-blue-900">
                                        Entrega a domicilio
                                    </p>


                                    @if ($package->delivery_address)

                                        <p class="mt-1 text-sm text-blue-800">
                                            {{ $package->delivery_address }}
                                        </p>

                                    @endif


                                    @if (
                                        $package->delivery_status
                                        === \App\Models\Package::DELIVERY_PENDING
                                    )

                                        <div class="mt-4 flex flex-wrap gap-3">

                                            <button
                                                type="button"
                                                wire:click="acceptDelivery({{ $package->id }})"
                                                wire:loading.attr="disabled"
                                                class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-60"
                                            >
                                                Aceptar entrega
                                            </button>


                                            <button
                                                type="button"
                                                wire:click="startRejectDelivery({{ $package->id }})"
                                                class="rounded-xl border border-red-200 bg-white px-4 py-2.5 text-sm font-semibold text-red-700 hover:bg-red-50"
                                            >
                                                Rechazar entrega
                                            </button>

                                        </div>


                                    @elseif (
                                        $package->delivery_status
                                        === \App\Models\Package::DELIVERY_ACCEPTED
                                    )

                                        <div class="mt-3 rounded-lg bg-emerald-100 p-3 text-sm text-emerald-700">
                                            Has aceptado la entrega a domicilio.
                                        </div>


                                    @elseif (
                                        $package->delivery_status
                                        === \App\Models\Package::DELIVERY_REJECTED
                                    )

                                        <div class="mt-3 rounded-lg bg-red-100 p-3 text-sm text-red-700">
                                            Has rechazado la entrega.
                                        </div>


                                    @elseif (
                                        $package->delivery_status
                                        === \App\Models\Package::DELIVERY_COMPLETED
                                    )

                                        <div class="mt-3 rounded-lg bg-emerald-100 p-3 text-sm text-emerald-700">
                                            Entrega completada.
                                        </div>

                                    @endif

                                </div>

                            @endif


                            {{-- Historial --}}
                            @if ($package->histories->isNotEmpty())

                                <div class="mt-5 border-t border-slate-100 pt-5">

                                    <p class="text-sm font-semibold text-slate-800">
                                        Últimos movimientos
                                    </p>

                                    <div class="mt-3 space-y-2">

                                        @foreach ($package->histories->sortByDesc('created_at')->take(4) as $history)

                                            <div class="flex items-center justify-between gap-3 text-sm">

                                                <span class="text-slate-600">
                                                    {{ $history->eventTypeLabel() }}
                                                </span>

                                                <span class="text-xs text-slate-400">
                                                    {{ $history->created_at?->format('d/m/Y H:i') }}
                                                </span>

                                            </div>

                                        @endforeach

                                    </div>

                                </div>

                            @endif

                        </div>

                    @endforeach

                @endif

            </div>


            {{-- Modal rechazo --}}
            @if ($rejectingPackageId)

                <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">

                    <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">

                        <h3 class="font-display text-xl font-bold text-slate-900">
                            Rechazar entrega
                        </h3>

                        <p class="mt-2 text-sm text-slate-500">
                            Indica el motivo por el cual no deseas recibir el paquete a domicilio.
                        </p>


                        <textarea
                            wire:model="rejectionReason"
                            rows="4"
                            class="mt-4 w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Escribe el motivo..."
                        ></textarea>


                        @error('rejectionReason')

                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>

                        @enderror


                        <div class="mt-5 flex justify-end gap-3">

                            <button
                                type="button"
                                wire:click="cancelRejectDelivery"
                                class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50"
                            >
                                Cancelar
                            </button>


                            <button
                                type="button"
                                wire:click="rejectDelivery"
                                wire:loading.attr="disabled"
                                class="rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-60"
                            >
                                Confirmar rechazo
                            </button>

                        </div>

                    </div>

                </div>

            @endif

        </div>

    </div>

</x-app-layout>
