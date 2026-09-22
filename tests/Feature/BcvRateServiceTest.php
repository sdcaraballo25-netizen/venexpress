<?php

namespace Tests\Feature;

use App\Models\BcvRate;
use App\Services\BcvRateService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

/**
 * getCurrentRate() es el único punto por el que TariffService obtiene
 * la tasa BCV para cotizar. Antes no verificaba su antigüedad, así
 * que si bcv:sync fallaba en silencio (cron caído, API BCV caída
 * varios días), el sistema seguía cobrando indefinidamente con una
 * tasa vieja sin que nadie se enterara.
 *
 * La antigüedad se cuenta en horas HÁBILES (businessHoursAge()): el
 * BCV no publica sábados, domingos ni feriados bancarios, así que un
 * fin de semana entero no debe contar como "atraso" — la tasa del
 * viernes en la tarde sigue siendo, correctamente, la vigente durante
 * todo el fin de semana. Todos los tests que ejercitan el umbral fijan
 * "now" a un día de la semana conocido (Carbon::setTestNow()) para que
 * el resultado no dependa de qué día real corra la suite.
 */
class BcvRateServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BcvRateService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(BcvRateService::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_get_current_rate_returns_a_fresh_rate(): void
    {
        BcvRate::create([
            'rate' => 40.00,
            'effective_date' => now()->toDateString(),
            'effective_at' => now(),
            'source' => 'manual',
        ]);

        $rate = $this->service->getCurrentRate();

        $this->assertEquals(40.00, (float) $rate->rate);
    }

    public function test_get_current_rate_rejects_a_stale_rate(): void
    {
        config(['services.bcv_api.max_age_hours' => 48]);

        // Miércoles 3pm, tasa del lunes 1pm de la misma semana: 50
        // horas de reloj, sin fin de semana de por medio, así que
        // también son 50 horas hábiles.
        Carbon::setTestNow(Carbon::parse('next Wednesday 15:00:00'));
        $effectiveAt = Carbon::now()->subHours(50);

        BcvRate::create([
            'rate' => 40.00,
            'effective_date' => $effectiveAt->toDateString(),
            'effective_at' => $effectiveAt,
            'source' => 'api',
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('La tasa BCV vigente tiene');

        $this->service->getCurrentRate();
    }

    public function test_get_current_rate_accepts_a_rate_within_the_configured_threshold(): void
    {
        config(['services.bcv_api.max_age_hours' => 48]);

        // Miércoles 3pm, tasa del martes 9am de la misma semana: 30
        // horas, todas hábiles.
        Carbon::setTestNow(Carbon::parse('next Wednesday 15:00:00'));
        $effectiveAt = Carbon::now()->subHours(30);

        BcvRate::create([
            'rate' => 40.00,
            'effective_date' => $effectiveAt->toDateString(),
            'effective_at' => $effectiveAt,
            'source' => 'api',
        ]);

        $rate = $this->service->getCurrentRate();

        $this->assertEquals(40.00, (float) $rate->rate);
    }

    /**
     * El caso concreto que motivó contar solo horas hábiles: la tasa
     * del viernes en la tarde sigue vigente el lunes por la mañana sin
     * bloquear nada, aunque en horas de reloj ya casi se cumplen 3
     * días completos.
     */
    public function test_get_current_rate_does_not_count_the_weekend_as_staleness(): void
    {
        config(['services.bcv_api.max_age_hours' => 48]);

        Carbon::setTestNow(Carbon::parse('next Monday 18:29:00'));
        $friday = Carbon::now()->subDays(3)->setTime(13, 30, 0);

        BcvRate::create([
            'rate' => 40.00,
            'effective_date' => $friday->toDateString(),
            'effective_at' => $friday,
            'source' => 'api',
        ]);

        // ~77 horas de reloj (viernes 1:30pm a lunes 6:29pm), pero
        // bastante menos de 48 horas hábiles: no debe bloquear.
        $rate = $this->service->getCurrentRate();

        $this->assertEquals(40.00, (float) $rate->rate);
    }

    public function test_business_hours_age_excludes_saturday_and_sunday(): void
    {
        Carbon::setTestNow(Carbon::parse('next Monday 09:00:00'));
        $friday = Carbon::now()->subDays(3)->setTime(13, 30, 0);

        $rate = BcvRate::create([
            'rate' => 40.00,
            'effective_date' => $friday->toDateString(),
            'effective_at' => $friday,
            'source' => 'api',
        ]);

        // Viernes 1:30pm -> lunes 9am: 20 horas hábiles (excluye
        // sábado y domingo por completo).
        $this->assertSame(20, $this->service->businessHoursAge($rate));
    }

    public function test_get_current_rate_fails_when_no_rate_exists_at_all(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No hay ninguna tasa BCV registrada todavía.');

        $this->service->getCurrentRate();
    }
}
