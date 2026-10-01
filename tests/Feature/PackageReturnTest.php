<?php

namespace Tests\Feature;

use App\Models\AllyFinancialTransaction;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Package;
use App\Models\PackageHistory;
use App\Models\User;
use App\Notifications\PackageStatusUpdated;
use App\Services\PackageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use RuntimeException;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Devolución al remitente: un admin la inicia sobre un paquete no
 * entregado (EN_DEVOLUCION) y la agencia de origen la cierra al
 * entregárselo al remitente verificando su cédula (DEVUELTO).
 */
class PackageReturnTest extends TestCase
{
    use CreatesTestPackages;
    use RefreshDatabase;

    private PackageService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(PackageService::class);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN_PRINCIPAL, 'status' => User::STATUS_ACTIVE]);
    }

    public function test_an_admin_can_start_a_return_from_every_returnable_status(): void
    {
        $ally = $this->createAlly();
        $admin = $this->admin();

        foreach (Package::RETURNABLE_STATUSES as $status) {
            $package = $this->createPackage($ally, ['current_status' => $status]);

            $returned = $this->service->startReturn($package, $admin->id, 'Destinatario ausente tres veces');

            $this->assertSame(Package::STATUS_EN_DEVOLUCION, $returned->current_status, $status);
            $this->assertSame('Destinatario ausente tres veces', $returned->return_reason);
            $this->assertNotNull($returned->return_requested_at);
        }
    }

    public function test_starting_a_return_releases_the_driver_cancels_pending_cod_and_logs_history(): void
    {
        $ally = $this->createAlly();
        $admin = $this->admin();
        $driver = Driver::factory()->create();

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
            'driver_id' => $driver->id,
            'is_cod' => true,
            'cod_amount_usd' => 20,
            'cod_status' => Package::COD_PENDIENTE,
        ]);

        $returned = $this->service->startReturn($package, $admin->id, 'Dirección inexistente');

        $this->assertNull($returned->driver_id);
        $this->assertSame(Package::COD_CANCELADO, $returned->cod_status);

        $history = $returned->histories()->latest('id')->first();
        $this->assertSame(Package::STATUS_EN_DEVOLUCION, $history->status);
        $this->assertSame(PackageHistory::EVENT_DEVOLUCION, $history->event_type);
        $this->assertSame($admin->id, (int) $history->scanned_by_user_id);
        $this->assertStringContainsString('Dirección inexistente', $history->location_description);
    }

    public function test_a_return_cannot_start_before_the_package_leaves_origin_or_after_delivery(): void
    {
        $ally = $this->createAlly();
        $admin = $this->admin();

        foreach ([Package::STATUS_RECIBIDO_AGENCIA, Package::STATUS_ENTREGADO, Package::STATUS_EN_DEVOLUCION, Package::STATUS_DEVUELTO] as $status) {
            $package = $this->createPackage($ally, ['current_status' => $status]);

            try {
                $this->service->startReturn($package, $admin->id, 'Motivo');
                $this->fail("Se esperaba un error para el estado {$status}.");
            } catch (RuntimeException) {
                $this->assertSame($status, $package->fresh()->current_status);
            }
        }
    }

    public function test_a_reason_is_required(): void
    {
        $package = $this->createPackage($this->createAlly(), ['current_status' => Package::STATUS_EN_HUB]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Indica el motivo de la devolución.');

        $this->service->startReturn($package, $this->admin()->id, '   ');
    }

    public function test_the_origin_agency_completes_the_return_after_verifying_the_sender(): void
    {
        $ally = $this->createAlly();
        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_LISTO_RETIRO,
            'sender_id_doc' => 'V-12345678',
        ]);

        $this->service->startReturn($package, $this->admin()->id, 'No retirado');

        $returned = $this->service->completeReturn($package, $ally->user->id, ' V-12345678 ', $ally);

        $this->assertSame(Package::STATUS_DEVUELTO, $returned->current_status);
        $this->assertNotNull($returned->returned_at);
        $this->assertSame(PackageHistory::EVENT_DEVOLUCION, $returned->histories()->latest('id')->first()->event_type);
    }

    public function test_completing_a_return_requires_the_senders_document(): void
    {
        $ally = $this->createAlly();
        $package = $this->createPackage($ally, ['current_status' => Package::STATUS_EN_HUB, 'sender_id_doc' => 'V-12345678']);
        $this->service->startReturn($package, $this->admin()->id, 'No retirado');

        $this->expectExceptionMessage('El documento no coincide con el del remitente.');

        try {
            $this->service->completeReturn($package, $ally->user->id, 'V-99999999', $ally);
        } finally {
            $this->assertSame(Package::STATUS_EN_DEVOLUCION, $package->fresh()->current_status);
        }
    }

    public function test_only_the_origin_agency_can_complete_the_return(): void
    {
        $origin = $this->createAlly();
        $otherAlly = $this->createAlly();
        $package = $this->createPackage($origin, ['current_status' => Package::STATUS_EN_HUB, 'sender_id_doc' => 'V-12345678']);
        $this->service->startReturn($package, $this->admin()->id, 'No retirado');

        $this->expectExceptionMessage('Esta guía no se registró en tu agencia.');

        $this->service->completeReturn($package, $otherAlly->user->id, 'V-12345678', $otherAlly);
    }

    public function test_a_package_not_in_return_cannot_be_handed_back(): void
    {
        $ally = $this->createAlly();
        $package = $this->createPackage($ally, ['current_status' => Package::STATUS_LISTO_RETIRO, 'sender_id_doc' => 'V-12345678']);

        $this->expectExceptionMessage('Esta guía no está en devolución.');

        $this->service->completeReturn($package, $ally->user->id, 'V-12345678', $ally);
    }

    public function test_the_normal_flow_cannot_move_a_package_out_of_a_return(): void
    {
        $ally = $this->createAlly();
        $package = $this->createPackage($ally, ['current_status' => Package::STATUS_LISTO_RETIRO]);
        $package = $this->service->startReturn($package, $this->admin()->id, 'No retirado');

        foreach ([Package::STATUS_ENTREGADO, Package::STATUS_EN_TRANSITO_NACIONAL, Package::STATUS_DEVUELTO] as $status) {
            try {
                $this->service->changeStatus($package, $status, $this->admin()->id);
                $this->fail("changeStatus() no debería permitir {$status} desde una devolución.");
            } catch (RuntimeException) {
                $this->assertSame(Package::STATUS_EN_DEVOLUCION, $package->fresh()->current_status);
            }
        }
    }

    public function test_the_ally_commission_is_kept(): void
    {
        $ally = $this->createAlly();
        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_HUB,
            'commission_amount_usd' => 1.50,
        ]);

        $before = AllyFinancialTransaction::where('ally_id', $ally->id)->sum('amount_usd');
        $this->assertEquals(1.50, $before);

        $this->service->startReturn($package, $this->admin()->id, 'No retirado');

        $this->assertEquals($before, AllyFinancialTransaction::where('ally_id', $ally->id)->sum('amount_usd'));
    }

    public function test_sender_and_recipient_are_notified(): void
    {
        Notification::fake();

        Customer::create(['id_doc' => 'V-11111111', 'name' => 'Remitente', 'phone' => '0414', 'email' => 'remitente@example.com']);
        Customer::create(['id_doc' => 'V-22222222', 'name' => 'Destinatario', 'phone' => '0424', 'email' => 'destinatario@example.com']);

        $package = $this->createPackage($this->createAlly(), [
            'current_status' => Package::STATUS_EN_HUB,
            'sender_id_doc' => 'V-11111111',
            'recipient_id_doc' => 'V-22222222',
        ]);

        $this->service->startReturn($package, $this->admin()->id, 'No retirado');

        foreach (['remitente@example.com', 'destinatario@example.com'] as $email) {
            Notification::assertSentOnDemand(
                PackageStatusUpdated::class,
                fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === $email
            );
        }
    }
}
