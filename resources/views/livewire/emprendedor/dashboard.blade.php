<div class="space-y-8 font-sans">

    <div>
        <h1 class="font-display text-3xl font-bold tracking-tight text-[#111111]">
            ¡Hola, {{ $emprendedor->business_name }}! 👋
        </h1>
        <p class="text-sm text-[#6B6B66] mt-1">
            {{ now()->translatedFormat('l, d \d\e F \d\e Y') }}
        </p>
    </div>

    @if ($emprendedor->status !== \App\Models\Emprendedor::STATUS_ACTIVE)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-800">
            @if ($emprendedor->status === \App\Models\Emprendedor::STATUS_PENDING)
                Tu cuenta está pendiente de aprobación por el equipo de Venexpress. Podrás publicar productos y recibir pedidos una vez que sea aprobada.
            @elseif ($emprendedor->status === \App\Models\Emprendedor::STATUS_SUSPENDED)
                Tu cuenta está suspendida. Contacta a soporte para más información.
            @else
                Tu solicitud no fue aprobada. Contacta a soporte si crees que esto es un error.
            @endif
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">

        <div class="bg-white rounded-2xl border border-[#E5E5E0] p-6 shadow-sm">
            <p class="text-xs font-bold text-[#6B6B66] uppercase tracking-wider">Productos activos</p>
            <p class="mt-2 text-2xl font-bold text-[#111111]">{{ $productosActivosCount }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-[#E5E5E0] p-6 shadow-sm">
            <p class="text-xs font-bold text-[#6B6B66] uppercase tracking-wider">Pedidos por confirmar</p>
            <p class="mt-2 text-2xl font-bold text-amber-600">{{ $pedidosPendientesCount }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-[#E5E5E0] p-6 shadow-sm">
            <p class="text-xs font-bold text-[#6B6B66] uppercase tracking-wider">Pedidos confirmados</p>
            <p class="mt-2 text-2xl font-bold text-emerald-600">{{ $pedidosConfirmadosCount }}</p>
        </div>

    </div>

    <div class="bg-white rounded-2xl border border-[#E5E5E0] shadow-sm overflow-hidden">
        <div class="p-5 border-b border-[#E5E5E0] flex items-center justify-between">
            <p class="text-xs font-bold text-[#6B6B66] uppercase tracking-wider">Pedidos recientes</p>
            <a href="{{ route('emprendedor.pedidos') }}" wire:navigate class="text-xs font-semibold text-blue-600 hover:text-blue-800">
                Ver todos
            </a>
        </div>

        <div class="divide-y divide-[#E5E5E0]">
            @forelse ($pedidosRecientes as $pedido)
                <div class="flex items-center justify-between px-5 py-3">
                    <div>
                        <p class="text-sm font-medium text-[#111111]">{{ $pedido->producto?->nombre }}</p>
                        <p class="text-xs text-[#6B6B66]">{{ $pedido->cliente_nombre }} · {{ $pedido->cantidad }} unidad(es)</p>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-lg
                        {{ $pedido->status === \App\Models\Pedido::STATUS_CONFIRMADO ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                        {{ $pedido->status }}
                    </span>
                </div>
            @empty
                <p class="px-5 py-6 text-sm text-[#6B6B66]">Todavía no tienes pedidos.</p>
            @endforelse
        </div>
    </div>

</div>
