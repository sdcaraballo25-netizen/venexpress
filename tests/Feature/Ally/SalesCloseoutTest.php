<?php

namespace Tests\Feature\Ally;

use App\Exports\SimpleArrayExport;
use App\Livewire\Ally\SalesCloseout;
use App\Models\Package;
use App\Services\AllyStaffService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Cierre del día por forma de pago (efectivo, divisas, punto, etc.),
 * para que el negocio cuadre la caja. El Aliado Administrador ve todo
 * el negocio y puede filtrar por taquilla; una Taquilla solo ve lo
 * que ella misma registró, sin poder elegir otra.
 */
class SalesCloseoutTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestPackages;

    private function createTaquilla($ally, string $username = 'taquilla1')
    {
        return app(AllyStaffService::class)->create($ally, [
            'name' => 'Taquilla 1',
            'username' => $username,
            'password' => 'password-seguro',
        ]);
    }

    public function test_ally_sees_the_combined_total_of_all_taquillas(): void
    {
        $ally = $this->createAlly();
        $taquilla = $this->createTaquilla($ally);

        $this->createPackage($ally, [
            'registered_by_user_id' => $ally->user->id,
            'payment_method' => 'efectivo_usd',
            'total_price_usd' => 10,
        ]);

        $this->createPackage($ally, [
            'registered_by_user_id' => $taquilla->id,
            'payment_method' => 'punto_venta',
            'total_price_usd' => 25,
        ]);

        Livewire::actingAs($ally->user)
            ->test(SalesCloseout::class)
            ->assertSet('registeredBy', 'all')
            ->assertSee('35.00')
            ->assertSee('Punto de venta')
            ->assertSee('Efectivo (USD)');
    }

    public function test_ally_can_filter_the_closeout_by_a_specific_taquilla(): void
    {
        $ally = $this->createAlly();
        $taquilla = $this->createTaquilla($ally);

        $this->createPackage($ally, [
            'registered_by_user_id' => $ally->user->id,
            'payment_method' => 'efectivo_usd',
            'total_price_usd' => 10,
        ]);

        $this->createPackage($ally, [
            'registered_by_user_id' => $taquilla->id,
            'payment_method' => 'punto_venta',
            'total_price_usd' => 25,
        ]);

        Livewire::actingAs($ally->user)
            ->test(SalesCloseout::class)
            ->set('registeredBy', (string) $taquilla->id)
            ->assertSee('25.00')
            ->assertDontSee('35.00');
    }

    public function test_export_excel_matches_the_same_filter_shown_on_screen(): void
    {
        Excel::fake();

        $ally = $this->createAlly();
        $taquilla = $this->createTaquilla($ally);

        $this->createPackage($ally, [
            'registered_by_user_id' => $ally->user->id,
            'payment_method' => 'efectivo_usd',
            'total_price_usd' => 10,
        ]);

        $this->createPackage($ally, [
            'registered_by_user_id' => $taquilla->id,
            'payment_method' => 'punto_venta',
            'total_price_usd' => 25,
        ]);

        $filename = 'cierre-'.now()->format('Y-m-d').'.xlsx';

        Livewire::actingAs($ally->user)
            ->test(SalesCloseout::class)
            ->set('registeredBy', (string) $taquilla->id)
            ->call('exportExcel')
            ->assertFileDownloaded();

        Excel::assertDownloaded($filename, function (SimpleArrayExport $export) {
            $rows = iterator_to_array($export->generator());

            self::assertSame(['Forma de pago', 'Guías', 'Total USD'], $export->headings());
            self::assertCount(1, $rows);
            self::assertSame(['Punto de venta', 1, '25.00'], $rows[0]);

            return true;
        });
    }

    public function test_taquilla_only_sees_their_own_sales_and_cannot_switch_filter(): void
    {
        $ally = $this->createAlly();
        $taquillaA = $this->createTaquilla($ally, 'taquillaa');
        $taquillaB = $this->createTaquilla($ally, 'taquillab');

        $this->createPackage($ally, [
            'registered_by_user_id' => $taquillaA->id,
            'payment_method' => 'efectivo_usd',
            'total_price_usd' => 10,
        ]);

        $this->createPackage($ally, [
            'registered_by_user_id' => $taquillaB->id,
            'payment_method' => 'transferencia',
            'total_price_usd' => 40,
        ]);

        Livewire::actingAs($taquillaA)
            ->test(SalesCloseout::class)
            ->assertSet('registeredBy', (string) $taquillaA->id)
            ->assertSee('10.00')
            ->assertDontSee('40.00')
            // Intento de "hackeo" vía wire:model directo a otra taquilla.
            ->set('registeredBy', (string) $taquillaB->id)
            ->assertSet('registeredBy', (string) $taquillaA->id)
            ->assertDontSee('40.00');
    }

    public function test_cod_packages_are_excluded_from_the_payment_method_breakdown(): void
    {
        $ally = $this->createAlly();

        $this->createPackage($ally, [
            'registered_by_user_id' => $ally->user->id,
            'payment_method' => null,
            'is_cod' => true,
            'cod_amount_usd' => 15,
            'total_price_usd' => 15,
        ]);

        Livewire::actingAs($ally->user)
            ->test(SalesCloseout::class)
            ->assertSee('No hay ventas con forma de pago registrada')
            ->assertSee('1')
            ->assertSee('15.00');
    }

    public function test_taquilla_cannot_access_the_general_dashboard_or_staff_manager(): void
    {
        $ally = $this->createAlly();
        $taquilla = $this->createTaquilla($ally);

        $this->actingAs($taquilla)->get(route('ally.dashboard'))->assertForbidden();
        $this->actingAs($taquilla)->get(route('ally.staff'))->assertForbidden();
        $this->actingAs($taquilla)->get(route('ally.sales-closeout'))->assertOk();
    }
}
