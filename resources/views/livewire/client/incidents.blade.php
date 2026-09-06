<div class="space-y-6">

    {{-- Encabezado --}}
    <div>
        <h1 class="font-display text-2xl font-bold text-slate-900">
            Incidencias
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Consulta el estado de tus reportes o crea uno nuevo sobre alguno de tus paquetes.
        </p>
    </div>

    {{-- Mensajes --}}
    @if (session('success'))

        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
            {{ session('success') }}
        </div>

    @endif

    {{-- ======================================================
         FORMULARIO: CREAR REPORTE
    ======================================================= --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

        <h2 class="font-display text-lg font-semibold text-slate-900">
            Reportar un problema
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            Indica el número de guía y cuéntanos qué sucedió. Nuestro equipo lo revisará.
        </p>

        <form wire:submit="create" class="mt-5 space-y-4">

            <div>
                <label
                    for="trackingNumber"
                    class="text-sm font-medium text-slate-700"
                >
                    Número de guía
                </label>

                <input
                    wire:model="trackingNumber"
                    id="trackingNumber"
                    type="text"
                    class="mt-1.5 w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="Ej. VEN-000123"
                >

                @error('trackingNumber')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label
                    for="description"
                    class="text-sm font-medium text-slate-700"
                >
                    Descripción del problema
                </label>

                <textarea
                    wire:model="description"
                    id="description"
                    rows="4"
                    class="mt-1.5 w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="Cuéntanos qué pasó con tu envío..."
                ></textarea>

                @error('description')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="submit"
                wire:loading.attr="disabled"
                class="rounded-xl bg-blue-900 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-800 disabled:opacity-60"
            >
                Enviar reporte
            </button>

        </form>

    </div>

    {{-- ======================================================
         LISTADO DE INCIDENCIAS
    ======================================================= --}}
    <div class="space-y-3">

        <h2 class="font-display text-lg font-semibold text-slate-900">
            Mis reportes
        </h2>

        @forelse ($incidents as $incident)

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                <div class="flex flex-wrap items-start justify-between gap-3">

                    <div>
                        <p class="font-tracking text-xs text-slate-400">
                            {{ $incident->package?->tracking_number }}
                        </p>

                        <p class="mt-1 text-sm font-semibold text-slate-900">
                            {{ str($incident->type)->replace('_', ' ')->title() }}
                        </p>
                    </div>

                    <span
                        @class([
                            'rounded-full px-3 py-1 text-xs font-medium',
                            'bg-amber-50 text-amber-700' => $incident->status === \App\Models\Incident::STATUS_OPEN,
                            'bg-blue-50 text-blue-700' => $incident->status === \App\Models\Incident::STATUS_IN_PROGRESS,
                            'bg-emerald-50 text-emerald-700' => $incident->isResolved(),
                        ])
                    >
                        {{ str($incident->status)->replace('_', ' ')->title() }}
                    </span>

                </div>

                <p class="mt-3 text-sm text-slate-600">
                    {{ $incident->description }}
                </p>

                @if ($incident->resolution_notes)

                    <div class="mt-4 rounded-xl bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Respuesta de VenExpress
                        </p>

                        <p class="mt-1 text-sm text-slate-700">
                            {{ $incident->resolution_notes }}
                        </p>
                    </div>

                @endif

                <p class="mt-3 text-xs text-slate-400">
                    Reportado el {{ $incident->created_at?->format('d/m/Y H:i') }}
                </p>

            </div>

        @empty

            <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center">
                <p class="text-sm text-slate-500">
                    No has reportado ninguna incidencia todavía.
                </p>
            </div>

        @endforelse

        {{ $incidents->links() }}

    </div>

</div>
