<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Dashboard;
use App\Models\BcvRate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * BcvRateService::getCurrentRate() bloquea cotizaciones a las 48h
 * hábiles de antigüedad, y SyncBcvRate avisa por correo si el propio
 * comando falla — pero ninguno de los dos se entera si el cron del
 * scheduler simplemente nunca corrió en el servidor. Este banner en
 * el dashboard de Admin es la única señal que no depende de que algún
 * proceso en segundo plano esté funcionando: basta con que un admin
 * abra la página.
 *
 * El umbral (24h hábiles) se prueba fijando "now" a un día de la
 * semana conocido, para que el resultado no dependa de qué día real
 * corra la suite (el BCV no publica fines de semana, así que esas
 * horas no cuentan — ver BcvRateService::businessHoursAge()).
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

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_dashboard_warns_when_the_bcv_rate_is_stale(): void
    {
        $admin = $this->createAdmin();

        // Miércoles 3pm, tasa del martes 9am de la misma semana: 30
        // horas hábiles, por encima del umbral de 24h.
        Carbon::setTestNow(Carbon::parse('next Wednesday 15:00:00'));
        $effectiveAt = Carbon::now()->subHours(30);

        BcvRate::create([
            'rate' => 40.00,
            'effective_date' => $effectiveAt->toDateString(),
            'effective_at' => $effectiveAt,
            'source' => 'api',
        ]);

        Livewire::actingAs($admin)
            ->test(Dashboard::class)
            ->assertSee('revisa la sincronización');
    }

    public function test_dashboard_does_not_warn_when_the_bcv_rate_is_fresh(): void
    {
        $admin = $this->createAdmin();

        // Miércoles 3pm, tasa del mismo miércoles a mediodía: 3
        // horas, por debajo del umbral de 24h.
        Carbon::setTestNow(Carbon::parse('next Wednesday 15:00:00'));
        $effectiveAt = Carbon::now()->subHours(3);

        BcvRate::create([
            'rate' => 40.00,
            'effective_date' => $effectiveAt->toDateString(),
            'effective_at' => $effectiveAt,
            'source' => 'api',
        ]);

        Livewire::actingAs($admin)
            ->test(Dashboard::class)
            ->assertDontSee('revisa la sincronización')
            ->assertDontSee('No hay ninguna tasa BCV registrada');
    }

    /**
     * Mismo caso que motivó contar solo horas hábiles: la tasa del
     * viernes en la tarde sigue vigente el lunes por la mañana sin
     * disparar la alerta, aunque en horas de reloj ya casi se cumplan
     * 3 días completos.
     */
    public function test_dashboard_does_not_warn_across_a_normal_weekend(): void
    {
        $admin = $this->createAdmin();

        Carbon::setTestNow(Carbon::parse('next Monday 09:00:00'));
        $friday = Carbon::now()->subDays(3)->setTime(13, 30, 0);

        BcvRate::create([
            'rate' => 40.00,
            'effective_date' => $friday->toDateString(),
            'effective_at' => $friday,
            'source' => 'api',
        ]);

        Livewire::actingAs($admin)
            ->test(Dashboard::class)
            ->assertDontSee('revisa la sincronización');
    }

    public function test_dashboard_warns_when_there_is_no_bcv_rate_at_all(): void
    {
        $admin = $this->createAdmin();

        Livewire::actingAs($admin)
            ->test(Dashboard::class)
            ->assertSee('No hay ninguna tasa BCV registrada');
    }
}
