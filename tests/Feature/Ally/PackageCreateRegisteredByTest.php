<?php

namespace Tests\Feature\Ally;

use App\Livewire\Ally\PackageCreate;
use App\Models\Ally;
use App\Models\BcvRate;
use App\Models\CityDistance;
use App\Models\Package;
use App\Models\RateMatrix;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Cubre dos requisitos de Gestión de Taquillas que dependen de
 * PackageCreate: que cada guía quede atribuida a quién la registró
 * (necesario para el cierre del día por taquilla), y que "Punto de
 * venta" exista como forma de pago.
 */
class PackageCreateRegisteredByTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestPackages;

    protected function setUp(): void
    {
        parent::setUp();

        RateMatrix::create([
            'base_price_usd' => 2.00,
            'price_per_kg_usd' => 0.50,
            'price_per_km_usd' => 0.01,
            'envelope_price_usd' => 1.50,
            'fragile_surcharge_usd' => 3.00,
            'insurance_percentage' => 2.00,
            'delivery_price_usd' => 4.00,
        ]);

        BcvRate::create([
            'rate' => 40.00,
            'effective_date' => now()->toDateString(),
            'effective_at' => now(),
            'source' => 'manual',
        ]);

        CityDistance::setDistance(
            cityOne: 'Caracas',
            stateOne: 'Distrito Capital',
            cityTwo: 'Valencia',
            stateTwo: 'Carabobo',
            distanceKm: 150,
        );
    }

    /**
     * Este fixture predata el campo pickup_mode (Fase "modalidad de
     * destino final"): un pedido sin requires_delivery ahora exige
     * elegir una modalidad de retiro. Se usa PICKUP_MODE_ALLY con un
     * Aliado verificado en el estado destino, que es el escenario
     * normal de retiro en punto aliado — no representa ningún cambio
     * de negocio, solo pone al día un test escrito antes de que
     * existiera ese contrato.
     */
    private function fillRequiredFields($component, string $paymentMethod = 'efectivo_usd')
    {
        $pickupAlly = $this->createAlly([
            'business_name' => 'Punto de Retiro Valencia',
            'city' => 'Valencia',
            'state' => 'Carabobo',
            'is_verified_destination' => true,
        ]);

        return $component
            ->set('sender_doc_number', '12345678')
            ->set('sender_name', 'Juan Pérez')
            ->set('sender_phone', '0414-1234567')
            ->set('sender_email', 'juan.perez@example.com')
            ->set('recipient_doc_number', '87654321')
            ->set('recipient_name', 'María Gómez')
            ->set('recipient_phone', '0424-7654321')
            ->set('recipient_email', 'maria.gomez@example.com')
            ->set('destination_state', 'Carabobo')
            ->set('destination_city', 'Valencia')
            ->set('physical_weight_kg', 2.0)
            ->set('payment_method', $paymentMethod)
            ->set('pickup_mode', Package::PICKUP_MODE_ALLY)
            ->set('pickup_ally_id', $pickupAlly->id);
    }

    public function test_package_created_by_a_taquilla_user_is_attributed_to_them(): void
    {
        $ally = $this->createAlly(['city' => 'Caracas', 'state' => 'Distrito Capital']);
        $taquilla = app(\App\Services\AllyStaffService::class)->create($ally, [
            'name' => 'Taquilla 1',
            'username' => 'taquilla1',
            'password' => 'password-seguro',
        ]);

        $component = $this->fillRequiredFields(Livewire::actingAs($taquilla)->test(PackageCreate::class));
        $component->call('save');
        $component->assertHasNoErrors();

        $package = Package::where('tracking_number', $component->get('createdTrackingNumber'))->firstOrFail();

        $this->assertSame($taquilla->id, $package->registered_by_user_id);
        $this->assertSame($ally->id, $package->ally_id);
    }

    public function test_punto_de_venta_is_accepted_as_a_payment_method(): void
    {
        $ally = $this->createAlly(['city' => 'Caracas', 'state' => 'Distrito Capital']);

        $component = $this->fillRequiredFields(
            Livewire::actingAs($ally->user)->test(PackageCreate::class),
            'punto_venta'
        );
        $component->call('save');
        $component->assertHasNoErrors();

        $package = Package::where('tracking_number', $component->get('createdTrackingNumber'))->firstOrFail();

        $this->assertSame('punto_venta', $package->payment_method);
        $this->assertSame($ally->user->id, $package->registered_by_user_id);
    }
}
