<?php

namespace Tests\Feature;

use App\Models\Recommendation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Livewire solo vuelve a aplicar en cada acción (POST /livewire/update)
 * los middleware persistentes. Sin registrar 'role'/'account.approved'
 * como persistentes (AppServiceProvider), un usuario desactivado con
 * una pestaña ya abierta seguía ejecutando acciones.
 */
class LivewirePersistentMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private function snapshotFrom(string $html): string
    {
        $this->assertSame(1, preg_match('/wire:snapshot="([^"]+)"/', $html, $matches));

        return htmlspecialchars_decode($matches[1], ENT_QUOTES);
    }

    private function callAction(string $snapshot, string $method, array $params = [])
    {
        return $this->withHeaders(['X-Livewire' => 'true'])->postJson('/livewire/update', [
            'components' => [[
                'snapshot' => $snapshot,
                'updates' => [],
                'calls' => [['path' => '', 'method' => $method, 'params' => $params]],
            ]],
        ]);
    }

    public function test_a_deactivated_admin_cannot_keep_running_actions_from_an_open_tab(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN_PRINCIPAL,
            'status' => User::STATUS_ACTIVE,
        ]);

        $recommendation = Recommendation::create([
            'name' => 'Visitante',
            'email' => 'visitante@example.com',
            'message' => 'Sugerencia de prueba para la bitácora.',
        ]);

        $html = $this->actingAs($admin)->get(route('admin.recommendations'))->assertOk()->getContent();
        $snapshot = $this->snapshotFrom($html);

        // Mientras está activo, la acción funciona.
        $this->callAction($snapshot, 'markRead', [$recommendation->id])->assertOk();

        $admin->update(['status' => User::STATUS_INACTIVE]);

        $this->callAction($snapshot, 'archive', [$recommendation->id])->assertForbidden();
    }
}
