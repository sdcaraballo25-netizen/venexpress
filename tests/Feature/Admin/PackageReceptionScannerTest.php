<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\PackageReception;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Lector QR de la recepción de Admin: el QR impreso contiene el
 * tracking_number (PackageLabelController), así que scanGuide() solo
 * rellena el campo y reutiliza search(). No recibe el paquete — eso
 * sigue exigiendo elegir almacén y confirmar (ver
 * PackageReceptionHubTest).
 */
class PackageReceptionScannerTest extends TestCase
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

    public function test_scanning_a_guide_locates_the_package(): void
    {
        $package = $this->createPackage($this->createAlly(), [
            'current_status' => Package::STATUS_RECOLECTADO_VENEXPRESS,
        ]);

        Livewire::actingAs($this->createAdmin())
            ->test(PackageReception::class)
            ->call('scanGuide', ' '.$package->tracking_number.' ')
            ->assertSet('trackingNumber', $package->tracking_number)
            ->assertSet('errorMessage', null)
            ->assertSee($package->tracking_number);
    }

    public function test_scanning_an_unknown_guide_shows_an_error(): void
    {
        Livewire::actingAs($this->createAdmin())
            ->test(PackageReception::class)
            ->call('scanGuide', 'VEN-NO-EXISTE')
            ->assertSet('package', null)
            ->assertSee('No existe una guía con número: VEN-NO-EXISTE');
    }

    public function test_scanning_does_not_receive_the_package(): void
    {
        $package = $this->createPackage($this->createAlly(), [
            'current_status' => Package::STATUS_RECOLECTADO_VENEXPRESS,
        ]);

        Livewire::actingAs($this->createAdmin())
            ->test(PackageReception::class)
            ->call('scanGuide', $package->tracking_number)
            ->assertSet('successMessage', null);

        $this->assertSame(
            Package::STATUS_RECOLECTADO_VENEXPRESS,
            $package->fresh()->current_status
        );
    }

    public function test_empty_scan_is_ignored(): void
    {
        Livewire::actingAs($this->createAdmin())
            ->test(PackageReception::class)
            ->call('scanGuide', '   ')
            ->assertSet('trackingNumber', '')
            ->assertSet('errorMessage', null);
    }
}
