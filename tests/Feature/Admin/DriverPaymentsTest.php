<?php

namespace Tests\Feature\Admin;

use App\Exports\SimpleArrayExport;
use App\Livewire\Admin\DriverPayments;
use App\Models\Driver;
use App\Models\DriverPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Admin\DriverPayments muestra un resumen tipo nómina (cuánto se le
 * debe en total a cada repartidor), no una lista plana de pagos
 * individuales. El detalle por guía queda expandible, y "Marcar todo
 * pagado" liquida de una vez todo lo pendiente de un repartidor.
 */
class DriverPaymentsTest extends TestCase
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

    private function createPayment(string $status, float $amount = 1.0, ?Driver $driver = null): DriverPayment
    {
        $driver ??= Driver::factory()->create();
        $package = $this->createPackage($this->createAlly());

        return DriverPayment::create([
            'driver_id' => $driver->id,
            'package_id' => $package->id,
            'amount_usd' => $amount,
            'status' => $status,
        ]);
    }

    public function test_default_view_shows_a_summary_grouped_by_driver(): void
    {
        $driver = Driver::factory()->create();
        $this->createPayment(DriverPayment::STATUS_PENDING, 5.00, $driver);
        $this->createPayment(DriverPayment::STATUS_PENDING, 3.00, $driver);

        $otherDriverPaid = $this->createPayment(DriverPayment::STATUS_PAID, 9.99);

        $component = Livewire::actingAs($this->createAdmin())
            ->test(DriverPayments::class)
            ->assertSee($driver->user->name)
            ->assertSee('2 guías')
            ->assertSee('8.00') // 5.00 + 3.00 sumados, no dos filas sueltas
            ->assertDontSee($otherDriverPaid->driver->user->name);

        $this->assertCount(1, $component->viewData('summary'));
    }

    public function test_mark_all_paid_settles_every_pending_payment_of_that_driver(): void
    {
        $driver = Driver::factory()->create();
        $a = $this->createPayment(DriverPayment::STATUS_PENDING, 5.00, $driver);
        $b = $this->createPayment(DriverPayment::STATUS_PENDING, 3.00, $driver);

        Livewire::actingAs($this->createAdmin())
            ->test(DriverPayments::class)
            ->call('markAllPaidForDriver', $driver->id)
            ->assertSet('status', 'pendiente');

        $a->refresh();
        $b->refresh();

        $this->assertSame(DriverPayment::STATUS_PAID, $a->status);
        $this->assertSame(DriverPayment::STATUS_PAID, $b->status);
        $this->assertNotNull($a->paid_at);
    }

    public function test_mark_all_paid_fails_gracefully_when_nothing_is_pending(): void
    {
        $driver = Driver::factory()->create();
        $this->createPayment(DriverPayment::STATUS_PAID, 5.00, $driver);

        Livewire::actingAs($this->createAdmin())
            ->test(DriverPayments::class)
            ->call('markAllPaidForDriver', $driver->id);

        // No debe romper la página ni marcar nada que ya estaba pagado
        // como pagado "otra vez" — session()->flash('error', ...) es el
        // único efecto esperado.
        $this->assertSame(
            DriverPayment::STATUS_PAID,
            DriverPayment::where('driver_id', $driver->id)->first()->status
        );
    }

    public function test_toggle_driver_shows_and_hides_the_individual_payments(): void
    {
        $driver = Driver::factory()->create();
        $payment = $this->createPayment(DriverPayment::STATUS_PENDING, 5.00, $driver);

        $component = Livewire::actingAs($this->createAdmin())
            ->test(DriverPayments::class)
            ->assertDontSee($payment->package->tracking_number)
            ->call('toggleDriver', $driver->id)
            ->assertSee($payment->package->tracking_number)
            ->call('toggleDriver', $driver->id)
            ->assertDontSee($payment->package->tracking_number);
    }

    public function test_export_excel_reflects_the_same_summary_shown_on_screen(): void
    {
        Excel::fake();

        $driver = Driver::factory()->create();
        $this->createPayment(DriverPayment::STATUS_PENDING, 5.50, $driver);
        $this->createPayment(DriverPayment::STATUS_PENDING, 2.50, $driver);

        $otherDriverPaid = $this->createPayment(DriverPayment::STATUS_PAID, 9.99);

        $filename = 'remuneraciones-'.now()->format('Y-m-d').'.xlsx';

        Livewire::actingAs($this->createAdmin())
            ->test(DriverPayments::class)
            ->call('exportExcel')
            ->assertFileDownloaded();

        Excel::assertDownloaded($filename, function (SimpleArrayExport $export) use ($driver, $otherDriverPaid) {
            $rows = iterator_to_array($export->generator());
            $names = array_column($rows, 0);

            self::assertSame(['Repartidor', 'Guías', 'Total USD'], $export->headings());
            self::assertContains($driver->user->name, $names);
            self::assertNotContains($otherDriverPaid->driver->user->name, $names);

            $driverRow = $rows[array_search($driver->user->name, $names)];
            self::assertSame(2, $driverRow[1]);
            self::assertSame('8.00', $driverRow[2]);

            return true;
        });
    }

    public function test_export_excel_respects_the_all_status_filter(): void
    {
        Excel::fake();

        $pendingDriver = Driver::factory()->create();
        $this->createPayment(DriverPayment::STATUS_PENDING, 5.00, $pendingDriver);

        $paidDriver = Driver::factory()->create();
        $this->createPayment(DriverPayment::STATUS_PAID, 9.99, $paidDriver);

        $filename = 'remuneraciones-'.now()->format('Y-m-d').'.xlsx';

        Livewire::actingAs($this->createAdmin())
            ->test(DriverPayments::class)
            ->set('status', 'all')
            ->call('exportExcel');

        Excel::assertDownloaded($filename, function (SimpleArrayExport $export) use ($pendingDriver, $paidDriver) {
            $names = array_column(iterator_to_array($export->generator()), 0);

            self::assertContains($pendingDriver->user->name, $names);
            self::assertContains($paidDriver->user->name, $names);

            return true;
        });
    }
}
