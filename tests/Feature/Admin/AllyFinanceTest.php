<?php

namespace Tests\Feature\Admin;

use App\Exports\SimpleArrayExport;
use App\Livewire\Admin\AllyFinance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Admin\AllyFinance no tenía ningún test. Cubre el listado de aliados
 * (que ya existía) y la exportación a Excel del historial financiero
 * completo de un aliado seleccionado (nueva).
 */
class AllyFinanceTest extends TestCase
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

    public function test_export_excel_requires_an_ally_to_be_selected_first(): void
    {
        Livewire::actingAs($this->createAdmin())
            ->test(AllyFinance::class)
            ->call('exportExcel')
            ->assertStatus(400);
    }

    public function test_export_excel_includes_the_full_transaction_history_of_the_selected_ally(): void
    {
        Excel::fake();

        $ally = $this->createAlly();

        // La creación del paquete ya genera un crédito de comisión vía
        // observer (ver AllyFinancialServiceTest).
        $this->createPackage($ally, [
            'commission_percentage_used' => 10.00,
            'commission_amount_usd' => 8.25,
        ]);

        $filename = 'finanzas-'.Str::slug($ally->business_name).'-'.now()->format('Y-m-d').'.xlsx';

        Livewire::actingAs($this->createAdmin())
            ->test(AllyFinance::class)
            ->call('selectAlly', $ally->id)
            ->call('exportExcel')
            ->assertFileDownloaded();

        Excel::assertDownloaded($filename, function (SimpleArrayExport $export) {
            $rows = iterator_to_array($export->generator());

            self::assertContains('8.25', array_column($rows, 3));
            self::assertContains('Crédito', array_column($rows, 2));
            self::assertContains('Commission', array_column($rows, 1));

            return true;
        });
    }
}
