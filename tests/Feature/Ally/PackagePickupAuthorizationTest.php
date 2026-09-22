<?php

namespace Tests\Feature\Ally;

use App\Livewire\Ally\PackagePickup;
use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * PackagePickup no validaba que la guía consultada estuviera asignada
 * (pickup_ally_id) a la agencia del usuario autenticado: cualquier
 * aliado podía buscar/entregar la guía de otra agencia adivinando o
 * enumerando el número de tracking.
 */
class PackagePickupAuthorizationTest extends TestCase
{
    use CreatesTestPackages;
    use RefreshDatabase;

    public function test_an_ally_cannot_search_a_package_assigned_to_another_agency(): void
    {
        $originAlly = $this->createAlly();
        $realPickupAlly = $this->createAlly();
        $otherAlly = $this->createAlly();

        $package = $this->createPackage($originAlly, [
            'requires_delivery' => false,
            'pickup_ally_id' => $realPickupAlly->id,
            'current_status' => Package::STATUS_LISTO_RETIRO,
        ]);

        Livewire::actingAs($otherAlly->user)
            ->test(PackagePickup::class)
            ->set('trackingNumber', $package->tracking_number)
            ->call('search')
            ->assertSet('package', null)
            ->assertSet('error', 'Guía no encontrada.');
    }

    public function test_an_ally_cannot_deliver_a_package_assigned_to_another_agency(): void
    {
        $originAlly = $this->createAlly();
        $realPickupAlly = $this->createAlly();
        $otherAlly = $this->createAlly();

        $package = $this->createPackage($originAlly, [
            'requires_delivery' => false,
            'pickup_ally_id' => $realPickupAlly->id,
            'current_status' => Package::STATUS_LISTO_RETIRO,
            'recipient_id_doc' => 'V-87654321',
        ]);

        Livewire::actingAs($otherAlly->user)
            ->test(PackagePickup::class)
            ->set('trackingNumber', $package->tracking_number)
            ->set('recipientIdDoc', 'V-87654321')
            ->call('deliver')
            ->assertSet('error', 'Esta guía no está asignada a tu agencia.');

        $this->assertSame(Package::STATUS_LISTO_RETIRO, $package->fresh()->current_status);
    }

    public function test_the_correct_pickup_ally_can_search_and_deliver_the_package(): void
    {
        $originAlly = $this->createAlly();
        $pickupAlly = $this->createAlly();

        $package = $this->createPackage($originAlly, [
            'requires_delivery' => false,
            'pickup_ally_id' => $pickupAlly->id,
            'current_status' => Package::STATUS_LISTO_RETIRO,
            'recipient_id_doc' => 'V-87654321',
        ]);

        Livewire::actingAs($pickupAlly->user)
            ->test(PackagePickup::class)
            ->set('trackingNumber', $package->tracking_number)
            ->call('search')
            ->assertSet('error', null)
            ->set('recipientIdDoc', 'V-87654321')
            ->call('deliver')
            ->assertSet('error', null)
            ->assertSet('message', 'Retiro confirmado. El paquete quedó ENTREGADO.');

        $this->assertSame(Package::STATUS_ENTREGADO, $package->fresh()->current_status);
    }
}
