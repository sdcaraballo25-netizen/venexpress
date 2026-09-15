<?php

namespace App\Livewire\Driver;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.driver')]
#[Title('Ayuda')]
class HelpCenter extends Component
{
    /**
     * @var array<int, array{question: string, answer: string}>
     */
    public array $faqs = [
        [
            'question' => '¿Por qué no puedo entrar aunque ya me registré?',
            'answer' => 'Tu cuenta empieza como PENDIENTE hasta que un administrador revisa tus documentos (licencia, cédula y carnet de circulación) y aprueba tu cuenta. Te avisaremos por correo.',
        ],
        [
            'question' => '¿Cómo escaneo un paquete?',
            'answer' => 'Usa "Escanear paquetes" en el menú. Apunta la cámara al código de la guía para registrar la recolección o entrega.',
        ],
        [
            'question' => '¿Qué diferencia hay entre repartidor de reparto y de HUB?',
            'answer' => 'El repartidor de reparto (delivery) entrega paquetes directamente a los destinatarios. El repartidor de HUB traslada paquetes entre agencias y almacenes de Venexpress; tu tipo lo define un administrador.',
        ],
        [
            'question' => '¿Dónde descargo la app para el celular?',
            'answer' => 'En "Descargar app", en el menú lateral. Solo repartidores aprobados pueden acceder a esta descarga.',
        ],
        [
            'question' => '¿Cómo veo mis paquetes pendientes?',
            'answer' => 'En "Mis paquetes" puedes ver todos los envíos asignados a ti y su estado actual.',
        ],
    ];

    public function render()
    {
        return view('livewire.driver.help-center');
    }
}
