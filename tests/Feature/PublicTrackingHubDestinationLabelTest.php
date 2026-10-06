<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\PackageHistory;
use App\Models\Warehouse;
use App\Models\WarehouseCoverage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Rastreo público — etiqueta de EN_HUB según si el paquete ya llegó a
 * su HUB destino (current_warehouse_id === destination_warehouse_id,
 * vía LogisticsResolutionService::isAtDestinationWarehouse()) o sigue
 * pendiente de otra transferencia HUB -> HUB. No crea ningún estado
 * nuevo: current_status sigue siendo EN_HUB en ambos casos, solo
 * cambia el texto que ve el cliente.
 */
class PublicTrackingHubDestinationLabelTest extends TestCase
{
    use CreatesTestPackages;
    use RefreshDatabase;

    public function test_en_hub_at_an_intermediate_hub_shows_the_classification_label(): void
    {
        $ally = $this->createAlly();
        $originHub = Warehouse::factory()->create();
        $destinationHub = Warehouse::factory()->create(['state' => 'Carabobo', 'city' => 'Valencia']);

        WarehouseCoverage::create([
            'warehouse_id' => $destinationHub->id,
            'state' => 'Carabobo',
            'city' => 'Valencia',
            'is_active' => true,
        ]);

        $package = $this->createPackage($ally, [
            'tracking_number' => 'VEN-TEST-HUB-INTERMEDIO',
            'current_status' => Package::STATUS_EN_HUB,
            'destination_state' => 'Carabobo',
            'destination_city' => 'Valencia',
            'current_warehouse_id' => $originHub->id,
            'destination_warehouse_id' => $destinationHub->id,
            'destination_resolution_status' => 'resolved',
        ]);

        $response = $this->get(route('tracking.show', ['guia' => $package->tracking_number]));

        $response->assertOk();
        $response->assertSee('En Hub de Clasificación');
        $response->assertDontSee('Llegó al HUB de destino');
    }

    public function test_en_hub_at_the_destination_hub_shows_the_arrived_label(): void
    {
        $ally = $this->createAlly();
        $destinationHub = Warehouse::factory()->create(['state' => 'Carabobo', 'city' => 'Valencia']);

        WarehouseCoverage::create([
            'warehouse_id' => $destinationHub->id,
            'state' => 'Carabobo',
            'city' => 'Valencia',
            'is_active' => true,
        ]);

        $package = $this->createPackage($ally, [
            'tracking_number' => 'VEN-TEST-HUB-DESTINO',
            'current_status' => Package::STATUS_EN_HUB,
            'destination_state' => 'Carabobo',
            'destination_city' => 'Valencia',
            'current_warehouse_id' => $destinationHub->id,
            'destination_warehouse_id' => $destinationHub->id,
            'destination_resolution_status' => 'resolved',
        ]);

        $response = $this->get(route('tracking.show', ['guia' => $package->tracking_number]));

        $response->assertOk();
        $response->assertSee('Llegó al HUB de destino');
        $response->assertDontSee('En Hub de Clasificación');
    }

    public function test_en_hub_without_a_resolvable_destination_keeps_the_classification_label(): void
    {
        $ally = $this->createAlly();
        $someHub = Warehouse::factory()->create();

        // Sin WarehouseCoverage configurada para el destino: la
        // resolución en vivo da no_coverage, isAtDestinationWarehouse()
        // devuelve false, y debe conservarse la etiqueta genérica.
        $package = $this->createPackage($ally, [
            'tracking_number' => 'VEN-TEST-HUB-SIN-RESOLUCION',
            'current_status' => Package::STATUS_EN_HUB,
            'destination_state' => 'Carabobo',
            'destination_city' => 'Valencia',
            'current_warehouse_id' => $someHub->id,
            'destination_warehouse_id' => null,
            'destination_resolution_status' => 'no_coverage',
        ]);

        $response = $this->get(route('tracking.show', ['guia' => $package->tracking_number]));

        $response->assertOk();
        $response->assertSee('En Hub de Clasificación');
        $response->assertDontSee('Llegó al HUB de destino');
    }

    public function test_en_hub_without_current_warehouse_id_keeps_the_classification_label(): void
    {
        $ally = $this->createAlly();

        $package = $this->createPackage($ally, [
            'tracking_number' => 'VEN-TEST-HUB-SIN-WAREHOUSE',
            'current_status' => Package::STATUS_EN_HUB,
            'current_warehouse_id' => null,
            'destination_warehouse_id' => null,
        ]);

        $response = $this->get(route('tracking.show', ['guia' => $package->tracking_number]));

        $response->assertOk();
        $response->assertSee('En Hub de Clasificación');
        $response->assertDontSee('Llegó al HUB de destino');
    }

    /**
     * Un paquete que ya avanzó más allá de EN_HUB no debe verse
     * afectado: ni su etapa actual ("En Tránsito Nacional") ni el
     * paso EN_HUB ya completado en el historial (que conserva la
     * etiqueta genérica, no la de "llegó a destino" — esa distinción
     * solo aplica al estado ACTUAL del paquete).
     */
    public function test_statuses_other_than_en_hub_are_not_affected(): void
    {
        $ally = $this->createAlly();
        $destinationHub = Warehouse::factory()->create(['state' => 'Carabobo', 'city' => 'Valencia']);

        WarehouseCoverage::create([
            'warehouse_id' => $destinationHub->id,
            'state' => 'Carabobo',
            'city' => 'Valencia',
            'is_active' => true,
        ]);

        $package = $this->createPackage($ally, [
            'tracking_number' => 'VEN-TEST-EN-TRANSITO',
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
            'destination_state' => 'Carabobo',
            'destination_city' => 'Valencia',
            'current_warehouse_id' => $destinationHub->id,
            'destination_warehouse_id' => $destinationHub->id,
            'destination_resolution_status' => 'resolved',
        ]);

        $response = $this->get(route('tracking.show', ['guia' => $package->tracking_number]));

        $response->assertOk();
        $response->assertSee('En Tránsito Nacional');
        $response->assertSee('En Hub de Clasificación');
        $response->assertDontSee('Llegó al HUB de destino');
    }

    /*
    |--------------------------------------------------------------------------
    | Línea de tiempo según modalidad: retiro en persona (LISTO_RETIRO)
    | o entrega a domicilio (PENDIENTE_ENTREGA -> EN_RUTA).
    |--------------------------------------------------------------------------
    */

    public function test_a_pickup_package_shows_the_agency_pickup_steps(): void
    {
        $package = $this->createPackage($this->createAlly(), [
            'tracking_number' => 'VEN-TEST-LISTO-SIN-DELIVERY',
            'current_status' => Package::STATUS_LISTO_RETIRO,
            'requires_delivery' => false,
        ]);

        $this->get(route('tracking.show', ['guia' => $package->tracking_number]))
            ->assertOk()
            ->assertSee('Listo para Retiro en Agencia Destino')
            ->assertDontSee('Pendiente de Entrega a Domicilio')
            ->assertDontSee('En Ruta de Entrega');
    }

    public function test_a_home_delivery_package_shows_the_delivery_steps(): void
    {
        $package = $this->createPackage($this->createAlly(), [
            'tracking_number' => 'VEN-TEST-LISTO-CON-DELIVERY',
            'current_status' => Package::STATUS_PENDIENTE_ENTREGA,
            'requires_delivery' => true,
            'delivery_address' => 'Av. Bolívar, Valencia',
        ]);

        $this->get(route('tracking.show', ['guia' => $package->tracking_number]))
            ->assertOk()
            ->assertSee('Pendiente de Entrega a Domicilio')
            ->assertSee('En Ruta de Entrega')
            ->assertSee('Entregado al Cliente')
            ->assertDontSee('Listo para Retiro en Agencia Destino')
            ->assertDontSee('estado especial');
    }

    public function test_out_for_delivery_explains_the_delivery_pin(): void
    {
        $package = $this->createPackage($this->createAlly(), [
            'tracking_number' => 'VEN-TEST-EN-RUTA',
            'current_status' => Package::STATUS_EN_RUTA,
            'requires_delivery' => true,
        ]);

        $this->get(route('tracking.show', ['guia' => $package->tracking_number]))
            ->assertOk()
            ->assertSee('En Ruta de Entrega')
            ->assertSee('PIN de entrega');
    }

    public function test_a_failed_delivery_is_explained_without_moving_the_timeline_back(): void
    {
        $package = $this->createPackage($this->createAlly(), [
            'tracking_number' => 'VEN-TEST-FALLIDA',
            'current_status' => Package::STATUS_ENTREGA_FALLIDA,
            'requires_delivery' => true,
        ]);

        foreach ([Package::STATUS_PENDIENTE_ENTREGA, Package::STATUS_EN_RUTA, Package::STATUS_ENTREGA_FALLIDA] as $status) {
            PackageHistory::create([
                'package_id' => $package->id,
                'status' => $status,
                'event_type' => PackageHistory::EVENT_MOVIMIENTO,
            ]);
        }

        $response = $this->get(route('tracking.show', ['guia' => $package->tracking_number]));

        $response->assertOk()
            ->assertSee('Entrega fallida')
            ->assertSee('No pudimos entregar tu envío')
            ->assertDontSee('estado especial');

        // "En Ruta de Entrega" sigue marcado como alcanzado.
        $this->assertEqualsWithDelta(5 / 6 * 100, $response->viewData('progressPercent'), 0.001);
    }

    /**
     * La línea de tiempo nunca retrocede: si una ruta de reparto se
     * cancela y el paquete vuelve a PENDIENTE_ENTREGA, el cliente sigue
     * viendo "En Ruta de Entrega" como paso alcanzado.
     */
    public function test_the_timeline_never_moves_backwards(): void
    {
        $package = $this->createPackage($this->createAlly(), [
            'tracking_number' => 'VEN-TEST-SIN-RETROCESO',
            'current_status' => Package::STATUS_PENDIENTE_ENTREGA,
            'requires_delivery' => true,
        ]);

        foreach ([Package::STATUS_PENDIENTE_ENTREGA, Package::STATUS_EN_RUTA, Package::STATUS_PENDIENTE_ENTREGA] as $status) {
            PackageHistory::create([
                'package_id' => $package->id,
                'status' => $status,
                'event_type' => PackageHistory::EVENT_MOVIMIENTO,
            ]);
        }

        $steps = collect(
            $this->get(route('tracking.show', ['guia' => $package->tracking_number]))
                ->assertOk()
                ->viewData('statusSteps')
        )->keyBy('label');

        $this->assertTrue($steps['En Ruta de Entrega']['done']);
        $this->assertTrue($steps['Pendiente de Entrega a Domicilio']['done']);
        $this->assertFalse($steps['Entregado al Cliente']['done']);
    }

    /**
     * Transferencia HUB -> HUB: EN_HUB (origen) -> EN_TRANSITO_NACIONAL
     * -> EN_HUB (destino). El timeline conserva el tránsito alcanzado y
     * el badge dice dónde está realmente.
     */
    public function test_arriving_at_the_destination_hub_after_transit_keeps_the_transit_step(): void
    {
        $destinationHub = Warehouse::factory()->create(['state' => 'Carabobo', 'city' => 'Valencia']);

        WarehouseCoverage::create([
            'warehouse_id' => $destinationHub->id,
            'state' => 'Carabobo',
            'city' => 'Valencia',
            'is_active' => true,
        ]);

        $package = $this->createPackage($this->createAlly(), [
            'tracking_number' => 'VEN-TEST-HUB-HUB',
            'current_status' => Package::STATUS_EN_HUB,
            'destination_state' => 'Carabobo',
            'destination_city' => 'Valencia',
            'current_warehouse_id' => $destinationHub->id,
            'destination_warehouse_id' => $destinationHub->id,
            'destination_resolution_status' => 'resolved',
        ]);

        foreach ([Package::STATUS_EN_HUB, Package::STATUS_EN_TRANSITO_NACIONAL, Package::STATUS_EN_HUB] as $status) {
            PackageHistory::create([
                'package_id' => $package->id,
                'status' => $status,
                'event_type' => PackageHistory::EVENT_MOVIMIENTO,
            ]);
        }

        $response = $this->get(route('tracking.show', ['guia' => $package->tracking_number]))->assertOk();

        $steps = collect($response->viewData('statusSteps'))->keyBy('label');

        $this->assertTrue($steps['En Tránsito Nacional']['done']);
        $this->assertSame('Llegó al HUB de destino', $response->viewData('currentStatusLabel'));
    }

    /*
    |--------------------------------------------------------------------------
    | Consistencia badge <-> timeline — ambos deben mostrar exactamente
    | el mismo texto para el estado actual, nunca dos textos distintos
    | en la misma pantalla.
    |--------------------------------------------------------------------------
    */

    public function test_badge_and_timeline_show_the_same_label_when_en_hub_at_destination(): void
    {
        $ally = $this->createAlly();
        $destinationHub = Warehouse::factory()->create(['state' => 'Carabobo', 'city' => 'Valencia']);

        WarehouseCoverage::create([
            'warehouse_id' => $destinationHub->id,
            'state' => 'Carabobo',
            'city' => 'Valencia',
            'is_active' => true,
        ]);

        $package = $this->createPackage($ally, [
            'tracking_number' => 'VEN-TEST-BADGE-HUB-DESTINO',
            'current_status' => Package::STATUS_EN_HUB,
            'destination_state' => 'Carabobo',
            'destination_city' => 'Valencia',
            'current_warehouse_id' => $destinationHub->id,
            'destination_warehouse_id' => $destinationHub->id,
            'destination_resolution_status' => 'resolved',
        ]);

        $response = $this->get(route('tracking.show', ['guia' => $package->tracking_number]));
        $response->assertOk();

        $content = $response->getContent();

        $this->assertSame(2, substr_count($content, 'Llegó al HUB de destino'));
        $this->assertStringNotContainsString('En Hub de Clasificación', $content);
    }

    public function test_badge_and_timeline_show_the_same_label_when_pending_home_delivery(): void
    {
        $package = $this->createPackage($this->createAlly(), [
            'tracking_number' => 'VEN-TEST-BADGE-DELIVERY',
            'current_status' => Package::STATUS_PENDIENTE_ENTREGA,
            'requires_delivery' => true,
            'delivery_address' => 'Av. Bolívar, Valencia',
        ]);

        $content = $this->get(route('tracking.show', ['guia' => $package->tracking_number]))
            ->assertOk()
            ->getContent();

        $this->assertSame(2, substr_count($content, 'Pendiente de Entrega a Domicilio'));
        $this->assertStringNotContainsString('Listo para Retiro en Agencia Destino', $content);
    }
}
