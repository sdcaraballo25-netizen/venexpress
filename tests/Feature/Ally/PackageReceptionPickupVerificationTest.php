<?php

namespace Tests\Feature\Ally;

use App\Livewire\Ally\PackageReception;
use App\Models\Ally;
use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Fase 5A — corrige la brecha de Ally\PackageReception: antes, la
 * autorización se decidía por coincidencia de texto ciudad/estado,
 * sin mirar pickup_ally_id ni is_verified_destination. Ahora solo
 * puede recibir el Ally que sea exactamente el pickup_ally_id del
 * paquete, esté activo y esté verificado como destino.
 */
class PackageReceptionPickupVerificationTest extends TestCase
{
    use CreatesTestPackages;
    use RefreshDatabase;

    private function createVerifiedAlly(array $overrides = []): Ally
    {
        return $this->createAlly(array_merge([
            'is_verified_destination' => true,
            'destination_verification_status' => Ally::DESTINATION_VERIFICATION_APPROVED,
        ], $overrides));
    }

    public function test_the_correct_verified_pickup_ally_can_receive_the_package(): void
    {
        $originAlly = $this->createAlly();
        $pickupAlly = $this->createVerifiedAlly(['city' => 'Valencia', 'state' => 'Carabobo']);

        $package = $this->createPackage($originAlly, [
            'requires_delivery' => false,
            'pickup_ally_id' => $pickupAlly->id,
            'destination_state' => 'Carabobo',
            'destination_city' => 'Valencia',
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
        ]);

        Livewire::actingAs($pickupAlly->user)
            ->test(PackageReception::class)
            ->set('trackingNumber', $package->tracking_number)
            ->call('search')
            ->assertSet('error', null)
            ->call('receive')
            ->assertSet('error', null)
            ->assertSet('message', 'Recepción registrada. El paquete quedó LISTO_RETIRO.');

        $this->assertSame(Package::STATUS_LISTO_RETIRO, $package->fresh()->current_status);
    }

    public function test_an_ally_matching_city_and_state_but_not_the_pickup_ally_cannot_receive(): void
    {
        $originAlly = $this->createAlly();
        $realPickupAlly = $this->createVerifiedAlly(['city' => 'Valencia', 'state' => 'Carabobo']);
        $otherAllySameCity = $this->createVerifiedAlly(['city' => 'Valencia', 'state' => 'Carabobo']);

        $package = $this->createPackage($originAlly, [
            'requires_delivery' => false,
            'pickup_ally_id' => $realPickupAlly->id,
            'destination_state' => 'Carabobo',
            'destination_city' => 'Valencia',
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
        ]);

        Livewire::actingAs($otherAllySameCity->user)
            ->test(PackageReception::class)
            ->set('trackingNumber', $package->tracking_number)
            ->call('search')
            ->assertSet('package', null)
            ->assertSet('error', 'Esta guía no está asignada a tu agencia como punto de retiro.');

        $this->assertSame(Package::STATUS_EN_TRANSITO_NACIONAL, $package->fresh()->current_status);
    }

    public function test_the_pickup_ally_cannot_receive_when_not_verified_as_destination(): void
    {
        $originAlly = $this->createAlly();
        $unverifiedPickupAlly = $this->createAlly([
            'city' => 'Valencia',
            'state' => 'Carabobo',
            'is_verified_destination' => false,
        ]);

        $package = $this->createPackage($originAlly, [
            'requires_delivery' => false,
            'pickup_ally_id' => $unverifiedPickupAlly->id,
            'destination_state' => 'Carabobo',
            'destination_city' => 'Valencia',
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
        ]);

        Livewire::actingAs($unverifiedPickupAlly->user)
            ->test(PackageReception::class)
            ->set('trackingNumber', $package->tracking_number)
            ->call('search')
            ->assertSet('package', null)
            ->assertSet('error', 'Esta guía no está asignada a tu agencia como punto de retiro.');

        $this->assertSame(Package::STATUS_EN_TRANSITO_NACIONAL, $package->fresh()->current_status);
    }

    public function test_the_pickup_ally_cannot_receive_when_inactive(): void
    {
        $originAlly = $this->createAlly();
        $inactivePickupAlly = $this->createVerifiedAlly([
            'city' => 'Valencia',
            'state' => 'Carabobo',
            'status' => Ally::STATUS_SUSPENDED,
        ]);

        $package = $this->createPackage($originAlly, [
            'requires_delivery' => false,
            'pickup_ally_id' => $inactivePickupAlly->id,
            'destination_state' => 'Carabobo',
            'destination_city' => 'Valencia',
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
        ]);

        Livewire::actingAs($inactivePickupAlly->user)
            ->test(PackageReception::class)
            ->set('trackingNumber', $package->tracking_number)
            ->call('search')
            ->assertSet('package', null)
            ->assertSet('error', 'Esta guía no está asignada a tu agencia como punto de retiro.');

        $this->assertSame(Package::STATUS_EN_TRANSITO_NACIONAL, $package->fresh()->current_status);
    }

    public function test_a_package_requiring_delivery_has_no_pickup_ally_id_and_cannot_be_received_as_pickup(): void
    {
        $originAlly = $this->createAlly();
        $verifiedAlly = $this->createVerifiedAlly(['city' => 'Valencia', 'state' => 'Carabobo']);

        $package = $this->createPackage($originAlly, [
            'requires_delivery' => true,
            'pickup_ally_id' => null,
            'destination_state' => 'Carabobo',
            'destination_city' => 'Valencia',
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
        ]);

        Livewire::actingAs($verifiedAlly->user)
            ->test(PackageReception::class)
            ->set('trackingNumber', $package->tracking_number)
            ->call('search')
            ->assertSet('package', null)
            ->assertSet('error', 'Esta guía no está asignada a tu agencia como punto de retiro.');

        $this->assertSame(Package::STATUS_EN_TRANSITO_NACIONAL, $package->fresh()->current_status);
    }
}
