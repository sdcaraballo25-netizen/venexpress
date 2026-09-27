<?php

namespace Tests\Feature\Ally;

use App\Exports\SimpleArrayExport;
use App\Livewire\Ally\Reports;
use App\Models\Incident;
use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Ally\Reports es la versión acotada de Admin\Reports para el propio
 * aliado: mismos KPIs/gráficos/tablas, pero solo con las guías de su
 * agencia. Cubre principalmente el aislamiento entre agencias, que es
 * lo único que este componente le agrega a Admin\Reports.
 */
class ReportsTest extends TestCase
{
    use CreatesTestPackages;
    use RefreshDatabase;

    public function test_the_screen_renders_with_default_period(): void
    {
        $ally = $this->createAlly();

        Livewire::actingAs($ally->user)
            ->test(Reports::class)
            ->assertOk()
            ->assertSee('Reportes');
    }

    public function test_only_counts_packages_from_this_ally(): void
    {
        $ally = $this->createAlly();
        $otherAlly = $this->createAlly();

        $this->createPackage($ally, ['total_price_usd' => 10.00]);
        $this->createPackage($otherAlly, ['total_price_usd' => 999.00]);

        $component = Livewire::actingAs($ally->user)
            ->test(Reports::class)
            ->set('dateRange', '30d');

        $this->assertSame(1, $component->viewData('registeredCount'));
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
            'delivery_completed_at' => now()->subDay(),
        ])->save();

        $outOfRange = $this->createPackage($ally, [
            'current_status' => Package::STATUS_ENTREGADO,
            'total_price_usd' => 999.00,
        ]);
        $outOfRange->forceFill([
            'created_at' => now()->subDays(60),
            'delivery_completed_at' => now()->subDays(59),
        ])->save();

        $component = Livewire::actingAs($ally->user)
            ->test(Reports::class)
            ->set('dateRange', '7d');

        $this->assertSame(1, $component->viewData('registeredCount'));
        $this->assertSame(1, $component->viewData('deliveredCount'));
        $this->assertSame(25.0, (float) $component->viewData('revenueTotal'));
    }

    public function test_incidents_count_only_includes_this_allys_incidents_in_the_period(): void
    {
        $ally = $this->createAlly();
        $otherAlly = $this->createAlly();

        $package = $this->createPackage($ally);
        $otherPackage = $this->createPackage($otherAlly);

        Incident::create([
            'ally_id' => $ally->id,
            'package_id' => $package->id,
            'reported_by_user_id' => $ally->user_id,
            'type' => 'OTRA',
            'description' => 'Incidencia de esta agencia',
            'status' => Incident::STATUS_OPEN,
        ]);

        Incident::create([
            'ally_id' => $otherAlly->id,
            'package_id' => $otherPackage->id,
            'reported_by_user_id' => $otherAlly->user_id,
            'type' => 'OTRA',
            'description' => 'Incidencia de otra agencia',
            'status' => Incident::STATUS_OPEN,
        ]);

        $component = Livewire::actingAs($ally->user)
            ->test(Reports::class)
            ->set('dateRange', '30d');

        $this->assertSame(1, $component->viewData('incidentsCount'));
    }

    public function test_export_excel_only_includes_this_allys_packages(): void
    {
        Excel::fake();

        $ally = $this->createAlly();
        $otherAlly = $this->createAlly();

        $this->createPackage($ally, ['tracking_number' => 'VEN-TEST-OWN1']);
        $this->createPackage($otherAlly, ['tracking_number' => 'VEN-TEST-OTHER1']);

        Livewire::actingAs($ally->user)
            ->test(Reports::class)
            ->call('exportExcel')
            ->assertFileDownloaded();

        $filename = 'reporte-'.Str::slug($ally->business_name).'-'.now()->subDays(29)->format('Y-m-d').'-a-'.now()->format('Y-m-d').'.xlsx';

        Excel::assertDownloaded($filename, function (SimpleArrayExport $export) {
            $trackingNumbers = array_column(iterator_to_array($export->generator()), 0);

            self::assertContains('VEN-TEST-OWN1', $trackingNumbers);
            self::assertNotContains('VEN-TEST-OTHER1', $trackingNumbers);

            return true;
        });
    }
}
