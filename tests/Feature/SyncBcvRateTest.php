<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\BcvRateSyncFailed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * bcv:sync corre cada 15 minutos (routes/console.php) para que una
 * falla puntual de la API del BCV se autocorrija rápido. Pero si la
 * API está caída de verdad, nadie se enteraba hasta que
 * BcvRateService::getCurrentRate() empezara a bloquear cotizaciones
 * por tasa vencida — potencialmente días después. Estos tests cubren
 * el aviso por correo a los admins cuando el comando falla, con un
 * throttle para no saturarles la bandeja cada 15 minutos.
 */
class SyncBcvRateTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(string $role = User::ROLE_ADMIN_PRINCIPAL): User
    {
        return User::factory()->create(['role' => $role]);
    }

    public function test_command_notifies_admins_when_the_api_call_fails(): void
    {
        Notification::fake();

        $admin = $this->createAdmin();

        Http::fake([
            config('services.bcv_api.url') => Http::response(null, 500),
        ]);

        $this->artisan('bcv:sync')->assertExitCode(1);

        Notification::assertSentTo($admin, BcvRateSyncFailed::class);
    }

    public function test_command_does_not_notify_admins_twice_within_the_throttle_window(): void
    {
        Notification::fake();

        $admin = $this->createAdmin();

        Http::fake([
            config('services.bcv_api.url') => Http::response(null, 500),
        ]);

        $this->artisan('bcv:sync')->assertExitCode(1);
        $this->artisan('bcv:sync')->assertExitCode(1);

        Notification::assertSentToTimes($admin, BcvRateSyncFailed::class, 1);
    }

    public function test_a_successful_sync_clears_the_throttle_key(): void
    {
        Cache::put('bcv_sync_failure_notified_at', true, now()->addHours(4));

        Http::fake([
            config('services.bcv_api.url') => Http::response([
                'promedio' => 45.50,
                'fechaActualizacion' => now()->toIso8601String(),
                'fuente' => 'BCV',
            ], 200),
        ]);

        $this->artisan('bcv:sync')->assertExitCode(0);

        $this->assertFalse(Cache::has('bcv_sync_failure_notified_at'));
    }

    public function test_command_does_not_notify_anyone_when_the_sync_succeeds(): void
    {
        Notification::fake();

        $this->createAdmin();

        Http::fake([
            config('services.bcv_api.url') => Http::response([
                'promedio' => 45.50,
                'fechaActualizacion' => now()->toIso8601String(),
                'fuente' => 'BCV',
            ], 200),
        ]);

        $this->artisan('bcv:sync')->assertExitCode(0);

        Notification::assertNothingSent();
    }
}
