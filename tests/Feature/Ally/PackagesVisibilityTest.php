<?php

namespace Tests\Feature\Ally;

use App\Livewire\Ally\PackageDetail;
use App\Livewire\Ally\Packages;
use App\Services\AllyStaffService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * "La taquilla solo puede visualizar sus paquetes, no los del aliado
 * completo": una Taquilla solo ve/abre las guías que ella misma
 * registró. El Aliado Administrador ve todas, y además quién la
 * registró (columna "Taquilla" / "Registrado por").
 */
class PackagesVisibilityTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestPackages;

    private function createTaquilla($ally, string $username = 'taquilla1')
    {
        return app(AllyStaffService::class)->create($ally, [
            'name' => 'Taquilla Uno',
            'username' => $username,
            'password' => 'password-seguro',
        ]);
    }

    public function test_ally_sees_every_package_and_who_registered_it(): void
    {
        $ally = $this->createAlly();
        $taquilla = $this->createTaquilla($ally);

        $ownPackage = $this->createPackage($ally, ['registered_by_user_id' => $ally->user->id]);
        $taquillaPackage = $this->createPackage($ally, ['registered_by_user_id' => $taquilla->id]);

        Livewire::actingAs($ally->user)
            ->test(Packages::class)
            ->assertSee($ownPackage->tracking_number)
            ->assertSee($taquillaPackage->tracking_number)
            ->assertSee('Taquilla Uno');
    }

    public function test_taquilla_only_sees_packages_they_registered(): void
    {
        $ally = $this->createAlly();
        $taquillaA = $this->createTaquilla($ally, 'taquillaa');
        $taquillaB = $this->createTaquilla($ally, 'taquillab');

        $ownPackage = $this->createPackage($ally, ['registered_by_user_id' => $taquillaA->id]);
        $othersPackage = $this->createPackage($ally, ['registered_by_user_id' => $taquillaB->id]);

        Livewire::actingAs($taquillaA)
            ->test(Packages::class)
            ->assertSee($ownPackage->tracking_number)
            ->assertDontSee($othersPackage->tracking_number)
            // Sin columna "Taquilla" para Taquilla: es obvio que es lo suyo.
            ->assertDontSee('Taquilla Uno');
    }

    public function test_taquilla_cannot_open_a_package_registered_by_someone_else(): void
    {
        $ally = $this->createAlly();
        $taquillaA = $this->createTaquilla($ally, 'taquillaa');
        $taquillaB = $this->createTaquilla($ally, 'taquillab');

        $othersPackage = $this->createPackage($ally, ['registered_by_user_id' => $taquillaB->id]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($taquillaA)->test(PackageDetail::class, ['packageId' => $othersPackage->id]);
    }

    public function test_ally_can_open_any_package_and_sees_who_registered_it(): void
    {
        $ally = $this->createAlly();
        $taquilla = $this->createTaquilla($ally);

        $package = $this->createPackage($ally, ['registered_by_user_id' => $taquilla->id]);

        Livewire::actingAs($ally->user)
            ->test(PackageDetail::class, ['packageId' => $package->id])
            ->assertSee('Taquilla Uno');
    }
}
