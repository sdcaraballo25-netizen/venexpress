<div class="space-y-6">

    {{-- ENCABEZADO --}}
    <div>
        <h2 class="font-display text-2xl font-semibold text-[#0F172A]">
            Mis pedidos
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            Consulta las guías registradas por tu agencia aliada.
        </p>
    </div>

    {{-- FILTROS --}}
    <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            {{-- BUSCAR --}}
            <div class="md:col-span-2">

                <label class="text-sm text-slate-600">
                    Buscar pedido
                </label>

                <input
                    type="text"
                    wire:model.live.debounce.400ms="search"
                    placeholder="Guía, remitente, destinatario o documento..."
                    class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900"
                >

            </div>

            {{-- ESTADO --}}
            <div>

                <label class="text-sm text-slate-600">
                    Estado
                </label>

                <select
                    wire:model.live="status"
                    class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900"
                >
                    <option value="">
                        Todos
                    </option>

                    <option value="{{ \App\Models\Package::STATUS_RECIBIDO_AGENCIA }}">
                        Recibido en agencia
                    </option>

                    <option value="{{ \App\Models\Package::STATUS_RECOLECTADO_VENEXPRESS }}">
                        Recolectado
                    </option>

                    <option value="{{ \App\Models\Package::STATUS_EN_HUB }}">
                        En hub
                    </option>

                    <option value="{{ \App\Models\Package::STATUS_EN_TRANSITO_NACIONAL }}">
                        En tránsito nacional
                    </option>

                    <option value="{{ \App\Models\Package::STATUS_LISTO_RETIRO }}">
                        Listo para retiro
                    </option>

                    <option value="{{ \App\Models\Package::STATUS_ENTREGADO }}">
                        Entregado
                    </option>
                </select>

            </div>

        </div>

    </div>

    {{-- LISTADO --}}
    <div class="rounded-2xl border border-[#E2E8F0] bg-white shadow-sm overflow-hidden">

        <div class="overflow-x-auto">

            <table class="min-w-full text-sm">

                <thead class="bg-slate-50 border-b border-slate-200">

                    <tr>
                        <th class="px-5 py-3 text-left font-semibold text-slate-600">
                            Guía
                        </th>

                        <th class="px-5 py-3 text-left font-semibold text-slate-600">
                            Remitente
                        </th>

                        <th class="px-5 py-3 text-left font-semibold text-slate-600">
                            Destinatario
                        </th>

                        <th class="px-5 py-3 text-left font-semibold text-slate-600">
                            Destino
                        </th>

                        <th class="px-5 py-3 text-left font-semibold text-slate-600">
                            Estado
                        </th>

                        <th class="px-5 py-3 text-left font-semibold text-slate-600">
                            Fecha
                        </th>

                        <th class="px-5 py-3 text-right font-semibold text-slate-600">
                            Acción
                        </th>
                    </tr>

                </thead>

                <tbody class="divide-y divide-slate-100">

                    @forelse ($packages as $package)

                        <tr class="hover:bg-slate-50">

                            {{-- GUÍA --}}
                            <td class="px-5 py-4">

                                <div class="font-tracking text-xs font-medium text-blue-900">
                                    {{ $package->tracking_number }}
                                </div>

                            </td>

                            {{-- REMITENTE --}}
                            <td class="px-5 py-4">

                                <div class="font-medium text-[#0F172A]">
                                    {{ $package->sender_name }}
                                </div>

                                <div class="text-xs text-slate-500">
                                    {{ $package->sender_id_doc }}
                                </div>

                            </td>

                            {{-- DESTINATARIO --}}
                            <td class="px-5 py-4">

                                <div class="font-medium text-[#0F172A]">
                                    {{ $package->recipient_name }}
                                </div>

                                <div class="text-xs text-slate-500">
                                    {{ $package->recipient_id_doc }}
                                </div>

                            </td>

                            {{-- DESTINO --}}
                            <td class="px-5 py-4">

                                <div class="font-medium text-[#0F172A]">
                                    {{ $package->destination_city }}
                                </div>

                                <div class="text-xs text-slate-500">
                                    {{ $package->destination_state }}
                                </div>

                            </td>

                            {{-- ESTADO --}}
                            <td class="px-5 py-4">

                                @php
                                    $statusClasses = match ($package->current_status) {
                                        \App\Models\Package::STATUS_ENTREGADO
                                            => 'bg-emerald-50 text-emerald-700',

                                        \App\Models\Package::STATUS_LISTO_RETIRO
                                            => 'bg-blue-50 text-blue-700',

                                        \App\Models\Package::STATUS_EN_TRANSITO_NACIONAL
                                            => 'bg-amber-50 text-amber-700',

                                        \App\Models\Package::STATUS_EN_HUB
                                            => 'bg-violet-50 text-violet-700',

                                        default
                                            => 'bg-slate-100 text-slate-700',
                                    };
                                @endphp

                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $statusClasses }}">
                                    {{ $this->statusLabel($package->current_status) }}
                                </span>

                            </td>

                            {{-- FECHA --}}
                            <td class="px-5 py-4 text-slate-500">

                                {{ $package->created_at?->format('d/m/Y H:i') }}

                            </td>

                            {{-- ACCIÓN --}}
                            <td class="px-5 py-4 text-right">

                                @if ($package->id)
                                    <a
                                        href="{{ route('packages.label', $package->id) }}"
                                        target="_blank"
                                        class="inline-flex items-center rounded-lg border border-blue-900 px-3 py-2 text-xs font-medium text-blue-900 hover:bg-blue-50"
                                    >
                                        Ver guía
                                    </a>
                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="7"
                                class="px-5 py-12 text-center"
                            >
                                <p class="font-medium text-[#0F172A]">
                                    No hay pedidos para mostrar.
                                </p>

                                <p class="mt-1 text-sm text-slate-500">
                                    Cuando registres una nueva guía, aparecerá aquí.
                                </p>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        {{-- PAGINACIÓN --}}
        @if ($packages->hasPages())
            <div class="border-t border-slate-200 px-5 py-4">
                {{ $packages->links() }}
            </div>
        @endif

    </div>

</div>

