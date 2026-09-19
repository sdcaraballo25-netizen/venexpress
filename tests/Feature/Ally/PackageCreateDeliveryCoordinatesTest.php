<?php

namespace Tests\Feature\Ally;

use App\Livewire\Ally\PackageCreate;
use App\Models\BcvRate;
use App\Models\CityDistance;
use App\Models\Package;
use App\Models\RateMatrix;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Cubre las coordenadas exactas capturadas por el Google Places
 * Autocomplete del campo "Dirección exacta de entrega"
 * (package-create.blade.php) — el JS del navegador no se puede probar
 * aquí, pero sí que el componente Livewire y PackageService guarden
 * (o descarten) delivery_latitude/longitude tal como se espera.
 */
class PackageCreateDeliveryCoordinatesTest extends TestCase
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

    private function fillRequiredFields($component)
    {
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
            ->set('payment_method', 'efectivo_usd');
    }

    public function test_save_stores_exact_coordinates_captured_by_autocomplete(): void
    {
        $ally = $this->createAlly(['city' => 'Caracas', 'state' => 'Distrito Capital']);
        $this->actingAs($ally->user);

        $component = $this->fillRequiredFields(Livewire::test(PackageCreate::class))
            ->set('requires_delivery', true)
            ->set('delivery_address', 'Av. Bolívar Norte, Valencia, Carabobo, Venezuela')
            ->set('delivery_sector', 'Bolívar Norte')
            // Esto es exactamente lo que el JS del autocompletado hace al
            // seleccionar una sugerencia: fija texto + coordenadas juntos.
            ->set('delivery_latitude', 10.1799)
            ->set('delivery_longitude', -68.0219);

        $component->call('save');
        $component->assertHasNoErrors();

        $package = Package::where('tracking_number', $component->get('createdTrackingNumber'))->firstOrFail();

        $this->assertTrue((bool) $package->requires_delivery);
        $this->assertEquals(10.1799, (float) $package->delivery_latitude);
        $this->assertEquals(-68.0219, (float) $package->delivery_longitude);
        $this->assertNotNull($package->delivery_geocoded_at);
    }

    public function test_save_allows_a_manually_typed_address_without_coordinates(): void
    {
        $ally = $this->createAlly(['city' => 'Caracas', 'state' => 'Distrito Capital']);
        $this->actingAs($ally->user);

        $component = $this->fillRequiredFields(Livewire::test(PackageCreate::class))
            ->set('requires_delivery', true)
            ->set('delivery_address', 'Casa sin número, sector tal')
            ->set('delivery_sector', 'sector tal');

        $component->call('save');
        $component->assertHasNoErrors();

        $package = Package::where('tracking_number', $component->get('createdTrackingNumber'))->firstOrFail();

        $this->assertNull($package->delivery_latitude);
        $this->assertNull($package->delivery_geocoded_at);
    }

    public function test_unchecking_requires_delivery_clears_captured_coordinates(): void
    {
        $ally = $this->createAlly(['city' => 'Caracas', 'state' => 'Distrito Capital']);
        $this->actingAs($ally->user);

        Livewire::test(PackageCreate::class)
            ->set('requires_delivery', true)
            ->set('delivery_latitude', 10.1799)
            ->set('delivery_longitude', -68.0219)
            ->set('requires_delivery', false)
            ->assertSet('delivery_latitude', null)
            ->assertSet('delivery_longitude', null);
    }

    public function test_renders_without_error_when_google_maps_api_key_is_configured(): void
    {
        // Solo confirma que la rama del Blade con el x-data del
        // autocompletado compila y renderiza sin errores dentro del
        // propio fragmento del componente. El <script> que carga
        // Google Maps vive en @push('scripts') (fuera del fragmento
        // que Livewire::test() puede inspeccionar, solo se ve en el
        // layout completo) y el widget de Places en sí requiere un
        // navegador real — ninguno de los dos se puede probar aquí.
        config(['services.google_maps.api_key' => 'test-fake-key-123']);

        $ally = $this->createAlly(['city' => 'Caracas', 'state' => 'Distrito Capital']);
        $this->actingAs($ally->user);

        Livewire::test(PackageCreate::class)
            ->set('requires_delivery', true)
            ->assertOk()
            ->assertSee('initAutocomplete', false);
    }

    public function test_full_page_loads_the_google_maps_script_only_when_configured(): void
    {
        $ally = $this->createAlly(['city' => 'Caracas', 'state' => 'Distrito Capital']);
        $this->actingAs($ally->user);

        config(['services.google_maps.api_key' => null]);
        $this->get(route('ally.packages.create'))
            ->assertOk()
            ->assertDontSee('maps.googleapis.com', false);

        config(['services.google_maps.api_key' => 'test-fake-key-123']);
        $this->get(route('ally.packages.create'))
            ->assertOk()
            ->assertSee('maps.googleapis.com/maps/api/js?key=test-fake-key-123', false);
    }

    public function test_rejects_out_of_range_coordinates(): void
    {
        $ally = $this->createAlly(['city' => 'Caracas', 'state' => 'Distrito Capital']);
        $this->actingAs($ally->user);

        $component = $this->fillRequiredFields(Livewire::test(PackageCreate::class))
            ->set('requires_delivery', true)
            ->set('delivery_address', 'Alguna dirección')
            ->set('delivery_sector', 'Algún sector')
            ->set('delivery_latitude', 999);

        $component->call('save');
        $component->assertHasErrors(['delivery_latitude']);
    }
}
