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
                        <option value="">Selecciona...</option>
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
                        wire:model="city"
                        @disabled($state === '')
                        class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-900 focus:ring-blue-900 disabled:bg-slate-50"
                    >
                        <option value="">Selecciona...</option>
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
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
