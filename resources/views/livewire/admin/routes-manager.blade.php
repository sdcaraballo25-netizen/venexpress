<div class="space-y-6 font-sans">

        {{-- HEADER --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h1 class="font-display text-3xl font-bold tracking-tight text-[#0F172A]">
                    Gestión de Rutas
                </h1>
                <p class="text-sm text-[#64748B] mt-1">
                    Crea, organiza y administra los recorridos de los repartidores.
                </p>
            </div>

            <button
                wire:click="startCreating"
                class="bg-[#0F172A] hover:bg-slate-800 text-white px-5 py-3 rounded-xl text-sm font-semibold transition">
                + Nueva ruta
            </button>
        </div>

        {{-- MENSAJES --}}
        @if (session('success'))
            <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        {{-- FILTROS --}}
        <div class="bg-white rounded-2xl border border-[#E2E8F0] p-5 shadow-sm mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div>
                    <label class="block text-xs font-bold text-[#64748B] uppercase mb-2">
                        Ciudad
                    </label>

                    <select
                        wire:model.live="filterCity"
                        class="w-full rounded-xl border-[#E2E8F0] text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Todas las ciudades</option>

                        @foreach ($citiesWithAllies as $cityOption)
                            <option value="{{ $cityOption }}">
                                {{ $cityOption }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-[#64748B] uppercase mb-2">
                        Estado
                    </label>

                    <select
    wire:model.live="filterStatus"
    class="w-full rounded-xl border-[#E2E8F0] text-sm focus:border-blue-500 focus:ring-blue-500">
    
    <option value="">Todos los estados</option>
    <option value="draft">Borrador</option>
    <option value="assigned">Asignada</option>
    <option value="in_progress">En curso</option>
    <option value="completed">Completada</option>
    <option value="cancelled">Cancelada</option>

</select>
                </div>

            </div>
        </div>

        {{-- LISTADO --}}
        <div class="space-y-5">

            @forelse ($routes as $route)

                <div
                    wire:key="route-{{ $route->id }}"
                    class="bg-white rounded-2xl border border-[#E2E8F0] shadow-sm overflow-hidden">

                    {{-- CABECERA DE RUTA --}}
                    <div class="p-6 border-b border-[#E2E8F0]">

                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">

                            <div>
                                <div class="flex items-center gap-3">
                                    <h2 class="text-lg font-bold text-[#0F172A]">
                                        {{ $route->name }}
                                    </h2>

                                    @php
    $statusLabels = [
        'draft' => 'Borrador',
        'assigned' => 'Asignada',
        'in_progress' => 'En curso',
        'completed' => 'Completada',
        'cancelled' => 'Cancelada',
    ];
@endphp

<span class="px-3 py-1 rounded-full text-xs font-semibold
    @if($route->status === 'in_progress')
        bg-blue-50 text-blue-700
    @elseif($route->status === 'assigned')
        bg-violet-50 text-violet-700
    @elseif($route->status === 'completed')
        bg-emerald-50 text-emerald-700
    @elseif($route->status === 'cancelled')
        bg-red-50 text-red-700
    @else
        bg-amber-50 text-amber-700
    @endif">
    {{ $statusLabels[$route->status] ?? $route->status }}
</span>
                                </div>

                                <p class="text-sm text-[#64748B] mt-1">
                                    {{ $route->city }}
                                </p>
                            </div>

                            <div class="flex flex-wrap gap-2">

                                <button
                                    wire:click="editRoute({{ $route->id }})"
                                    class="px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm text-[#475569] hover:bg-slate-50">
                                    Editar
                                </button>

                                <button
                                    wire:click="openAssignModal({{ $route->id }})"
                                    class="px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm text-[#475569] hover:bg-slate-50">
                                    Repartidor
                                </button>

                                @if ($route->status === 'assigned')
                                    <button
                                        wire:click="startRoute({{ $route->id }})"
                                        class="px-3 py-2 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700">
                                        Iniciar
                                    </button>
                                @endif

                                @if ($route->status === 'in_progress')
                                    <button
                                        wire:click="completeRoute({{ $route->id }})"
                                        wire:confirm="¿Quieres finalizar esta ruta?"
                                        class="px-3 py-2 rounded-lg bg-emerald-600 text-white text-sm hover:bg-emerald-700">
                                        Finalizar
                                    </button>
                                @endif

                                @if ($route->status !== 'completed' && $route->status !== 'cancelled')
                                    <button
                                        wire:click="cancelRoute({{ $route->id }})"
                                        wire:confirm="¿Quieres cancelar esta ruta?"
                                        class="px-3 py-2 rounded-lg bg-red-50 text-red-700 text-sm hover:bg-red-100">
                                        Cancelar
                                    </button>
                                @endif

                                <button
                                    wire:click="duplicateRoute({{ $route->id }})"
                                    class="px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm text-[#475569] hover:bg-slate-50">
                                    Duplicar
                                </button>

                            </div>
                        </div>

                        {{-- INFORMACIÓN --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-5">

                            <div class="bg-slate-50 rounded-xl p-4">
                                <p class="text-xs text-[#64748B] uppercase font-bold">
                                    Paradas
                                </p>
                                <p class="text-xl font-bold text-[#0F172A] mt-1">
                                    {{ $route->stops->count() }}
                                </p>
                            </div>

                            <div class="bg-slate-50 rounded-xl p-4">
                                <p class="text-xs text-[#64748B] uppercase font-bold">
                                    Repartidor
                                </p>
                                <p class="text-sm font-semibold text-[#0F172A] mt-2">
                                    {{ $route->driver?->user?->name ?? 'Sin asignar' }}
                                </p>
                            </div>

                            <div class="bg-slate-50 rounded-xl p-4">
                                <p class="text-xs text-[#64748B] uppercase font-bold">
                                    Vehículo
                                </p>
                                <p class="text-sm font-semibold text-[#0F172A] mt-2">
                                    {{ $route->driver?->vehicle_plate ?? '—' }}
                                </p>
                            </div>

                        </div>
                    </div>

                    {{-- PARADAS --}}
                    <div class="p-6">

                        <p class="text-xs font-bold text-[#64748B] uppercase tracking-wider mb-4">
                            Recorrido
                        </p>

                        <div class="space-y-3">

                            @foreach ($route->stops as $index => $stop)

                                <div class="flex items-center justify-between border border-[#E2E8F0] rounded-xl p-4">

                                    <div class="flex items-center gap-4">

                                        <div class="w-9 h-9 rounded-full bg-blue-50 text-blue-700 flex items-center justify-center font-bold text-sm">
                                            {{ $index + 1 }}
                                        </div>

                                        <div>
                                            <p class="font-semibold text-sm text-[#0F172A]">
                                                {{ $stop->ally?->business_name ?? 'Agencia' }}
                                            </p>

                                            <p class="text-xs text-[#64748B]">
                                                {{ $stop->ally?->city ?? $route->city }}
                                            </p>
                                        </div>

                                    </div>

                                    @if ($route->status === 'in_progress')
                                        <button
                                            wire:click="openCollectionModal({{ $route->id }}, {{ $stop->id }})"
                                            class="px-3 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-semibold text-slate-700">
                                            Registrar recolección
                                        </button>
                                    @endif

                                </div>

                            @endforeach

                        </div>

                    </div>

                </div>

            @empty

                <div class="bg-white rounded-2xl border border-[#E2E8F0] p-12 text-center">
                    <p class="text-lg font-semibold text-[#0F172A]">
                        No hay rutas registradas
                    </p>

                    <p class="text-sm text-[#64748B] mt-2">
                        Crea tu primera ruta para comenzar.
                    </p>

                    <button
                        wire:click="startCreating"
                        class="mt-5 bg-[#0F172A] text-white px-5 py-3 rounded-xl text-sm font-semibold">
                        Crear primera ruta
                    </button>
                </div>

            @endforelse

        </div>

        <div class="mt-6">
            {{ $routes->links() }}
        </div>

    {{-- =========================================================
         MODAL CREAR / EDITAR RUTA
    ========================================================== --}}
    @if ($showBuilder)

        <div class="fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4">

            <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">

                <div class="p-6 border-b border-[#E2E8F0] flex justify-between items-center">
                    <div>
                        <h2 class="text-xl font-bold text-[#0F172A]">
                            {{ $editingRouteId ? 'Editar ruta' : 'Crear nueva ruta' }}
                        </h2>

                        <p class="text-sm text-[#64748B] mt-1">
                            Define la ciudad y ordena las agencias.
                        </p>
                    </div>

                    <button
                        wire:click="cancelBuilder"
                        class="text-slate-400 hover:text-slate-700 text-xl">
                        ✕
                    </button>
                </div>

                <div class="p-6 space-y-6">

                    {{-- DATOS --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">

    {{-- ESTADO --}}
    <div>
        <label class="block text-xs font-bold text-[#64748B] uppercase mb-2">
            Estado
        </label>

        <select
            wire:model.live="state"
            class="w-full rounded-xl border-[#E2E8F0]">

            <option value="">Seleccionar estado</option>

            @foreach ($states as $stateOption)
                <option value="{{ $stateOption }}">
                    {{ $stateOption }}
                </option>
            @endforeach

        </select>

        @error('state')
            <p class="text-xs text-red-600 mt-1">
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- CIUDAD --}}
    <div>
        <label class="block text-xs font-bold text-[#64748B] uppercase mb-2">
            Ciudad
        </label>

        <select
            wire:model.live="city"
            @disabled($state === '')
            class="w-full rounded-xl border-[#E2E8F0]">

            <option value="">
                {{ $state === ''
                    ? 'Primero selecciona un estado'
                    : 'Seleccionar ciudad' }}
            </option>

            @foreach ($cities as $cityOption)
                <option value="{{ $cityOption }}">
                    {{ $cityOption }}
                </option>
            @endforeach

        </select>

        @error('city')
            <p class="text-xs text-red-600 mt-1">
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- NOMBRE --}}
    <div>
        <label class="block text-xs font-bold text-[#64748B] uppercase mb-2">
            Nombre de la ruta
        </label>

        <input
            type="text"
            wire:model="name"
            placeholder="Ej. Ruta Caracas Centro"
            class="w-full rounded-xl border-[#E2E8F0]">

        @error('name')
            <p class="text-xs text-red-600 mt-1">
                {{ $message }}
            </p>
        @enderror
    </div>

</div>

                    {{-- ORDEN --}}
                    <div>

                        <p class="text-xs font-bold text-[#64748B] uppercase mb-3">
                            Orden del recorrido
                        </p>

                        @if (empty($selectedStops))

                            <div class="rounded-xl bg-slate-50 p-5 text-sm text-[#64748B]">
                                Selecciona las agencias que formarán parte de la ruta.
                            </div>

                        @else

                            <div class="space-y-2">

                                @foreach ($selectedStops as $index => $allyId)

                                    @php
                                        $selectedAlly = $availableAllies->firstWhere('id', $allyId);
                                    @endphp

                                    <div class="flex items-center justify-between rounded-xl border border-[#E2E8F0] p-3">

                                        <div class="flex items-center gap-3">

                                            <span class="w-8 h-8 rounded-full bg-blue-50 text-blue-700 flex items-center justify-center font-bold text-xs">
                                                {{ $index + 1 }}
                                            </span>

                                            <span class="text-sm font-semibold text-[#0F172A]">
                                                {{ $selectedAlly?->business_name ?? 'Agencia #' . $allyId }}
                                            </span>

                                        </div>

                                        <div class="flex gap-1">

                                            <button
                                                type="button"
                                                wire:click="moveStopUp({{ $index }})"
                                                class="px-2 py-1 rounded-lg bg-slate-100 text-xs">
                                                ↑
                                            </button>

                                            <button
                                                type="button"
                                                wire:click="moveStopDown({{ $index }})"
                                                class="px-2 py-1 rounded-lg bg-slate-100 text-xs">
                                                ↓
                                            </button>

                                            <button
                                                type="button"
                                                wire:click="toggleStop({{ $allyId }})"
                                                class="px-2 py-1 rounded-lg bg-red-50 text-red-600 text-xs">
                                                ✕
                                            </button>

                                        </div>

                                    </div>

                                @endforeach

                            </div>

                        @endif

                    </div>

                </div>

                <div class="p-6 border-t border-[#E2E8F0] flex justify-end gap-3">

                    <button
                        wire:click="cancelBuilder"
                        class="px-5 py-2.5 rounded-xl border border-[#E2E8F0] text-sm font-semibold">
                        Cancelar
                    </button>

                    <button
                        wire:click="saveRoute"
                        class="px-5 py-2.5 rounded-xl bg-[#0F172A] text-white text-sm font-semibold">
                        {{ $editingRouteId ? 'Guardar cambios' : 'Crear ruta' }}
                    </button>

                </div>

            </div>
        </div>

    @endif

    {{-- =========================================================
         MODAL ASIGNAR REPARTIDOR
    ========================================================== --}}
    @if ($showAssignModal)

        <div class="fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4">

            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">

                <div class="p-6 border-b border-[#E2E8F0]">
                    <h2 class="text-lg font-bold text-[#0F172A]">
                        Asignar repartidor
                    </h2>
                </div>

                <div class="p-6">

                    <label class="block text-xs font-bold text-[#64748B] uppercase mb-2">
                        Repartidor
                    </label>

                    <select
                        wire:model="selectedDriverId"
                        class="w-full rounded-xl border-[#E2E8F0]">
                        <option value="">Seleccionar repartidor</option>

                        @foreach ($activeDrivers as $driver)
                            <option value="{{ $driver->id }}">
                                {{ $driver->user?->name ?? 'Repartidor' }}
                                — {{ $driver->vehicle_plate }}
                            </option>
                        @endforeach
                    </select>

                    @error('selectedDriverId')
                        <p class="text-xs text-red-600 mt-2">{{ $message }}</p>
                    @enderror

                </div>

                <div class="p-6 border-t border-[#E2E8F0] flex justify-end gap-3">

                    <button
                        wire:click="$set('showAssignModal', false)"
                        class="px-4 py-2 rounded-xl border border-[#E2E8F0] text-sm">
                        Cancelar
                    </button>

                    <button
                        wire:click="assignDriver"
                        class="px-4 py-2 rounded-xl bg-[#0F172A] text-white text-sm font-semibold">
                        Guardar
                    </button>

                </div>

            </div>

        </div>

    @endif

    {{-- =========================================================
         MODAL RECOLECCIÓN
    ========================================================== --}}
    @if ($showCollectionModal)

        <div class="fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4">

            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[85vh] overflow-y-auto">

                <div class="p-6 border-b border-[#E2E8F0]">
                    <h2 class="text-lg font-bold text-[#0F172A]">
                        Registrar recolección
                    </h2>

                    <p class="text-sm text-[#64748B] mt-1">
                        Selecciona los paquetes recolectados en esta agencia.
                    </p>
                </div>

                <div class="p-6">

                    @if ($collectiblePackages->isEmpty())

                        <div class="rounded-xl bg-slate-50 p-5 text-sm text-[#64748B]">
                            No hay paquetes pendientes de recolección.
                        </div>

                    @else

                        <div class="space-y-2">

                            @foreach ($collectiblePackages as $package)

                                <label class="flex items-center gap-3 border border-[#E2E8F0] rounded-xl p-4 cursor-pointer hover:bg-slate-50">

                                    <input
                                        type="checkbox"
                                        value="{{ $package->id }}"
                                        wire:model="collectedPackageIds"
                                        class="rounded border-slate-300">

                                    <div>
                                        <p class="text-sm font-semibold text-[#0F172A]">
                                            Paquete #{{ $package->id }}
                                        </p>
                                    </div>

                                </label>

                            @endforeach

                        </div>

                    @endif

                </div>

                <div class="p-6 border-t border-[#E2E8F0] flex justify-end gap-3">

                    <button
                        wire:click="$set('showCollectionModal', false)"
                        class="px-4 py-2 rounded-xl border border-[#E2E8F0] text-sm">
                        Cancelar
                    </button>

                    <button
                        wire:click="registerCollection"
                        class="px-4 py-2 rounded-xl bg-[#0F172A] text-white text-sm font-semibold">
                        Registrar
                    </button>

                </div>

            </div>

        </div>

    @endif

</div>
