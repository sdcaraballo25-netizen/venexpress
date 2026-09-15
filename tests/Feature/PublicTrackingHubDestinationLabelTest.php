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
}
