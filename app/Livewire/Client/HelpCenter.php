<?php

namespace App\Livewire\Client;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.client')]
#[Title('Ayuda')]
class HelpCenter extends Component
{
    /**
     * @var array<int, array{question: string, answer: string}>
     */
    public array $faqs = [
        [
            'question' => '¿Cómo rastreo mi paquete?',
            'answer' => 'Usa el buscador "Rastrear guía" en la barra lateral, o revisa la pestaña "Pendientes" de "Mis pedidos" para ver el estado de todos tus envíos activos.',
        ],
        [
            'question' => '¿Qué diferencia hay entre "Pendientes", "Retiros" e "Historial"?',
            'answer' => '"Pendientes" muestra todo lo que aún no llega a su destino final. "Retiros" son los paquetes que ya están en la agencia esperando que pases a buscarlos. "Historial" son tus entregas ya completadas.',
        ],
        [
            'question' => '¿Cómo recibo una entrega a domicilio?',
            'answer' => 'Cuando tu paquete salga a reparto te enviaremos por correo un PIN de entrega. Dáselo al repartidor solo cuando tengas el paquete en tus manos. Si no tienes el PIN a mano, también puedes recibirlo mostrando tu cédula.',
        ],
        [
            'question' => '¿Cómo pago un pedido contra entrega (COD)?',
            'answer' => 'En "Pagos" puedes ver los pedidos con cobro contra entrega que todavía no has cancelado.',
        ],
        [
            'question' => '¿Dónde reporto un problema con un envío?',
            'answer' => 'En "Incidencias" puedes abrir un reporte sobre cualquier pedido asociado a tu cuenta.',
        ],
    ];

    public function render()
    {
        return view('livewire.client.help-center');
    }
}
