<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Ayuda')]
class HelpCenter extends Component
{
    /**
     * @var array<int, array{question: string, answer: string}>
     */
    public array $faqs = [
        [
            'question' => '¿Cómo apruebo a un aliado o repartidor nuevo?',
            'answer' => 'Ve a "Aliados" o "Aprobar repartidores" en el menú. Ahí puedes revisar sus datos (y documentos, en el caso de repartidores) y Aprobar, Rechazar o Suspender la cuenta.',
        ],
        [
            'question' => '¿Cómo creo un usuario de almacén?',
            'answer' => 'Primero crea el almacén en "Almacenes". Luego, en "Gestión de usuarios", crea un usuario con tipo "Personal de Almacén" y selecciona el almacén al que pertenece.',
        ],
        [
            'question' => '¿Dónde veo las recomendaciones enviadas por visitantes?',
            'answer' => 'En "Recomendaciones", dentro de la sección Control. Ahí puedes marcarlas como leídas o archivarlas.',
        ],
        [
            'question' => '¿Cómo actualizo la tasa BCV o las tarifas?',
            'answer' => 'Usa "Gestión de tarifas" (aliados) o "BCV" en el menú, según lo que necesites actualizar.',
        ],
        [
            'question' => '¿Dónde queda el registro de todas las acciones administrativas?',
            'answer' => 'En "Bitácora de auditoría" (solo visible para el Administrador Principal), donde se registra cada aprobación, cambio de estado y edición de usuarios.',
        ],
    ];

    public function render()
    {
        return view('livewire.admin.help-center');
    }
}
