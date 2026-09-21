<div class="space-y-6">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="font-display text-2xl font-semibold text-[#0F172A]">
                Almacenes
            </h2>
            <p class="text-sm text-slate-500">
                Almacenes propios de Venexpress: destino de las rutas de HUB Distribución.
            </p>
        </div>

        <button
            wire:click="startCreating"
            class="rounded-xl bg-blue-900 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-800"
        >
            + Nuevo almacén
        </button>
    </div>

    @if ($successMessage)
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ $successMessage }}
        </div>
    @endif

    @if ($coverageError)
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
            {{ $coverageError }}
        </div>
    @endif

    @if ($showForm)
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="font-display text-lg font-semibold text-slate-900">
                {{ $editingWarehouseId ? 'Editar almacén' : 'Nuevo almacén' }}
            </h3>

            <form wire:submit.prevent="save" class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">

                <div class="sm:col-span-2">
                    <label class="text-sm font-medium text-slate-600">
                        Nombre
                    </label>
                    <input
                        type="text"
                        wire:model="name"
                        placeholder="Ej. Almacén Valencia"
                        class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-900 focus:ring-blue-900"
                    >
                    @error('name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-600">
                        Estado
                    </label>
                    <select
                        wire:model.live="state"
                        class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-900 focus:ring-blue-900"
                    >
                        <option value="">Seleccionar estado</option>
                        @foreach ($states as $stateOption)
                            <option value="{{ $stateOption }}">{{ $stateOption }}</option>
                        @endforeach
                    </select>
                    @error('state')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-600">
                        Ciudad
                    </label>
                    <select
                        wire:model.live="city"
                        @disabled($state === '')
                        class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-900 focus:ring-blue-900 disabled:bg-slate-100"
                    >
                        <option value="">
                            {{ $state === '' ? 'Primero selecciona un estado' : 'Seleccionar ciudad' }}
                        </option>
                        @foreach ($cities as $cityOption)
                            <option value="{{ $cityOption }}">{{ $cityOption }}</option>
                        @endforeach
                    </select>
                    @error('city')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="text-sm font-medium text-slate-600">
                        Dirección (opcional)
                    </label>
                    <input
                        type="text"
                        wire:model="address"
                        placeholder="Av. Principal, Zona Industrial"
                        class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-900 focus:ring-blue-900"
                    >
                    @error('address')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2 flex justify-end gap-3">
                    <button
                        type="button"
                        wire:click="cancelForm"
                        class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="rounded-xl bg-blue-900 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-800"
                    >
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    @endif

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        @if ($warehouses->isEmpty())
            <div class="p-6 text-sm text-slate-500">
                Todavía no hay almacenes registrados.
            </div>
        @else
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-400">
                    <tr>
                        <th class="px-6 py-3 text-left">Nombre</th>
                        <th class="px-6 py-3 text-left">Ciudad</th>
                        <th class="px-6 py-3 text-left">Estado</th>
                        <th class="px-6 py-3 text-left">Estatus</th>
                        <th class="px-6 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($warehouses as $warehouse)
                        <tr>
                            <td class="px-6 py-4 font-medium text-slate-800">
                                {{ $warehouse->name }}
                            </td>
                            <td class="px-6 py-4 text-slate-600">
                                {{ $warehouse->city }}
                            </td>
                            <td class="px-6 py-4 text-slate-600">
                                {{ $warehouse->state }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold
                                    {{ $warehouse->is_active
                                        ? 'bg-emerald-50 text-emerald-700'
                                        : 'bg-slate-100 text-slate-500' }}">
                                    {{ $warehouse->is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <button
                                    wire:click="editWarehouse({{ $warehouse->id }})"
                                    class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                                >
                                    Editar
                                </button>
                                <button
                                    wire:click="toggleActive({{ $warehouse->id }})"
                                    class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                                >
                                    {{ $warehouse->is_active ? 'Desactivar' : 'Activar' }}
                                </button>
                                <button
                                    wire:click="toggleCoveragePanel({{ $warehouse->id }})"
                                    class="rounded-lg border border-blue-200 px-3 py-2 text-xs font-semibold text-blue-900 hover:bg-blue-50"
                                >
                                    Cobertura ({{ $warehouse->coverages->count() }})
                                </button>
                            </td>
                        </tr>

                        @if ($coverageWarehouseId === $warehouse->id)
                            <tr>
                                <td colspan="5" class="bg-slate-50 px-6 py-5">
                                    <h4 class="text-sm font-semibold text-slate-700">
                                        Cobertura de "{{ $warehouse->name }}"
                                    </h4>
                                    <p class="mt-1 text-xs text-slate-500">
                                        Estados/ciudades que este almacén atiende como destino logístico
                                        (usado por la resolución de Fase 4, no restringe rutas ni escaneos).
                                    </p>

                                    @if ($warehouse->coverages->isEmpty())
                                        <p class="mt-4 text-sm text-slate-500">
                                            Este almacén todavía no tiene cobertura configurada.
                                        </p>
                                    @else
                                        <ul class="mt-4 divide-y divide-slate-200 rounded-xl border border-slate-200 bg-white">
                                            @foreach ($warehouse->coverages as $coverage)
                                                <li class="flex items-center justify-between px-4 py-3 text-sm">
                                                    <span class="text-slate-700">
                                                        {{ $coverage->city ?? 'Todo el estado' }}, {{ $coverage->state }}
                                                    </span>
                                                    <span class="flex items-center gap-3">
                                                        <span class="rounded-full px-3 py-1 text-xs font-semibold
                                                            {{ $coverage->is_active
                                                                ? 'bg-emerald-50 text-emerald-700'
                                                                : 'bg-slate-100 text-slate-500' }}">
                                                            {{ $coverage->is_active ? 'Activa' : 'Inactiva' }}
                                                        </span>
                                                        <button
                                                            wire:click="toggleCoverageActive({{ $coverage->id }})"
                                                            class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                                                        >
                                                            {{ $coverage->is_active ? 'Desactivar' : 'Activar' }}
                                                        </button>
                                                    </span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif

                                    <form wire:submit.prevent="addCoverage" class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-4 sm:items-end">
                                        <div>
                                            <label class="text-xs font-medium text-slate-600">Estado</label>
                                            <select
                                                wire:model.live="coverageState"
                                                class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-blue-900 focus:ring-blue-900"
                                            >
                                                <option value="">Selecciona...</option>
                                                @foreach ($states as $stateOption)
                                                    <option value="{{ $stateOption }}">{{ $stateOption }}</option>
                                                @endforeach
                                            </select>
                                            @error('coverageState')
                                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label class="text-xs font-medium text-slate-600">Ciudad</label>
                                            <select
                                                wire:model="coverageCity"
                                                @disabled($coverageState === '' || $coverageWholeState)
                                                class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-blue-900 focus:ring-blue-900 disabled:bg-slate-100"
                                            >
                                                <option value="">Selecciona...</option>
                                                @foreach ($coverageCities as $cityOption)
                                                    <option value="{{ $cityOption }}">{{ $cityOption }}</option>
                                                @endforeach
                                            </select>
                                            @error('coverageCity')
                                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div class="flex items-center gap-2 pb-2">
                                            <input
                                                type="checkbox"
                                                wire:model.live="coverageWholeState"
                                                id="coverageWholeState-{{ $warehouse->id }}"
                                                class="rounded border-slate-300 text-blue-900 focus:ring-blue-900"
                                            >
                                            <label for="coverageWholeState-{{ $warehouse->id }}" class="text-xs font-medium text-slate-600">
                                                Todo el estado
                                            </label>
                                        </div>

                                        <button
                                            type="submit"
                                            class="rounded-xl bg-blue-900 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800"
                                        >
                                            + Agregar cobertura
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </div>
</div>
