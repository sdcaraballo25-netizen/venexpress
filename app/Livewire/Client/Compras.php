<?php

namespace App\Livewire\Client;

use App\Models\Pedido;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Pedidos del marketplace hechos por este Cliente mientras tenía
 * sesión iniciada (Pedido::user_id — ver Public\Marketplace). Sin
 * esto, la única forma de volver al chat con el emprendedor era haber
 * guardado el enlace que se mostró una sola vez al confirmar el
 * pedido.
 */
#[Layout('layouts.client')]
#[Title('Mis Compras')]
class Compras extends Component
{
    use WithPagination;

    public function render()
    {
        return view('livewire.client.compras', [
            'pedidos' => Pedido::query()
                ->where('user_id', Auth::id())
                ->with(['producto', 'emprendedor'])
                ->latest()
                ->paginate(10),
        ]);
    }
}
