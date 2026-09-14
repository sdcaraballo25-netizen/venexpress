<?php

namespace App\Livewire\Ally;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.ally')]
#[Title('Ayuda')]
class HelpCenter extends Component
{
    /**
     * @var array<int, array{question: string, answer: string}>
     */
    public array $faqs = [
        [
            'question' => '¿Cómo registro un pedido nuevo?',
            'answer' => 'Ve a "Nuevo pedido" en el menú. Ingresa la cédula del remitente y destinatario primero: si ya son clientes conocidos, sus datos se autocompletan.',
        ],
        [
            'question' => '¿Por qué mi cuenta aparece "en revisión"?',
            'answer' => 'Todo aliado nuevo empieza como PENDIENTE hasta que un administrador de Venexpress revisa tus datos (RIF, foto de fachada y ubicación) y aprueba tu cuenta.',
        ],
        [
            'question' => '¿Cómo agrego personal de taquilla?',
            'answer' => 'En "Taquillas" puedes crear cuentas para tu personal. Cada taquilla ve solo lo que ella misma registra en su cierre del día.',
        ],
        [
            'question' => '¿Cómo cobro un pedido contra entrega (COD)?',
            'answer' => 'En "COD" puedes ver los pedidos con cobro contra entrega pendientes de liquidar con Venexpress.',
        ],
        [
            'question' => '¿Dónde reporto un problema con un envío?',
            'answer' => 'En "Incidencias" puedes abrir un reporte sobre cualquier pedido de tu agencia.',
        ],
    ];

    public function render()
    {
        return view('livewire.ally.help-center');
    }
}
