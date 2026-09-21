<?php

namespace Tests\Browser;

use App\Models\Recommendation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Guarda de regresión: Admin\RecommendationsManager (y muchas otras
 * pantallas de Admin) dependen de Alpine.store('confirm') registrado
 * en confirm-dialog.blade.php. Si Alpine no inicializa ese store, el
 * botón "Archivar" no hace nada visible — ningún test de PHPUnit lo
 * detecta porque esa lógica corre enteramente en el navegador.
 */
class AdminConfirmDialogTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_admin_can_archive_a_recommendation_through_the_confirm_dialog(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN_PRINCIPAL,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
            'password' => bcrypt('password-seguro'),
        ]);

        $recommendation = Recommendation::create([
            'name' => 'Visitante de prueba',
            'message' => 'Sugerencia para probar el diálogo de confirmación.',
            'status' => Recommendation::STATUS_NEW,
        ]);

        $this->browse(function (Browser $browser) use ($admin, $recommendation) {
            $browser->loginAs($admin)
                ->visit('/admin/recomendaciones')
                ->assertSee('Sugerencia para probar el diálogo de confirmación.')
                ->clickAtXPath("(//button[contains(., 'Archivar')])[1]")
                ->waitForText('¿Archivar esta recomendación?', 5)
                ->clickAtXPath("(//button[contains(., 'Archivar')])[last()]")
                ->waitForText('Recomendación archivada.', 5);
        });

        $this->assertSame(
            Recommendation::STATUS_ARCHIVED,
            $recommendation->fresh()->status
        );
    }
}
