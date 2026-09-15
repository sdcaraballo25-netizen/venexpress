<?php

namespace Tests\Feature\Driver;

use App\Livewire\Driver\PackageDetail;
use App\Models\Driver;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Portal web, equivalente a DeliveryClaimFromDestinationAgencyTest /
 * PackageServiceCodTest pero para Livewire\Driver\PackageDetail: un
 * repartidor no puede confirmar la entrega de un COD sin indicar
 * antes con qué forma de pago le cancelaron.
 */
class PackageDetailCodPaymentMethodTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestPackages;

    private function createDeliveryDriverUser(): array
    {
        $user = User::factory()->create([
            'role' => User::ROLE_REPARTIDOR,
            'status' => User::STATUS_ACTIVE,
        ]);

        $driver = Driver::factory()->create([
            'user_id' => $user->id,
            'status' => Driver::STATUS_ACTIVE,
            'driver_type' => Driver::TYPE_DELIVERY,
        ]);

        return [$user, $driver];
    }

    private function createCodPackageInTransit($ally, Driver $driver): Package
    {
        return $this->createPackage($ally, [
            'requires_delivery' => true,
            'driver_id' => $driver->id,
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
            'delivery_status' => Package::DELIVERY_ACCEPTED,
            'is_cod' => true,
            'cod_amount_usd' => 15.00,
        ]);
    }

    public function test_shows_payment_method_selector_for_a_pending_cod_package(): void
    {
        [$user, $driver] = $this->createDeliveryDriverUser();
        $ally = $this->createAlly();
        $package = $this->createCodPackageInTransit($ally, $driver);

        Livewire::actingAs($user)
            ->test(PackageDetail::class, ['packageId' => $package->id])
            ->assertSee('Forma de pago del cobro');
    }

    public function test_completing_delivery_without_payment_method_flashes_an_error(): void
    {
        [$user, $driver] = $this->createDeliveryDriverUser();
        $ally = $this->createAlly();
        $package = $this->createCodPackageInTransit($ally, $driver);

        Livewire::actingAs($user)
            ->test(PackageDetail::class, ['packageId' => $package->id])
            ->call('completeDelivery');

        // La excepción se captura dentro del propio método (session
        // flash), así que lo verificable desde aquí es que el
        // paquete se quedó sin entregar y sin cobro registrado.
        $package->refresh();
        $this->assertNotSame(Package::STATUS_ENTREGADO, $package->current_status);
        $this->assertNull($package->cod_collected_at);
    }

    public function test_completing_delivery_with_a_payment_method_succeeds(): void
    {
        [$user, $driver] = $this->createDeliveryDriverUser();
        $ally = $this->createAlly();
        $package = $this->createCodPackageInTransit($ally, $driver);

        Livewire::actingAs($user)
            ->test(PackageDetail::class, ['packageId' => $package->id])
            ->set('codPaymentMethod', 'transferencia')
            ->call('completeDelivery');

        $package->refresh();
        $this->assertSame(Package::STATUS_ENTREGADO, $package->current_status);
        $this->assertSame('transferencia', $package->cod_payment_method);
        $this->assertNotNull($package->cod_collected_at);
    }

    public function test_non_cod_package_does_not_show_the_payment_method_selector(): void
    {
        [$user, $driver] = $this->createDeliveryDriverUser();
        $ally = $this->createAlly();

        $package = $this->createPackage($ally, [
            'requires_delivery' => true,
            'driver_id' => $driver->id,
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
            'delivery_status' => Package::DELIVERY_ACCEPTED,
            'is_cod' => false,
        ]);

        Livewire::actingAs($user)
            ->test(PackageDetail::class, ['packageId' => $package->id])
            ->assertDontSee('Forma de pago del cobro')
            ->call('completeDelivery');

        $package->refresh();
        $this->assertSame(Package::STATUS_ENTREGADO, $package->current_status);
    }
}
