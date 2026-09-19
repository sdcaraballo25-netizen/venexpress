<?php

namespace Tests\Feature\Admin;

use App\Exports\SimpleArrayExport;
use App\Livewire\Admin\RemunerationsSummary;
use App\Models\Ally;
use App\Models\BcvRate;
use App\Models\Driver;
use App\Models\DriverPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Resumen consolidado de lo que se debe pagar hoy a Aliados
 * (comisión) y Repartidores (remuneración), en una sola tabla — a
 * diferencia de AllyFinance (un aliado a la vez) y DriverPayments
 * (solo repartidores).
 */
class RemunerationsSummaryTest extends TestCase
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

    private function creditAllyCommission(Ally $ally, float $amountUsd): void
    {
        $package = $this->createPackage($ally, [
            'commission_amount_usd' => $amountUsd,
            'commission_percentage_used' => $ally->commission_percentage,
        ]);

        app(\App\Services\AllyFinancialService::class)->recordPackageCommission($package);
    }

    private function createDriverWithPendingPayment(float $amountUsd, float $packageTotalUsd = 10.0): Driver
    {
        $ally = $this->createAlly();
        $driverUser = User::factory()->create(['role' => User::ROLE_REPARTIDOR, 'status' => User::STATUS_ACTIVE]);
        $driver = Driver::factory()->create(['user_id' => $driverUser->id, 'status' => Driver::STATUS_ACTIVE]);
        $package = $this->createPackage($ally, ['total_price_usd' => $packageTotalUsd]);

        DriverPayment::create([
            'driver_id' => $driver->id,
            'package_id' => $package->id,
            'amount_usd' => $amountUsd,
            'status' => DriverPayment::STATUS_PENDING,
        ]);

        return $driver;
    }

    public function test_admin_sees_ally_and_driver_rows_together(): void
    {
        $admin = $this->createAdmin();

        $ally = $this->createAlly(['business_name' => 'Agencia Con Saldo']);
        $this->creditAllyCommission($ally, 25.0);

        $driver = $this->createDriverWithPendingPayment(15.0);

        Livewire::actingAs($admin)
            ->test(RemunerationsSummary::class)
            ->assertSee('Agencia Con Saldo')
            ->assertSee('Aliado')
            ->assertSee($driver->user->name)
            ->assertSee('Repartidor')
            ->assertSee('40.00'); // total a pagar: 25 + 15
    }

    public function test_ally_with_zero_balance_is_excluded(): void
    {
        $admin = $this->createAdmin();

        $this->createAlly(['business_name' => 'Agencia Sin Saldo']);

        Livewire::actingAs($admin)
            ->test(RemunerationsSummary::class)
            ->assertDontSee('Agencia Sin Saldo');
    }

    public function test_driver_with_no_pending_payments_is_excluded(): void
    {
        $admin = $this->createAdmin();

        $driverUser = User::factory()->create(['role' => User::ROLE_REPARTIDOR, 'status' => User::STATUS_ACTIVE, 'name' => 'Repartidor Sin Pagos']);
        Driver::factory()->create(['user_id' => $driverUser->id, 'status' => Driver::STATUS_ACTIVE]);

        Livewire::actingAs($admin)
            ->test(RemunerationsSummary::class)
            ->assertDontSee('Repartidor Sin Pagos');
    }

    public function test_export_excel_includes_bank_details_and_totals(): void
    {
        Excel::fake();

        $admin = $this->createAdmin();

        $ally = $this->createAlly([
            'business_name' => 'Agencia Exportable',
            'bank_account_number' => '0102-1234-56-1234567890',
            'bank_account_holder_id' => 'J-11111111-1',
        ]);
        $this->creditAllyCommission($ally, 25.0);

        BcvRate::create([
            'rate' => 40.0,
            'effective_date' => now()->toDateString(),
            'effective_at' => now(),
            'source' => 'manual',
        ]);

        $filename = 'resumen-pagos-'.now()->format('Y-m-d').'.xlsx';

        Livewire::actingAs($admin)
            ->test(RemunerationsSummary::class)
            ->call('exportExcel')
            ->assertFileDownloaded();

        Excel::assertDownloaded($filename, function (SimpleArrayExport $export) {
            $rows = iterator_to_array($export->generator());

            self::assertSame(
                ['Rol', 'RIF o Cédula', 'Nombre', 'Correo', 'Número de cuenta', 'Cédula del titular', 'Paquetes', 'Producido USD', 'Saldo USD a pagar', 'Saldo Bs a pagar'],
                $export->headings()
            );

            self::assertSame('Aliado', $rows[0][0]);
            self::assertSame('Agencia Exportable', $rows[0][2]);
            self::assertSame('0102-1234-56-1234567890', $rows[0][4]);
            self::assertSame('J-11111111-1', $rows[0][5]);
            self::assertSame('25.00', $rows[0][8]);
            self::assertSame('1000.00', $rows[0][9]); // 25 * 40

            self::assertSame('TOTAL A PAGAR', $rows[count($rows) - 2][0]);
            self::assertSame('TOTAL PRODUCIDO POR ALIADOS (USD)', $rows[count($rows) - 1][0]);

            return true;
        });
    }

    public function test_ally_cannot_access_the_consolidated_summary(): void
    {
        $ally = $this->createAlly();

        $this->actingAs($ally->user)
            ->get(route('admin.remunerations-summary'))
            ->assertForbidden();
    }
}
