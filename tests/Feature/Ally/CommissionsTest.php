<?php

namespace Tests\Feature\Ally;

use App\Exports\SimpleArrayExport;
use App\Livewire\Ally\Commissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Ally\Commissions no tenía ningún test. Cubre que la pantalla
 * renderiza y que el Excel exportado refleja el desglose mensual real.
 */
class CommissionsTest extends TestCase
{
    use CreatesTestPackages;
    use RefreshDatabase;

    public function test_the_screen_renders_with_the_current_month_totals(): void
    {
        $ally = $this->createAlly();

        $this->createPackage($ally, [
            'commission_percentage_used' => 10.00,
            'commission_amount_usd' => 7.50,
        ]);

        Livewire::actingAs($ally->user)
            ->test(Commissions::class)
            ->assertOk()
            ->assertSee('7.50');
    }

    public function test_export_excel_includes_the_monthly_breakdown(): void
    {
        Excel::fake();

        $ally = $this->createAlly();

        $this->createPackage($ally, [
            'tracking_number' => 'VEN-TEST-COMM1',
            'commission_percentage_used' => 10.00,
            'commission_amount_usd' => 12.34,
        ]);

        $filename = 'comisiones-'.now()->format('Y-m-d').'.xlsx';

        Livewire::actingAs($ally->user)
            ->test(Commissions::class)
            ->call('exportExcel')
            ->assertFileDownloaded();

        Excel::assertDownloaded($filename, function (SimpleArrayExport $export) {
            $rows = iterator_to_array($export->generator());

            self::assertSame(['Mes', 'Guías', 'Comisión USD'], $export->headings());
            self::assertContains('12.34', array_column($rows, 2));

            return true;
        });
    }
}
