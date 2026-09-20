<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\Auth;

/**
 * Algunas páginas viven en la web pública (calculadora de precio,
 * localizador de agencias) pero también se enlazan desde el menú de
 * un usuario ya logueado (Cliente, Aliado, etc.). Sin esto, un
 * usuario autenticado que las abría salía del layout de su panel
 * (sidebar, "Mi cuenta", cerrar sesión) y caía en el layout público
 * (navbar de marketing) — mismo defecto en cada rol, así que se
 * resuelve una sola vez aquí en vez de repetirlo por componente.
 */
trait ResolvesLayoutForViewer
{
    protected function resolveLayoutForViewer(string $guestLayout = 'layouts.public'): string
    {
        $user = Auth::user();

        if (! $user) {
            return $guestLayout;
        }

        return match (true) {
            $user->isAdmin() => 'layouts.admin',
            $user->isAliadoModule() => 'layouts.ally',
            $user->isRepartidor() => 'layouts.driver',
            $user->isAlmacen() => 'layouts.almacen',
            $user->isCliente() => 'layouts.client',
            default => $guestLayout,
        };
    }
}
