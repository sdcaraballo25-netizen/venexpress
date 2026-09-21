<?php

namespace Tests\Feature\Admin;

use App\Exports\SimpleArrayExport;
use App\Livewire\Admin\Reports;
use App\Models\Driver;
use App\Models\Incident;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Admin\Reports es el dashboard de tendencias por período (a
 * diferencia de Admin\Dashboard, que muestra solo el estado actual,
 * sin filtro de fecha). Cubre: que las guías fuera del rango elegido
 * no contaminen los KPIs/tablas, y que el export a Excel respete el
 * mismo rango.
 */
class ReportsTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestPackages;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_ADMIN_PRINCIPAL,
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    public function test_the_screen_renders_with_default_period(): void
    {
        Livewire::actingAs($this->createAdmin())
            ->test(Reports::class)
            ->assertOk()
            ->assertSee('Reportes');
    }

    public function test_registered_and_delivered_counts_only_include_packages_inside_the_chosen_period(): void
    {
        $ally = $this->createAlly();

        $inRange = $this->createPackage($ally, [
            'current_status' => Package::STATUS_ENTREGADO,
            'total_price_usd' => 25.00,
        ]);
        $inRange->forceFill([
            'created_at' => now()->subDays(2),
            'delivery_completed_at' => now()->subDays(1),
        ])->save();

        $outOfRange = $this->createPackage($ally, [
            'current_status' => Package::STATUS_ENTREGADO,
            'total_price_usd' => 999.00,
        ]);
        $outOfRange->forceFill([
            'created_at' => now()->subDays(60),
            'delivery_completed_at' => now()->subDays(59),
        ])->save();

        $component = Livewire::actingAs($this->createAdmin())
            ->test(Reports::class)
            ->set('dateRange', '7d');

        $this->assertSame(1, $component->viewData('registeredCount'));
        $this->assertSame(1, $component->viewData('deliveredCount'));
        $this->assertSame(25.0, (float) $component->viewData('revenueTotal'));
    }

    public function test_custom_date_range_filters_by_the_chosen_dates(): void
    {
        $ally = $this->createAlly();

        $inRange = $this->createPackage($ally);
        $inRange->forceFill(['created_at' => now()->subDays(10)])->save();

        $outOfRange = $this->createPackage($ally);
        $outOfRange->forceFill(['created_at' => now()->subDays(60)])->save();

        $component = Livewire::actingAs($this->createAdmin())
            ->test(Reports::class)
            ->set('dateRange', 'custom')
            ->set('customFrom', now()->subDays(15)->toDateString())
            ->set('customTo', now()->subDays(5)->toDateString());

        $this->assertSame(1, $component->viewData('registeredCount'));
    }

    public function test_top_allies_table_only_counts_packages_from_the_period(): void
    {
        $topAlly = $this->createAlly(['business_name' => 'Agencia Top']);
        $otherAlly = $this->createAlly(['business_name' => 'Agencia Fuera de Rango']);

        $this->createPackage($topAlly);

        $oldPackage = $this->createPackage($otherAlly);
        $oldPackage->forceFill(['created_at' => now()->subDays(60)])->save();

        Livewire::actingAs($this->createAdmin())
            ->test(Reports::class)
            ->set('dateRange', '7d')
            ->assertSee('Agencia Top')
            ->assertDontSee('Agencia Fuera de Rango');
    }

    public function test_top_drivers_table_only_counts_deliveries_completed_in_the_period(): void
    {
        $ally = $this->createAlly();

        $activeDriver = Driver::factory()->create();
        $idleDriver = Driver::factory()->create();

        $delivered = $this->createPackage($ally, [
            'driver_id' => $activeDriver->id,
            'current_status' => Package::STATUS_ENTREGADO,
        ]);
        $delivered->forceFill(['delivery_completed_at' => now()->subDay()])->save();

        // Este repartidor no tiene ninguna entrega COMPLETADA en el
        // período: no debe aparecer en el top aunque tenga guías
        // asignadas.
        $this->createPackage($ally, [
            'driver_id' => $idleDriver->id,
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
        ]);

        Livewire::actingAs($this->createAdmin())
            ->test(Reports::class)
            ->set('dateRange', '7d')
            ->assertSee($activeDriver->user->name)
            ->assertDontSee($idleDriver->user->name);
    }

    public function test_incidents_count_respects_the_period(): void
    {
        $ally = $this->createAlly();
        $package = $this->createPackage($ally);

        $recent = Incident::create([
            'ally_id' => $ally->id,
            'package_id' => $package->id,
            'reported_by_user_id' => $this->createAdmin()->id,
            'type' => 'OTRA',
            'description' => 'Incidencia reciente',
            'status' => Incident::STATUS_OPEN,
        ]);

        $old = Incident::create([
            'ally_id' => $ally->id,
            'package_id' => $package->id,
            'reported_by_user_id' => $this->createAdmin()->id,
            'type' => 'OTRA',
            'description' => 'Incidencia vieja',
            'status' => Incident::STATUS_OPEN,
        ]);
        $old->forceFill(['created_at' => now()->subDays(60)])->save();

        $component = Livewire::actingAs($this->createAdmin())
            ->test(Reports::class)
            ->set('dateRange', '7d');

        $this->assertSame(1, $component->viewData('incidentsCount'));
    }

    public function test_export_excel_respects_the_chosen_period(): void
    {
        Excel::fake();

        $ally = $this->createAlly();

        $inRange = $this->createPackage($ally, ['tracking_number' => 'VEN-INCLUIDO']);
        $inRange->forceFill(['created_at' => now()->subDays(2)])->save();

        $outOfRange = $this->createPackage($ally, ['tracking_number' => 'VEN-EXCLUIDO']);
        $outOfRange->forceFill(['created_at' => now()->subDays(60)])->save();

        Livewire::actingAs($this->createAdmin())
            ->test(Reports::class)
            ->set('dateRange', '7d')
            ->call('exportExcel')
            ->assertFileDownloaded();

        Excel::assertDownloaded(
            'reporte-paquetes-'.now()->subDays(6)->format('Y-m-d').'-a-'.now()->format('Y-m-d').'.xlsx',
            function (SimpleArrayExport $export) {
                $trackingNumbers = array_column(iterator_to_array($export->generator()), 0);

                self::assertContains('VEN-INCLUIDO', $trackingNumbers);
                self::assertNotContains('VEN-EXCLUIDO', $trackingNumbers);

                return true;
            }
        );
    }
}
