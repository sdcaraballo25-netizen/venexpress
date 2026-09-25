<div class="space-y-6 font-sans">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="font-display text-3xl font-bold tracking-tight text-[#111111]">Mis Productos</h1>
            <p class="text-sm text-[#6B6B66] mt-1">Administra el catálogo que verán los clientes en la tienda.</p>
        </div>

        <button
            wire:click="startCreating"
            class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700"
        >
            + Nuevo producto
        </button>
    </div>

    @if ($successMessage)
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ $successMessage }}
        </div>
    @endif

    @if ($showForm)
        <div class="rounded-2xl border border-[#E5E5E0] bg-white p-6 shadow-sm">
            <h3 class="font-display text-lg font-semibold text-[#111111]">
                {{ $editingProductoId ? 'Editar producto' : 'Nuevo producto' }}
            </h3>

            <form wire:submit.prevent="save" class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">

                <div class="sm:col-span-2">
                    <label class="text-sm font-medium text-[#6B6B66]">Nombre</label>
                    <input type="text" wire:model="nombre" placeholder="Ej. Camisa de algodón"
                           class="mt-2 w-full rounded-xl border border-[#E5E5E0] px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('nombre') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="text-sm font-medium text-[#6B6B66]">Descripción (opcional)</label>
                    <textarea wire:model="descripcion" rows="3"
                              class="mt-2 w-full rounded-xl border border-[#E5E5E0] px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    @error('descripcion') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-sm font-medium text-[#6B6B66]">Precio (USD)</label>
                    <input type="number" step="0.01" wire:model="precio_usd"
                           class="mt-2 w-full rounded-xl border border-[#E5E5E0] px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('precio_usd') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-sm font-medium text-[#6B6B66]">Peso (kg)</label>
                    <input type="number" step="0.001" wire:model="peso_kg"
                           class="mt-2 w-full rounded-xl border border-[#E5E5E0] px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <p class="mt-1 text-xs text-[#B8B8B2]">Se usa para calcular el costo del envío.</p>
                    @error('peso_kg') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-sm font-medium text-[#6B6B66]">Stock disponible</label>
                    <input type="number" wire:model="stock"
                           class="mt-2 w-full rounded-xl border border-[#E5E5E0] px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('stock') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-sm font-medium text-[#6B6B66]">Categoría (opcional)</label>
                    <select wire:model="categoria_id"
                            class="mt-2 w-full rounded-xl border border-[#E5E5E0] px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Sin categoría</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                        @endforeach
                    </select>
                    @error('categoria_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="text-sm font-medium text-[#6B6B66]">Fotos (hasta 6, opcional)</label>
                    <input type="file" wire:model="fotos" accept="image/*" multiple
                           class="mt-2 block w-full text-sm text-[#6B6B66]
                                  file:mr-4 file:py-2 file:px-4 file:rounded-xl
                                  file:border-0 file:text-sm file:font-semibold
                                  file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="mt-1 text-xs text-[#6B6B66]" wire:loading wire:target="fotos">Subiendo fotos...</p>
                    @error('fotos') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    @error('fotos.*') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach ($fotos as $foto)
                            <img src="{{ $foto->temporaryUrl() }}" class="h-20 w-20 rounded-xl object-cover" alt="Vista previa">
                        @endforeach

                        @foreach ($existingFotos as $existingFoto)
                            <div class="relative">
                                <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($existingFoto->path) }}"
                                     class="h-20 w-20 rounded-xl object-cover" alt="Foto del producto">
                                <button type="button" wire:click="eliminarFoto({{ $existingFoto->id }})"
                                        wire:confirm="¿Quitar esta foto del producto?"
                                        class="absolute -top-1.5 -right-1.5 h-5 w-5 rounded-full bg-red-600 text-white text-xs leading-5">
                                    ✕
                                </button>
                            </div>
                        @endforeach

                        @if ($existingFotos->isEmpty() && ! $fotos && $existingFotoPath)
                            <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($existingFotoPath) }}"
                                 class="h-20 w-20 rounded-xl object-cover" alt="Foto actual">
                        @endif
                    </div>
                </div>

                <div class="sm:col-span-2 flex justify-end gap-3">
                    <button type="button" wire:click="cancelForm"
                            class="rounded-xl border border-[#E5E5E0] bg-white px-5 py-3 text-sm font-medium text-[#6B6B66] hover:bg-slate-50">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                        Guardar
                    </button>
                </div>

            </form>
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse ($productos as $producto)
            <div class="bg-white rounded-2xl border border-[#E5E5E0] shadow-sm overflow-hidden">

                @if ($producto->foto_principal_path)
                    <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($producto->foto_principal_path) }}" class="h-36 w-full object-cover" alt="{{ $producto->nombre }}">
                @else
                    <div class="h-36 w-full bg-slate-100 flex items-center justify-center text-slate-300 text-4xl">📦</div>
                @endif

                <div class="p-4">
                    <div class="flex items-start justify-between gap-2">
                        <p class="font-semibold text-[#111111]">{{ $producto->nombre }}</p>
                        <span class="shrink-0 text-xs font-semibold px-2 py-1 rounded-lg {{ $producto->activo ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                            {{ $producto->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>

                    @if ($producto->categoria)
                        <p class="mt-0.5 text-xs text-[#6B6B66]">{{ $producto->categoria->nombre }}</p>
                    @endif

                    <p class="mt-1 text-lg font-bold text-[#111111]">${{ number_format((float) $producto->precio_usd, 2) }}</p>
                    <p class="text-xs text-[#6B6B66]">Stock: {{ $producto->stock }} · {{ $producto->peso_kg }} kg</p>

                    <div class="mt-4 flex items-center gap-2">
                        <button wire:click="editProducto({{ $producto->id }})"
                                class="flex-1 rounded-xl border border-[#E5E5E0] px-3 py-2 text-xs font-semibold text-[#111111] hover:bg-slate-50">
                            Editar
                        </button>
                        <button wire:click="toggleActivo({{ $producto->id }})"
                                class="flex-1 rounded-xl px-3 py-2 text-xs font-semibold
                                    {{ $producto->activo ? 'bg-amber-50 text-amber-700 hover:bg-amber-100' : 'bg-blue-50 text-blue-700 hover:bg-blue-100' }}">
                            {{ $producto->activo ? 'Desactivar' : 'Activar' }}
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="sm:col-span-2 lg:col-span-3 rounded-2xl border border-dashed border-[#E5E5E0] p-10 text-center text-sm text-[#6B6B66]">
                Todavía no has agregado productos. Crea el primero con el botón de arriba.
            </div>
        @endforelse
    </div>

</div>
