<?php

namespace App\Livewire\Almacen;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.almacen')]
#[Title('Ayuda')]
class HelpCenter extends Component
{
    /**
     * @var array<int, array{question: string, answer: string}>
     */
    public array $faqs = [
        [
            'question' => '¿Cómo registro la llegada de un paquete?',
            'answer' => 'En tu panel, usa "Escanear llegada de paquete" e introduce el número de guía. El sistema valida que el paquete tenga a tu almacén como destino antes de registrarlo.',
        ],
        [
            'question' => '¿Qué significa "Por llegar" y "Recibidas"?',
            'answer' => '"Por llegar" son las paradas de rutas HUB Distribución que todavía no han sido escaneadas en tu almacén. "Recibidas" son las que ya confirmaste.',
        ],
        [
            'question' => '¿Qué hago si un paquete no corresponde a mi almacén?',
            'answer' => 'El sistema rechazará el escaneo si el destino del paquete no coincide con tu almacén. Verifica el número de guía o contacta a un administrador.',
        ],
    ];

    public function render()
    {
        return view('livewire.almacen.help-center');
    }
}
