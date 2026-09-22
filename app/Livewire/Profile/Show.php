<?php

namespace App\Livewire\Profile;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Página de perfil compartida por los 5 roles del sistema (Admin,
 * Aliado, Repartidor, Cliente, Almacén). Reutiliza los mismos
 * componentes Volt del scaffold de Breeze
 * (profile.update-profile-information-form,
 * profile.update-password-form) para no duplicar esa lógica, pero
 * envuelve todo con el layout branded del rol de quien la ve — antes
 * esta página usaba el layout genérico de Breeze, sin relación visual
 * con el resto del sistema, y ningún panel la enlazaba.
 *
 * A propósito NO incluye el formulario de "eliminar cuenta" del
 * scaffold original: allies.user_id y drivers.user_id tienen
 * cascadeOnDelete(), así que un Aliado/Repartidor borrando su propia
 * cuenta destruiría en cascada su agencia/perfil de repartidor. Esa
 * acción no fue parte de lo pedido (solo editar datos personales), y
 * exponerla aquí sería peligroso para esos roles.
 */
class Show extends Component
{
    protected function resolveLayout(): string
    {
        $user = Auth::user();

        return match (true) {
            $user->isAdmin() => 'layouts.admin',
            $user->isAliadoModule() => 'layouts.ally',
            $user->isRepartidor() => 'layouts.driver',
            $user->isAlmacen() => 'layouts.almacen',
            $user->isEmprendedor() => 'layouts.emprendedor',
            default => 'layouts.client',
        };
    }

    public function render()
    {
        return view('livewire.profile.show')
            ->layout($this->resolveLayout());
    }
}
