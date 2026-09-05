<?php

namespace Tests\Feature;

use App\Models\Incident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Hallazgo de auditoría #5: los 6 estados de Package::STATUSES no
 * incluyen "devuelto"/"con incidencia", así que un paquete con una
 * incidencia abierta se veía congelado en su último paso conocido
 * sin ninguna explicación al cliente. Se agregó un aviso explícito
 * en el rastreo público cuando hay una incidencia abierta o en
 * proceso para ese paquete.
 */
class PublicTrackingIncidentTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestPackages;

    public function test_tracking_shows_incident_banner_when_there_is_an_open_incident(): void
    {
        $ally = $this->createAlly();

        $package = $this->createPackage($ally, [
            'tracking_number' => 'VEN-TEST-INCIDENTE',
        ]);

        Incident::create([
            'ally_id' => $ally->id,
            'package_id' => $package->id,
            'type' => 'PAQUETE_DAÑADO',
            'description' => 'El paquete llegó dañado al hub.',
            'status' => Incident::STATUS_OPEN,
        ]);

        $response = $this->get(route('tracking.show', ['guia' => 'VEN-TEST-INCIDENTE']));

        $response->assertOk();
        $response->assertSee('incidencia en revisión');
    }

    public function test_tracking_does_not_show_incident_banner_when_incident_is_resolved(): void
    {
        $ally = $this->createAlly();

        $package = $this->createPackage($ally, [
            'tracking_number' => 'VEN-TEST-RESUELTA',
        ]);

        Incident::create([
            'ally_id' => $ally->id,
            'package_id' => $package->id,
            'type' => 'PAQUETE_DAÑADO',
            'description' => 'Incidencia ya resuelta.',
            'status' => Incident::STATUS_RESOLVED,
            'resolved_at' => now(),
        ]);

        $response = $this->get(route('tracking.show', ['guia' => 'VEN-TEST-RESUELTA']));

        $response->assertOk();
        $response->assertDontSee('incidencia en revisión');
    }
}
