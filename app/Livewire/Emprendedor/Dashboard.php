<?php

namespace App\Livewire\Emprendedor;

use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.emprendedor')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public function render()
    {
        $emprendedor = Auth::user()->emprendedor;

        $productosActivosCount = Producto::query()
            ->where('emprendedor_id', $emprendedor->id)
            ->where('activo', true)
            ->count();

        $pedidosPendientesCount = Pedido::query()
            ->where('emprendedor_id', $emprendedor->id)
            ->where('status', Pedido::STATUS_PENDIENTE)
            ->count();

        $pedidosConfirmadosCount = Pedido::query()
            ->where('emprendedor_id', $emprendedor->id)
            ->where('status', Pedido::STATUS_CONFIRMADO)
            ->count();

        $pedidosRecientes = Pedido::query()
            ->where('emprendedor_id', $emprendedor->id)
            ->with('items.producto')
            ->latest()
            ->limit(5)
            ->get();

        return view('livewire.emprendedor.dashboard', [
            'emprendedor' => $emprendedor,
            'productosActivosCount' => $productosActivosCount,
            'pedidosPendientesCount' => $pedidosPendientesCount,
            'pedidosConfirmadosCount' => $pedidosConfirmadosCount,
            'pedidosRecientes' => $pedidosRecientes,
        ]);
    }
}
