<?php

namespace Tests\Feature\Ally;

use App\Livewire\Ally\PackagePickup;
use App\Models\Package;
use App\Services\PackageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    /**
     * Antes deliver() usaba changeStatus() en vez de
     * PackageService::completeAgencyPickup(): un COD retirado en
     * agencia quedaba ENTREGADO pero sin cod_collected_at, así que
     * nunca se podía liquidar y el cliente lo seguía viendo pendiente.
     */
    public function test_picking_up_a_cod_package_records_the_collection_so_it_can_be_liquidated(): void
    {
        $originAlly = $this->createAlly();
        $pickupAlly = $this->createAlly();

        $package = $this->createPackage($originAlly, [
            'requires_delivery' => false,
            'pickup_ally_id' => $pickupAlly->id,
            'current_status' => Package::STATUS_LISTO_RETIRO,
            'recipient_id_doc' => 'V-87654321',
            'is_cod' => true,
            'cod_amount_usd' => 10.00,
            'cod_status' => Package::COD_PENDIENTE,
        ]);

        Livewire::actingAs($pickupAlly->user)
            ->test(PackagePickup::class)
            ->set('trackingNumber', $package->tracking_number)
            ->set('recipientIdDoc', 'V-87654321')
            ->call('deliver')
            ->assertSet('error', null);

        $package->refresh();

        $this->assertSame(Package::STATUS_ENTREGADO, $package->current_status);
        $this->assertNotNull($package->cod_collected_at);
        $this->assertSame($pickupAlly->user->id, (int) $package->cod_collected_by_user_id);
        $this->assertNotNull($package->delivery_completed_at);

        $liquidated = app(PackageService::class)->liquidateCod($package, $originAlly->user->id);

        $this->assertSame(Package::COD_LIQUIDADO, $liquidated->cod_status);
    }

    public function test_an_authorized_third_party_can_pick_up_with_id_copies(): void
    {
        Storage::fake('documents');

        $pickupAlly = $this->createAlly();

        $package = $this->createPackage($this->createAlly(), [
            'requires_delivery' => false,
            'pickup_ally_id' => $pickupAlly->id,
            'current_status' => Package::STATUS_LISTO_RETIRO,
            'recipient_id_doc' => 'V-87654321',
        ]);

        // Sin marcar "tercero", otra cédula no sirve.
        Livewire::actingAs($pickupAlly->user)
            ->test(PackagePickup::class)
            ->set('trackingNumber', $package->tracking_number)
            ->call('search')
            ->set('recipientIdDoc', 'V-11111111')
            ->call('deliver')
            ->assertSet('error', 'El documento del receptor no coincide.')
            ->set('byThirdParty', true)
            ->set('thirdPartyName', 'Pedro Gómez')
            ->set('thirdPartyIdPhoto', UploadedFile::fake()->image('cedula-tercero.jpg'))
            ->set('recipientIdCopy', UploadedFile::fake()->image('copia-cedula.jpg'))
            ->call('deliver')
            ->assertSet('error', null)
            ->assertSet('message', 'Retiro confirmado por un tercero autorizado. El paquete quedó ENTREGADO.');

        $package->refresh();
        $this->assertSame(Package::STATUS_ENTREGADO, $package->current_status);
        $this->assertTrue($package->received_by_third_party);
        $this->assertSame('Pedro Gómez', $package->receiver_name);
        $this->assertSame(Package::DELIVERY_CONFIRMATION_THIRD_PARTY, $package->delivery_confirmation_method);
        Storage::disk('documents')->assertExists($package->recipient_id_copy_path);
        $this->assertStringContainsString(
            'tercero autorizado: Pedro Gómez',
            $package->histories()->latest('id')->first()->location_description
        );
    }
}
