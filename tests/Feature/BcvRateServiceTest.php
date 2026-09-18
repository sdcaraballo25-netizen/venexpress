<?php

namespace Tests\Feature;

use App\Models\BcvRate;
use App\Services\BcvRateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

/**
 * getCurrentRate() es el único punto por el que TariffService obtiene
 * la tasa BCV para cotizar. Antes no verificaba su antigüedad, así
 * que si bcv:sync fallaba en silencio (cron caído, API BCV caída
 * varios días), el sistema seguía cobrando indefinidamente con una
 * tasa vieja sin que nadie se enterara.
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
        config(['services.bcv_api.max_age_hours' => 72]);

        BcvRate::create([
            'rate' => 40.00,
            'effective_date' => now()->subHours(80)->toDateString(),
            'effective_at' => now()->subHours(80),
            'source' => 'api',
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('La tasa BCV vigente tiene');

        $this->service->getCurrentRate();
    }

    public function test_get_current_rate_accepts_a_rate_within_the_configured_threshold(): void
    {
        config(['services.bcv_api.max_age_hours' => 72]);

        BcvRate::create([
            'rate' => 40.00,
            'effective_date' => now()->subHours(60)->toDateString(),
            'effective_at' => now()->subHours(60),
            'source' => 'api',
        ]);

        $rate = $this->service->getCurrentRate();

        $this->assertEquals(40.00, (float) $rate->rate);
    }

    public function test_get_current_rate_fails_when_no_rate_exists_at_all(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No hay ninguna tasa BCV registrada todavía.');

        $this->service->getCurrentRate();
    }
}
