<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Driver;
use App\Models\Package;
use App\Models\PackageHistory;
use App\Models\User;
use App\Services\PackageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Cubre el hallazgo de auditoría #1: "Cero pruebas automatizadas
 * sobre la lógica que mueve dinero y paquetes". Este archivo prueba
 * específicamente el flujo de cobro contra entrega (COD), que es
 * dinero real que un repartidor recibe en efectivo del destinatario
 * y que la agencia aliada debe eventualmente liquidar.
 */
class PackageServiceCodTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestPackages;

    protected PackageService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(PackageService::class);
    }

    protected function deliverPackage(Package $package, int $userId): Package
    {
        // Recorre las transiciones válidas hasta ENTREGADO, tal como
        // lo exige validateStatusTransition().
        $steps = [
            Package::STATUS_RECOLECTADO_VENEXPRESS,
            Package::STATUS_EN_HUB,
            Package::STATUS_EN_TRANSITO_NACIONAL,
            Package::STATUS_LISTO_RETIRO,
            Package::STATUS_ENTREGADO,
        ];

        foreach ($steps as $status) {
            $package = $this->service->changeStatus($package, $status, $userId);
        }

        return $package;
    }

    public function test_cod_cannot_be_collected_before_delivery(): void
    {
        $ally = $this->createAlly();
        $user = User::factory()->create();

        $package = $this->createPackage($ally, [
            'is_cod' => true,
            'cod_amount_usd' => 25.00,
            'cod_status' => Package::COD_PENDIENTE,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('El COD solo puede registrarse después de confirmar la entrega.');

        $this->service->collectCod($package, $user->id);
    }

    public function test_full_cod_flow_collect_then_liquidate(): void
    {
        $ally = $this->createAlly();
        $user = User::factory()->create();

        $package = $this->createPackage($ally, [
            'is_cod' => true,
            'cod_amount_usd' => 25.00,
            'cod_status' => Package::COD_PENDIENTE,
        ]);

        $package = $this->deliverPackage($package, $user->id);
        $this->assertSame(Package::STATUS_ENTREGADO, $package->current_status);

        $package = $this->service->collectCod($package, $user->id);
        $this->assertNotNull($package->cod_collected_at);
        $this->assertSame($user->id, $package->cod_collected_by_user_id);
        $this->assertSame(Package::COD_PENDIENTE, $package->cod_status);

        // Cobrar el COD debe dejar un renglón en el historial, igual
        // que cualquier otro movimiento relevante del paquete —
        // necesario para reconstruir el cierre de caja/conciliación.
        $this->assertSame(
            1,
            $package->histories()
                ->where('event_type', PackageHistory::EVENT_MOVIMIENTO)
                ->where('location_description', 'Cobro COD registrado')
                ->where('scanned_by_user_id', $user->id)
                ->count()
        );

        $package = $this->service->liquidateCod($package, $user->id);
        $this->assertSame(Package::COD_LIQUIDADO, $package->cod_status);
        $this->assertNotNull($package->cod_liquidated_at);

        // El corazón del negocio: liquidar dinero DEBE dejar rastro
        // auditable.
        $this->assertSame(
            1,
            AuditLog::query()
                ->where('action', 'package.cod_liquidated')
                ->where('target_id', $package->id)
                ->count()
        );
    }

    public function test_cod_collection_is_idempotent(): void
    {
        $ally = $this->createAlly();
        $user = User::factory()->create();

        $package = $this->createPackage($ally, [
            'is_cod' => true,
            'cod_amount_usd' => 25.00,
            'cod_status' => Package::COD_PENDIENTE,
        ]);

        $package = $this->deliverPackage($package, $user->id);

        $first = $this->service->collectCod($package, $user->id);
        $second = $this->service->collectCod($first, $user->id);

        // Llamar dos veces no debe pisar la fecha de cobro original
        // ni el usuario que lo registró.
        $this->assertSame(
            $first->cod_collected_at->toDateTimeString(),
            $second->cod_collected_at->toDateTimeString()
        );
        $this->assertSame($first->cod_collected_by_user_id, $second->cod_collected_by_user_id);
    }

    public function test_cod_cannot_be_liquidated_twice(): void
    {
        $ally = $this->createAlly();
        $user = User::factory()->create();

        $package = $this->createPackage($ally, [
            'is_cod' => true,
            'cod_amount_usd' => 25.00,
            'cod_status' => Package::COD_PENDIENTE,
        ]);

        $package = $this->deliverPackage($package, $user->id);
        $package = $this->service->collectCod($package, $user->id);
        $package = $this->service->liquidateCod($package, $user->id);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('El COD ya está liquidado.');

        $this->service->liquidateCod($package, $user->id);
    }

    public function test_cod_cannot_be_liquidated_without_collecting_first(): void
    {
        $ally = $this->createAlly();
        $user = User::factory()->create();

        $package = $this->createPackage($ally, [
            'is_cod' => true,
            'cod_amount_usd' => 25.00,
            'cod_status' => Package::COD_PENDIENTE,
        ]);

        $package = $this->deliverPackage($package, $user->id);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Primero debes registrar el cobro del COD.');

        $this->service->liquidateCod($package, $user->id);
    }

    public function test_non_cod_package_rejects_cod_collection(): void
    {
        $ally = $this->createAlly();
        $user = User::factory()->create();

        $package = $this->createPackage($ally, [
            'is_cod' => false,
        ]);

        $package = $this->deliverPackage($package, $user->id);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Este paquete no tiene COD.');

        $this->service->collectCod($package, $user->id);
    }

    public function test_invalid_status_transition_is_rejected(): void
    {
        $ally = $this->createAlly();
        $user = User::factory()->create();

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_RECIBIDO_AGENCIA,
        ]);

        // No se puede saltar directo a ENTREGADO sin pasar por el
        // resto de la cadena logística.
        $this->expectException(RuntimeException::class);

        $this->service->changeStatus($package, Package::STATUS_ENTREGADO, $user->id);
    }

    public function test_status_cannot_change_once_delivered(): void
    {
        $ally = $this->createAlly();
        $user = User::factory()->create();

        $package = $this->createPackage($ally);
        $package = $this->deliverPackage($package, $user->id);

        $this->expectException(RuntimeException::class);

        $this->service->changeStatus($package, Package::STATUS_EN_TRANSITO_NACIONAL, $user->id);
    }

    private function createActiveDriver(): Driver
    {
        $user = User::factory()->create([
            'role' => User::ROLE_REPARTIDOR,
            'status' => User::STATUS_ACTIVE,
        ]);

        return Driver::factory()->create([
            'user_id' => $user->id,
            'status' => Driver::STATUS_ACTIVE,
        ]);
    }

    /**
     * completeDelivery() ya no puede marcar un COD como cobrado sin
     * que el repartidor confirme con qué forma de pago le cancelaron
     * — antes lo asumía automáticamente, sin ningún registro real.
     */
    /**
     * Igual que completeDelivery(), collectCod() ahora rechaza a un
     * repartidor que ya no está activo, aunque su token todavía no
     * haya sido revocado (defensa en profundidad además de la
     * revocación de tokens en DriversApprovalManager).
     */
    public function test_collect_cod_rejects_a_suspended_driver(): void
    {
        $ally = $this->createAlly();
        $driver = $this->createActiveDriver();

        $package = $this->createPackage($ally, [
            'requires_delivery' => true,
            'driver_id' => $driver->id,
            'current_status' => Package::STATUS_ENTREGADO,
            'is_cod' => true,
            'cod_amount_usd' => 15.00,
        ]);

        $driver->update(['status' => Driver::STATUS_SUSPENDED]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('El repartidor no está activo.');

        $this->service->collectCod($package, $driver->user_id, $driver);
    }

    public function test_complete_delivery_requires_a_payment_method_for_cod_packages(): void
    {
        $ally = $this->createAlly();
        $driver = $this->createActiveDriver();

        $package = $this->createPackage($ally, [
            'requires_delivery' => true,
            'driver_id' => $driver->id,
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
            'is_cod' => true,
            'cod_amount_usd' => 15.00,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Este pedido es contra entrega (COD): indica la forma de pago con la que te cancelaron antes de confirmar la entrega.'
        );

        $this->service->completeDelivery(
            package: $package,
            driver: $driver,
            receiverName: 'María Gómez',
            receiverIdDoc: 'V-87654321',
            deliveryConfirmationMethod: 'cedula',
        );
    }

    public function test_complete_delivery_stores_the_cod_payment_method(): void
    {
        $ally = $this->createAlly();
        $driver = $this->createActiveDriver();

        $package = $this->createPackage($ally, [
            'requires_delivery' => true,
            'driver_id' => $driver->id,
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
            'is_cod' => true,
            'cod_amount_usd' => 15.00,
        ]);

        $delivered = $this->service->completeDelivery(
            package: $package,
            driver: $driver,
            receiverName: 'María Gómez',
            receiverIdDoc: 'V-87654321',
            deliveryConfirmationMethod: 'cedula',
            codPaymentMethod: 'pago_movil',
        );

        $this->assertSame(Package::STATUS_ENTREGADO, $delivered->current_status);
        $this->assertNotNull($delivered->cod_collected_at);
        $this->assertSame('pago_movil', $delivered->cod_payment_method);
    }

    public function test_complete_delivery_does_not_require_a_payment_method_for_non_cod_packages(): void
    {
        $ally = $this->createAlly();
        $driver = $this->createActiveDriver();

        $package = $this->createPackage($ally, [
            'requires_delivery' => true,
            'driver_id' => $driver->id,
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
            'is_cod' => false,
        ]);

        $delivered = $this->service->completeDelivery(
            package: $package,
            driver: $driver,
            receiverName: 'María Gómez',
            receiverIdDoc: 'V-87654321',
            deliveryConfirmationMethod: 'cedula',
        );

        $this->assertSame(Package::STATUS_ENTREGADO, $delivered->current_status);
        $this->assertNull($delivered->cod_collected_at);
        $this->assertNull($delivered->cod_payment_method);
    }
}
