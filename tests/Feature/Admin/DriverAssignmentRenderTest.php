<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\DriverAssignment;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Prueba de humo para Admin\DriverAssignment: el componente y su vista
 * deben renderizar sin errores. Esta pantalla no tenía NINGÚN test que
 * la renderizara — por eso un error de sintaxis Blade (un @endif
 * faltante y un </form> huérfano en driver-assignment.blade.php) llegó
 * a producción sin que nada lo detectara, tumbando la pantalla entera
 * con un 500. Encontrado durante la auditoría de responsive, corregido
 * aparte por ser un bug real y bloqueante, no por pedido explícito.
 */
class DriverAssignmentRenderTest extends TestCase
{
    use CreatesTestPackages;
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_ADMIN_PRINCIPAL,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);
    }

    public function test_the_screen_renders_without_a_package_selected(): void
    {
        Livewire::actingAs($this->createAdmin())
            ->test(DriverAssignment::class)
            ->assertOk()
            ->assertSee('Asignación a reparto');
    }

    /**
     * El bug real estaba en la rama que SÍ pinta un paquete encontrado
     * con driver_id asignado (el botón "Retirar asignación") — por eso
     * este caso es el que de verdad prueba el fix, no solo el smoke
     * test de arriba.
     */
    public function test_the_screen_renders_a_found_package_with_an_assigned_driver(): void
    {
        $ally = $this->createAlly();

        $package = $this->createPackage($ally, [
            'requires_delivery' => true,
            'driver_id' => null,
        ]);

        // Forzamos driver_id directamente: lo único que importa aquí
        // es ejercitar la rama @if($package->driver_id) de la vista,
        // no repetir la lógica de negocio de asignación real.
        $driver = \App\Models\Driver::factory()->create();
        $package->update(['driver_id' => $driver->id]);

        Livewire::actingAs($this->createAdmin())
            ->test(DriverAssignment::class)
            ->set('trackingNumber', $package->tracking_number)
            ->call('search')
            ->assertOk()
            ->assertSee('Retirar asignación');
    }
}
