<div class="space-y-6">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="font-display text-2xl font-semibold text-[#111111]">
                Categorías
            </h2>
            <p class="text-sm text-slate-500">
                Categorías del catálogo de la Tienda de Emprendedores.
            </p>
        </div>

        <button
            wire:click="startCreating"
            class="rounded-xl bg-blue-900 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-800"
        >
            + Nueva categoría
        </button>
    </div>

    @if ($successMessage)
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ $successMessage }}
        </div>
    @endif

    @if ($errorMessage)
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
            {{ $errorMessage }}
        </div>
    @endif

    @if ($showForm)
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="font-display text-lg font-semibold text-slate-900">
                {{ $editingCategoriaId ? 'Editar categoría' : 'Nueva categoría' }}
            </h3>

            <form wire:submit.prevent="save" class="mt-5 flex flex-col gap-4 sm:flex-row sm:items-end">
                <div class="flex-1">
                    <label class="text-sm font-medium text-slate-600">Nombre</label>
                    <input
                        type="text"
                        wire:model="nombre"
                        placeholder="Ej. Ropa y Accesorios"
                        class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-900 focus:ring-blue-900"
                    >
                    @error('nombre')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-3">
                    <button type="button" wire:click="cancelForm"
                            class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-medium text-slate-600 hover:bg-slate-50">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="rounded-xl bg-blue-900 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-800">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase">Nombre</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-slate-500 uppercase">Productos</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-500 uppercase">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categorias as $categoria)
                        <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50 transition">
                            <td class="px-6 py-4 font-medium text-slate-900">{{ $categoria->nombre }}</td>
                            <td class="px-6 py-4 text-center text-slate-600">{{ $categoria->productos_count }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end items-center gap-2">
                                    <button wire:click="editCategoria({{ $categoria->id }})"
                                            class="px-3 py-2 rounded-lg border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                                        Editar
                                    </button>
                                    <button wire:click="eliminar({{ $categoria->id }})"
                                            wire:confirm="¿Eliminar esta categoría?"
                                            class="px-3 py-2 rounded-lg border border-red-200 text-xs font-semibold text-red-600 hover:bg-red-50 transition">
                                        Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-12 text-center text-sm text-slate-500">
                                Todavía no hay categorías.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
