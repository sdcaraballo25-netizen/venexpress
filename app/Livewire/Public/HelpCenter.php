<?php

namespace App\Livewire\Public;

use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Centro de ayuda público: preguntas frecuentes. Contenido estático
 * por ahora — sin modelo propio, igual que el resto de secciones
 * informativas de la landing.
 */
#[Layout('layouts.public', ['title' => 'Centro de ayuda — Venexpress'])]
class HelpCenter extends Component
{
    /**
     * @var array<int, array{question: string, answer: string}>
     */
    public array $faqs = [
        [
            'question' => '¿Cómo envío un paquete con Venexpress?',
            'answer' => 'Acércate a cualquiera de nuestras agencias aliadas con tu paquete o sobre, indica el destino y paga el envío. Recibirás un número de guía para hacer seguimiento.',
        ],
        [
            'question' => '¿Cómo rastreo mi envío?',
            'answer' => 'Usa la sección "Rastreo" con tu número de guía para ver el estado actual de tu paquete en tiempo real.',
        ],
        [
            'question' => '¿Cómo me convierto en agencia aliada?',
            'answer' => 'Regístrate como "Punto aliado" desde la pantalla de registro. Tu solicitud será revisada por el equipo de Venexpress antes de activarse.',
        ],
        [
            'question' => '¿Cómo me convierto en repartidor?',
            'answer' => 'Regístrate como "Repartidor" desde la pantalla de registro. Tu cuenta quedará pendiente de aprobación hasta que un administrador la revise.',
        ],
        [
            'question' => '¿Qué hago si mi paquete tiene un problema?',
            'answer' => 'Contacta a la agencia aliada donde lo enviaste o escríbenos a info@venexpress.com indicando tu número de guía.',
        ],
    ];

    public function render()
    {
        return view('public.help-center');
    }
}
