<?php

namespace Tests\Feature;

use App\Models\Package;
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
    | LISTO_RETIRO — "Listo para Retiro en Agencia Destino" vs.
    | "Listo para Entrega" según requires_delivery. No es un estado
    | nuevo: mismo current_status, solo cambia el texto que ve el
    | cliente (mismo patrón que la relabelación de EN_HUB de arriba).
    |--------------------------------------------------------------------------
    */

    public function test_listo_retiro_without_delivery_shows_the_agency_pickup_label(): void
    {
        $ally = $this->createAlly();

        $package = $this->createPackage($ally, [
            'tracking_number' => 'VEN-TEST-LISTO-SIN-DELIVERY',
            'current_status' => Package::STATUS_LISTO_RETIRO,
            'requires_delivery' => false,
        ]);

        $response = $this->get(route('tracking.show', ['guia' => $package->tracking_number]));

        $response->assertOk();
        $response->assertSee('Listo para Retiro en Agencia Destino');
        $response->assertDontSee('Listo para Entrega');
    }

    public function test_listo_retiro_with_delivery_shows_the_ready_for_delivery_label(): void
    {
        $ally = $this->createAlly();

        $package = $this->createPackage($ally, [
            'tracking_number' => 'VEN-TEST-LISTO-CON-DELIVERY',
            'current_status' => Package::STATUS_LISTO_RETIRO,
            'requires_delivery' => true,
            'delivery_address' => 'Av. Bolívar, Valencia',
        ]);

        $response = $this->get(route('tracking.show', ['guia' => $package->tracking_number]));

        $response->assertOk();
        $response->assertSee('Listo para Entrega');
        $response->assertDontSee('Listo para Retiro en Agencia Destino');
    }

    /**
     * Un LISTO_RETIRO ya completado en el historial (no es el paso
     * actual) conserva la etiqueta genérica — la relabelación por
     * requires_delivery, igual que la de EN_HUB, solo aplica al paso
     * ACTUAL del timeline.
     */
    public function test_a_past_listo_retiro_step_keeps_the_generic_label_even_with_delivery(): void
    {
        $ally = $this->createAlly();

        $package = $this->createPackage($ally, [
            'tracking_number' => 'VEN-TEST-LISTO-PASADO',
            'current_status' => Package::STATUS_ENTREGADO,
            'requires_delivery' => true,
        ]);

        $response = $this->get(route('tracking.show', ['guia' => $package->tracking_number]));

        $response->assertOk();
        $response->assertSee('Entregado al Cliente');
        $response->assertSee('Listo para Retiro en Agencia Destino');
        $response->assertDontSee('Listo para Entrega');
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

    public function test_badge_and_timeline_show_the_same_label_when_listo_retiro_with_delivery(): void
    {
        $ally = $this->createAlly();

        $package = $this->createPackage($ally, [
            'tracking_number' => 'VEN-TEST-BADGE-DELIVERY',
            'current_status' => Package::STATUS_LISTO_RETIRO,
            'requires_delivery' => true,
            'delivery_address' => 'Av. Bolívar, Valencia',
        ]);

        $response = $this->get(route('tracking.show', ['guia' => $package->tracking_number]));
        $response->assertOk();

        $content = $response->getContent();

        $this->assertSame(2, substr_count($content, 'Listo para Entrega'));
        $this->assertStringNotContainsString('Listo para Retiro en Agencia Destino', $content);
    }
}
