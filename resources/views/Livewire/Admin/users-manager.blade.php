<div class="min-h-screen">
    <div class="flex flex-col gap-5 mb-8 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="font-display text-3xl font-bold text-[#0F172A]">Gestión de usuarios</h1>
            <p class="mt-1 text-sm text-[#64748B]">Consulta, filtra y administra las cuentas de Venexpress.</p>
        </div>

        <button wire:click="openCreateModal"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800">
            <span class="text-lg leading-none">+</span>
            Crear usuario
        </button>
    </div>

    @if (session()->has('success'))
        <div class="mb-6 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            <div class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100">✓</div>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="mb-6 rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
        <div class="grid gap-4 md:grid-cols-[1fr_220px_180px]">
            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-[#64748B]">Buscar</label>
                <input wire:model.live.debounce.300ms="search" type="text"
                       placeholder="Nombre o correo electrónico..."
                       class="w-full rounded-xl border border-[#E2E8F0] px-4 py-3 text-sm text-[#0F172A] placeholder:text-[#94A3B8] focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-[#64748B]">Tipo de usuario</label>
                <select wire:model.live="roleFilter" class="w-full rounded-xl border border-[#E2E8F0] px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Todos</option>
                    @foreach ($roleLabels as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-[#64748B]">Estado</label>
                <select wire:model.live="statusFilter" class="w-full rounded-xl border border-[#E2E8F0] px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Todos</option>
                    <option value="activo">Activo</option>
                    <option value="inactivo">Inactivo</option>
                </select>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-[#E2E8F0] bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#64748B]">Usuario</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#64748B]">Tipo</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold uppercase text-[#64748B]">Estado</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#64748B]">Registro</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold uppercase text-[#64748B]">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr class="border-b border-[#F1F5F9] last:border-0 hover:bg-slate-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-50 font-bold uppercase text-blue-800">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-[#0F172A]">{{ $user->name }}</p>
                                        <p class="mt-1 text-xs text-[#64748B]">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $roleStyle = match ($user->role) {
                                        'admin_principal' => 'bg-violet-50 text-violet-700',
                                        'admin_operativo' => 'bg-indigo-50 text-indigo-700',
                                        'aliado' => 'bg-blue-50 text-blue-700',
                                        'repartidor' => 'bg-orange-50 text-orange-700',
                                        default => 'bg-slate-100 text-slate-700',
                                    };
                                @endphp
                                <span class="inline-flex rounded-lg px-3 py-1.5 text-xs font-semibold {{ $roleStyle }}">
                                    {{ $roleLabels[$user->role] ?? $user->role }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center gap-2 rounded-lg px-3 py-1.5 text-xs font-semibold {{ $user->isActive() ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $user->isActive() ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                    {{ $user->isActive() ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-[#475569]">
                                {{ optional($user->created_at)->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    @if (auth()->user()->canDeactivateUser($user))
                                        <button wire:click="toggleStatus({{ $user->id }})"
                                                class="rounded-lg border border-[#E2E8F0] px-3 py-2 text-xs font-semibold text-[#475569] hover:bg-slate-50">
                                            {{ $user->isActive() ? 'Desactivar' : 'Activar' }}
                                        </button>
                                    @endif

                                    @if (auth()->user()->canDeleteUser($user))
                                        <button wire:click="requestDelete({{ $user->id }})"
                                                class="rounded-lg border border-red-200 px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-50">
                                            Eliminar
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-14 text-center">
                                <p class="font-semibold text-[#0F172A]">No encontramos usuarios</p>
                                <p class="mt-1 text-sm text-[#64748B]">Prueba con otro nombre, correo o filtro.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="border-t border-[#E2E8F0] px-6 py-4">{{ $users->links() }}</div>
        @endif
    </div>

    {{-- Modal crear usuario --}}
    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4" wire:click.self="closeCreateModal">
            <div class="max-h-[92vh] w-full max-w-3xl overflow-y-auto rounded-2xl bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-[#E2E8F0] px-6 py-5">
                    <div>
                        <h2 class="font-display text-xl font-bold text-[#0F172A]">Crear usuario</h2>
                        <p class="mt-1 text-xs text-[#64748B]">La cuenta quedará activa al completar la creación.</p>
                    </div>
                    <button wire:click="closeCreateModal" class="text-2xl text-[#64748B]">×</button>
                </div>

                <form wire:submit="requestCreate" class="space-y-6 p-6">
                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <x-input-label for="admin-user-name" value="Nombre completo" />
                            <x-text-input wire:model="name" id="admin-user-name" class="mt-1.5 block w-full" type="text" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="admin-user-email" value="Correo electrónico" />
                            <x-text-input wire:model="email" id="admin-user-email" class="mt-1.5 block w-full" type="email" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <x-input-label for="admin-user-password" value="Contraseña inicial" />
                            <x-password-input wire:model="password" id="admin-user-password" class="mt-1.5 block w-full" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="admin-user-password-confirmation" value="Confirmar contraseña" />
                            <x-password-input wire:model="password_confirmation" id="admin-user-password-confirmation" class="mt-1.5 block w-full" />
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="admin-user-role" value="Tipo de usuario" />
                        <select wire:model.live="role" id="admin-user-role" class="mt-1.5 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="aliado">Aliado</option>
                            <option value="repartidor">Repartidor</option>
                            <option value="admin_operativo">Administrador Operativo</option>
                            @if (auth()->user()->isAdminPrincipal())
                                <option value="admin_principal">Administrador Principal</option>
                            @endif
                            <option value="cliente">Cliente</option>
                        </select>
                        <x-input-error :messages="$errors->get('role')" class="mt-2" />
                    </div>

                    @if ($role === 'aliado')
                        <div class="rounded-xl border border-blue-100 bg-blue-50/50 p-5">
                            <h3 class="font-semibold text-blue-950">Información del aliado</h3>
                            <div class="mt-4 grid gap-5 md:grid-cols-2">
                                <div>
                                    <x-input-label for="business-name" value="Nombre comercial" />
                                    <x-text-input wire:model="business_name" id="business-name" class="mt-1.5 block w-full" type="text" />
                                    <x-input-error :messages="$errors->get('business_name')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="rif" value="RIF" />
                                    <x-text-input wire:model="rif" id="rif" class="mt-1.5 block w-full" type="text" />
                                    <x-input-error :messages="$errors->get('rif')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="city" value="Ciudad" />
                                    <x-text-input wire:model="city" id="city" class="mt-1.5 block w-full" type="text" />
                                    <x-input-error :messages="$errors->get('city')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="address" value="Dirección" />
                                    <x-text-input wire:model="address" id="address" class="mt-1.5 block w-full" type="text" />
                                    <x-input-error :messages="$errors->get('address')" class="mt-2" />
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($role === 'repartidor')
                        <div class="rounded-xl border border-orange-100 bg-orange-50/50 p-5">
                            <h3 class="font-semibold text-orange-950">Información del repartidor</h3>
                            <div class="mt-4 grid gap-5 md:grid-cols-2">
                                <div>
                                    <x-input-label for="vehicle-plate" value="Placa" />
                                    <x-text-input wire:model="vehicle_plate" id="vehicle-plate" class="mt-1.5 block w-full" type="text" />
                                    <x-input-error :messages="$errors->get('vehicle_plate')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="vehicle-type" value="Tipo de vehículo" />
                                    <x-text-input wire:model="vehicle_type" id="vehicle-type" class="mt-1.5 block w-full" type="text" placeholder="Moto, automóvil, furgoneta..." />
                                    <x-input-error :messages="$errors->get('vehicle_type')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="phone" value="Teléfono" />
                                    <x-text-input wire:model="phone" id="phone" class="mt-1.5 block w-full" type="text" />
                                    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="flex justify-end gap-3 border-t border-[#E2E8F0] pt-5">
                        <button type="button" wire:click="closeCreateModal" class="rounded-xl border border-[#E2E8F0] px-5 py-3 text-sm font-semibold text-[#475569]">Cancelar</button>
                        <button type="submit" class="rounded-xl bg-blue-900 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-800">Continuar</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal reautenticación creación --}}
    @if ($showConfirmModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-950/60 p-4">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-amber-50 text-amber-700">🔐</div>
                <h2 class="mt-4 font-display text-xl font-bold text-[#0F172A]">Confirmar creación</h2>
                <p class="mt-2 text-sm leading-6 text-[#64748B]">Esta acción creará una cuenta con acceso al sistema. Introduce tu contraseña de administrador para confirmar que eres tú.</p>

                <div class="mt-5">
                    <x-input-label for="admin-password-create" value="Contraseña de administrador" />
                    <x-password-input wire:model="adminPassword" id="admin-password-create" class="mt-1.5 block w-full" autofocus />
                    <x-input-error :messages="$errors->get('adminPassword')" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('showConfirmModal', false)" class="rounded-xl border border-[#E2E8F0] px-5 py-3 text-sm font-semibold text-[#475569]">Volver</button>
                    <button wire:click="createUser" class="rounded-xl bg-blue-900 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-800">Confirmar creación</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal eliminar --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-950/60 p-4">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-50 text-red-600">!</div>
                <h2 class="mt-4 font-display text-xl font-bold text-[#0F172A]">Eliminar usuario</h2>
                <p class="mt-2 text-sm leading-6 text-[#64748B]">Esta acción es permanente. Confirma con tu contraseña de administrador para continuar.</p>

                <div class="mt-5">
                    <x-input-label for="admin-password-delete" value="Contraseña de administrador" />
                    <x-password-input wire:model="adminPassword" id="admin-password-delete" class="mt-1.5 block w-full" autofocus />
                    <x-input-error :messages="$errors->get('adminPassword')" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('showDeleteModal', false)" class="rounded-xl border border-[#E2E8F0] px-5 py-3 text-sm font-semibold text-[#475569]">Cancelar</button>
                    <button wire:click="deleteUser" class="rounded-xl bg-red-600 px-5 py-3 text-sm font-semibold text-white hover:bg-red-700">Eliminar definitivamente</button>
                </div>
            </div>
        </div>
    @endif
</div>
