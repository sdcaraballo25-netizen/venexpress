<div class="space-y-6">

    <div>
        <h2 class="font-display text-2xl font-semibold text-[#0F172A]">
            Gestión de Taquillas
        </h2>
        <p class="text-sm text-slate-500 mt-1">
            Crea un acceso por cada taquilla de tu comercio. Cada una puede registrar y ver guías, y cerrar su caja del día — sin ver el resto del negocio.
        </p>
    </div>

    @if ($successMessage)
        <div class="rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">
            {{ $successMessage }}
        </div>
    @endif

    <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-display text-lg font-semibold text-[#0F172A]">
                Taquillas ({{ $staff->total() }})
            </h3>

            @unless ($showForm)
                <button
                    type="button"
                    wire:click="startCreate"
                    class="rounded-xl bg-blue-900 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800"
                >
                    + Nueva taquilla
                </button>
            @endunless
        </div>

        @if ($showForm)
            <form wire:submit="save" class="grid grid-cols-1 md:grid-cols-2 gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4 mb-5">
                <div class="md:col-span-2">
                    <h4 class="text-sm font-semibold text-[#0F172A]">
                        {{ $editingId ? 'Editar taquilla' : 'Nueva taquilla' }}
                    </h4>
                </div>

                <div>
                    <label class="text-sm text-slate-600">Nombre</label>
                    <input type="text" wire:model="name"
                        placeholder="Ej. Taquilla Los Próceres"
                        class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900">
                    @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-sm text-slate-600">Usuario de acceso</label>
                    <input type="text" wire:model="username"
                        placeholder="taquilla1"
                        autocapitalize="off" autocorrect="off"
                        class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900">
                    <p class="text-xs text-slate-400 mt-1">Solo minúsculas, números, puntos y guiones — sin correo.</p>
                    @error('username') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-sm text-slate-600">
                        Contraseña {{ $editingId ? '(déjala vacía para no cambiarla)' : '' }}
                    </label>
                    <input type="password" wire:model="password"
                        class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900">
                    @error('password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-sm text-slate-600">Confirmar contraseña</label>
                    <input type="password" wire:model="password_confirmation"
                        class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900">
                </div>

                <div class="md:col-span-2 flex items-center gap-3">
                    <button type="submit" class="rounded-xl bg-blue-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-800">
                        {{ $editingId ? 'Guardar cambios' : 'Crear taquilla' }}
                    </button>
                    <button type="button" wire:click="cancel" class="text-sm font-medium text-slate-500 hover:text-slate-700">
                        Cancelar
                    </button>
                </div>
            </form>
        @endif

        @if ($staff->isEmpty())
            <p class="text-sm text-slate-400 py-6 text-center">
                Todavía no has creado ninguna taquilla. Todas las guías las estás registrando tú directamente.
            </p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-slate-400 border-b border-slate-200">
                            <th class="py-2 pr-4">Taquilla</th>
                            <th class="py-2 pr-4">Usuario</th>
                            <th class="py-2 pr-4">Estado</th>
                            <th class="py-2 pr-4">Hoy</th>
                            <th class="py-2 pr-4"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($staff as $member)
                            @php $sales = $todaySalesByUser->get($member->id); @endphp
                            <tr class="border-b border-slate-100 last:border-0">
                                <td class="py-3 pr-4 font-medium text-[#0F172A]">{{ $member->name }}</td>
                                <td class="py-3 pr-4 text-slate-500 font-tracking">{{ $member->username }}</td>
                                <td class="py-3 pr-4">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium
                                        {{ $member->isActive() ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $member->isActive() ? 'Activa' : 'Desactivada' }}
                                    </span>
                                </td>
                                <td class="py-3 pr-4 text-slate-600">
                                    @if ($sales)
                                        {{ $sales->guides }} guía(s) · ${{ number_format((float) $sales->total_usd, 2) }}
                                    @else
                                        Sin ventas hoy
                                    @endif
                                </td>
                                <td class="py-3 pr-4 text-right whitespace-nowrap">
                                    <button type="button" wire:click="edit({{ $member->id }})" class="text-blue-900 hover:underline text-xs font-medium mr-3">
                                        Editar
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="toggleActive({{ $member->id }})"
                                        wire:confirm="¿{{ $member->isActive() ? 'Desactivar' : 'Reactivar' }} a {{ $member->name }}?"
                                        class="text-xs font-medium {{ $member->isActive() ? 'text-red-600' : 'text-emerald-700' }} hover:underline"
                                    >
                                        {{ $member->isActive() ? 'Desactivar' : 'Reactivar' }}
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $staff->links() }}
            </div>
        @endif
    </div>
</div>
