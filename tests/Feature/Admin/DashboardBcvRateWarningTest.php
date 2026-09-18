<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Dashboard;
use App\Models\BcvRate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * BcvRateService::getCurrentRate() bloquea cotizaciones a las 72h de
 * antigüedad, y SyncBcvRate avisa por correo si el propio comando
 * falla — pero ninguno de los dos se entera si el cron del scheduler
 * simplemente nunca corrió en el servidor. Este banner en el
 * dashboard de Admin es la única señal que no depende de que algún
 * proceso en segundo plano esté funcionando: basta con que un admin
 * abra la página.
 */
class DashboardBcvRateWarningTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_ADMIN_PRINCIPAL,
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    public function test_dashboard_warns_when_the_bcv_rate_is_stale(): void
    {
        $admin = $this->createAdmin();

        BcvRate::create([
            'rate' => 40.00,
            'effective_date' => now()->subHours(10)->toDateString(),
            'effective_at' => now()->subHours(10),
            'source' => 'api',
        ]);

        Livewire::actingAs($admin)
            ->test(Dashboard::class)
            ->assertSee('revisa la sincronización');
    }

    public function test_dashboard_does_not_warn_when_the_bcv_rate_is_fresh(): void
    {
        $admin = $this->createAdmin();

        BcvRate::create([
            'rate' => 40.00,
            'effective_date' => now()->toDateString(),
            'effective_at' => now(),
            'source' => 'api',
        ]);

        Livewire::actingAs($admin)
            ->test(Dashboard::class)
            ->assertDontSee('revisa la sincronización')
            ->assertDontSee('No hay ninguna tasa BCV registrada');
    }

    public function test_dashboard_warns_when_there_is_no_bcv_rate_at_all(): void
    {
        $admin = $this->createAdmin();

        Livewire::actingAs($admin)
            ->test(Dashboard::class)
            ->assertSee('No hay ninguna tasa BCV registrada');
    }
}
