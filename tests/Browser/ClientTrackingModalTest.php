<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Guarda de regresión para un bug real: Alpine.js no inicializaba
 * datos declarados en x-data del <html> raíz (ni Alpine.store) en
 * ciertos entornos, dejando el sidebar y el modal de "Rastrear guía"
 * completamente rotos sin que ningún test de PHPUnit lo detectara
 * (esos solo verifican el HTML generado por el servidor, no el
 * JavaScript que corre después en el navegador). Solo un test de
 * navegador real como este lo agarra.
 */
class ClientTrackingModalTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_the_sidebar_and_the_tracking_modal_work(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_CLIENTE,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
            'account_verified_at' => now(),
            'password' => bcrypt('password-seguro'),
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/cliente/dashboard')
                ->assertSee('Mis pedidos')
                ->assertSee('Recomendaciones')

                // El buscador "Rastrear guía" no debe navegar a otra
                // página: abre un modal (Alpine.store('tracking', ...)).
                ->type('guia', 'VEN-DOES-NOT-EXIST')
                ->click('[aria-label="Buscar guía"]')
                ->waitForText('Resultado del rastreo', 5)
                ->assertPathIs('/cliente/dashboard')
                ->assertSee('Resultado del rastreo');
        });
    }
}
