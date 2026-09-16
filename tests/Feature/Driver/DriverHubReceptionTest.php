<?php

namespace Tests\Feature\Driver;

use App\Livewire\Driver\Scanner;
use App\Models\Ally;
use App\Models\Driver;
use App\Models\Package;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\User;
use App\Services\HubReceptionService;
use App\Services\LogisticsScanService;
use App\Services\RouteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use RuntimeException;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Fase 5A — LogisticsScanService::scanHubReception() quedó bloqueado:
 * la recepción/verificación interna en HUB dejó de ser algo que el
 * Driver puede confirmar desde el Scanner. Ahora es una operación
 * administrativa interna (ver
 * tests/Feature/Admin/PackageReceptionHubTest.php y
 * tests/Feature/Services/HubReceptionServiceTest.php).
 *
 * Este archivo antes cubría el escaneo exitoso de recepción en HUB
 * por parte del Driver; ahora cubre exactamente lo contrario: que ese
 * camino esté bloqueado, con un mensaje claro, sin tocar Scanner.php
 * ni scanner.blade.php (protegidos en esta fase).
 *
 * Reutiliza HubReceptionService::receive() tal cual para la
 * transición RECOLECTADO_VENEXPRESS -> EN_HUB (EVENT_RECEPCION) — ese
 * método no cambió y sigue funcionando igual para el smoke test de
 * más abajo.
 */
class DriverHubReceptionTest extends TestCase
{
    use CreatesTestPackages;
    use RefreshDatabase;

    private const BLOCKED_MESSAGE =
        'La recepción de paquetes en HUB ahora se confirma desde la operación interna de HUB '
        .'en el panel de Admin. Los repartidores ya no pueden confirmarla desde aquí.';

    private function createDriverUser(string $driverType = Driver::TYPE_HUB): array
    {
        $user = User::factory()->create([
            'role' => User::ROLE_REPARTIDOR,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $driver = Driver::factory()->create([
            'user_id' => $user->id,
            'status' => Driver::STATUS_ACTIVE,
            'driver_type' => $driverType,
        ]);

        return [$user, $driver];
    }

    private function createInProgressHubTransferRoute(Driver $driver, Ally $ally): array
    {
        $creator = User::factory()->create();

        $route = Route::create([
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
            'name' => 'Recolección Caracas',
            'driver_id' => $driver->id,
            'created_by' => $creator->id,
            'status' => Route::STATUS_IN_PROGRESS,
            'started_at' => now(),
            'route_type' => Route::TYPE_HUB_TRANSFER,
        ]);

        $stop = RouteStop::create([
            'route_id' => $route->id,
            'ally_id' => $ally->id,
            'sequence' => 1,
            'status' => RouteStop::STATUS_PENDING,
        ]);

        return [$route, $stop];
    }

    public function test_scan_hub_reception_is_blocked_even_for_an_otherwise_valid_reception(): void
    {
        [$user, $driver] = $this->createDriverUser();
        $ally = $this->createAlly();
        $this->createInProgressHubTransferRoute($driver, $ally);

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_RECIBIDO_AGENCIA,
        ]);

        $scanService = app(LogisticsScanService::class);
        $scanService->scanCollection($package, $driver, (int) $user->id);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(self::BLOCKED_MESSAGE);

        $scanService->scanHubReception($package->fresh(), $driver, (int) $user->id);
    }

    public function test_scan_hub_reception_is_blocked_and_does_not_change_package_status(): void
    {
        [$user, $driver] = $this->createDriverUser();
        $ally = $this->createAlly();
        $this->createInProgressHubTransferRoute($driver, $ally);

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_RECIBIDO_AGENCIA,
        ]);

        $scanService = app(LogisticsScanService::class);
        $scanService->scanCollection($package, $driver, (int) $user->id);

        try {
            $scanService->scanHubReception($package->fresh(), $driver, (int) $user->id);
        } catch (RuntimeException $e) {
            // esperado
        }

        $this->assertSame(Package::STATUS_RECOLECTADO_VENEXPRESS, $package->fresh()->current_status);
    }

    public function test_scan_hub_reception_is_blocked_regardless_of_driver_type(): void
    {
        [$user, $driver] = $this->createDriverUser(Driver::TYPE_DELIVERY);
        $ally = $this->createAlly();

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_RECOLECTADO_VENEXPRESS,
            'driver_id' => $driver->id,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(self::BLOCKED_MESSAGE);

        app(LogisticsScanService::class)->scanHubReception($package, $driver, (int) $user->id);
    }

    public function test_admin_hub_reception_flow_still_works_without_any_route(): void
    {
        $admin = User::factory()->create();
        $ally = $this->createAlly();

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_RECOLECTADO_VENEXPRESS,
        ]);

        $received = app(HubReceptionService::class)->receive(
            package: $package,
            userId: (int) $admin->id,
            hubLocation: 'HUB Caracas',
        );

        $this->assertSame(Package::STATUS_EN_HUB, $received->current_status);
    }

    public function test_scanner_blocks_hub_reception_after_collecting_on_the_same_route(): void
    {
        [$user, $driver] = $this->createDriverUser();
        $ally = $this->createAlly();
        $this->createInProgressHubTransferRoute($driver, $ally);

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_RECIBIDO_AGENCIA,
        ]);

        $component = Livewire::actingAs($user)->test(Scanner::class);

        // 1er escaneo: un paquete = un solo escaneo, así que
        // searchPackage() ya identifica y ejecuta "collection" en el
        // mismo paso. scanCollection() no cambió en Fase 5A, sigue
        // funcionando igual.
        $component
            ->set('trackingNumber', $package->tracking_number)
            ->call('searchPackage')
            ->assertSet('errorMessage', null);

        $this->assertSame(Package::STATUS_RECOLECTADO_VENEXPRESS, $package->fresh()->current_status);

        // 2do escaneo de la MISMA guía inmediatamente después: como
        // coincide con lastProcessedPackageId, no se ejecuta sola —
        // Scanner.php la deja pendiente de confirmación explícita en
        // vez de encadenar la siguiente etapa ("hub_reception") sin que
        // el driver lo pidiera.
        $component
            ->set('trackingNumber', $package->tracking_number)
            ->call('searchPackage')
            ->assertSet('errorMessage', null)
            ->assertSet('pendingOperation', 'hub_reception');

        // Confirmarla ahora queda bloqueada por
        // LogisticsScanService::scanHubReception().
        $component
            ->call('confirmOperation', 'hub_reception')
            ->assertSet('errorMessage', self::BLOCKED_MESSAGE);

        // El paquete se queda en RECOLECTADO_VENEXPRESS: ya no avanza
        // a EN_HUB por esta vía. La recepción real ahora se hace desde
        // Admin\PackageReception (ver PackageReceptionHubTest).
        $this->assertSame(Package::STATUS_RECOLECTADO_VENEXPRESS, $package->fresh()->current_status);
    }
}
