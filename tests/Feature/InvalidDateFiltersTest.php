<?php

namespace Tests\Feature;

use App\Livewire\Admin\Reports as AdminReports;
use App\Livewire\Ally\Reports as AllyReports;
use App\Livewire\Ally\SalesCloseout;
use App\Livewire\Client\Dashboard as ClientDashboard;
use App\Models\User;
use App\Support\DateInput;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Una fecha manipulada en un filtro (wire:model) provocaba un error 500
 * (Carbon::parse o, en PostgreSQL, la consulta). Ahora se ignora.
 */
class InvalidDateFiltersTest extends TestCase
{
    use CreatesTestPackages;
    use RefreshDatabase;

    public function test_date_input_only_accepts_real_calendar_dates(): void
    {
        $this->assertSame('2026-11-05', DateInput::clean('2026-11-05'));
        $this->assertSame('', DateInput::clean('abc'));
        $this->assertSame('', DateInput::clean('2026-13-45'));
        $this->assertSame('', DateInput::clean('2026-02-30'));
        $this->assertSame('', DateInput::clean("2026-11-05' OR 1=1"));
        $this->assertNull(DateInput::parse(''));
    }

    public function test_reports_and_closeout_ignore_invalid_dates_instead_of_crashing(): void
    {
        $ally = $this->createAlly();
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN_PRINCIPAL, 'status' => User::STATUS_ACTIVE]);

        Livewire::actingAs($ally->user)->test(AllyReports::class)
            ->set('dateRange', 'custom')->set('customFrom', 'abc')->set('customTo', '2026-99-99')
            ->assertOk();

        Livewire::actingAs($admin)->test(AdminReports::class)
            ->set('dateRange', 'custom')->set('customFrom', 'abc')
            ->assertOk();

        Livewire::actingAs($ally->user)->test(SalesCloseout::class)
            ->set('date', 'no-es-fecha')
            ->assertOk();
    }

    public function test_client_history_ignores_invalid_dates(): void
    {
        $client = User::factory()->create(['role' => User::ROLE_CLIENTE, 'status' => User::STATUS_ACTIVE, 'account_verified_at' => now()]);

        Livewire::actingAs($client)->test(ClientDashboard::class)
            ->set('activeTab', 'history')
            ->set('historyFrom', 'abc')
            ->set('historyTo', '2026-02-30')
            ->assertOk();
    }
}
