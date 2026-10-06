<?php

namespace Tests\Feature\Notifications;

use App\Models\Ally;
use App\Models\Customer;
use App\Models\Package;
use App\Models\Warehouse;
use App\Models\WarehouseCoverage;
use App\Notifications\PackageStatusUpdated;
use App\Services\DestinationReceptionService;
use App\Services\HubReleaseService;
use App\Services\LogisticsResolutionResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use RuntimeException;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * DestinationReceptionService y HubReleaseService llevan el paquete a
 * LISTO_RETIRO escribiendo current_status directamente (no pasan por
 * PackageService::changeStatus()), así que el destinatario nunca recibía
 * el aviso de que ya podía retirar su paquete. Ahora usan el mismo canal
 * que el resto de estados (PackageService::notifyStatusChange(), correo
 * PackageStatusUpdated al customer del recipient_id_doc), solo cuando la
 * transacción se confirma.
 */
class ReadyForPickupNotificationTest extends TestCase
{
    use CreatesTestPackages;
    use RefreshDatabase;

    private const RECIPIENT_EMAIL = 'destinatario@example.com';

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        // createPackage() usa recipient_id_doc V-87654321.
        Customer::create([
            'id_doc' => 'V-87654321',
            'name' => 'María Gómez',
            'phone' => '0424-7654321',
            'email' => self::RECIPIENT_EMAIL,
        ]);
    }

    private function verifiedPickupAlly(): Ally
    {
        return $this->createAlly([
            'is_verified_destination' => true,
            'status' => Ally::STATUS_ACTIVE,
        ]);
    }

    private function destinationHub(): Warehouse
    {
        $hub = Warehouse::factory()->create(['state' => 'Carabobo', 'city' => 'Valencia', 'is_active' => true]);

        WarehouseCoverage::create([
            'warehouse_id' => $hub->id,
            'state' => 'Carabobo',
            'city' => 'Valencia',
            'is_active' => true,
        ]);

        return $hub;
    }

    private function assertReadyNotificationSentTimes(int $times, ?Package $package = null, string $status = Package::STATUS_LISTO_RETIRO): void
    {
        Notification::assertSentTimes(PackageStatusUpdated::class, $times);

        if ($times === 0) {
            return;
        }

        Notification::assertSentTo(
            new AnonymousNotifiable,
            PackageStatusUpdated::class,
            function (PackageStatusUpdated $notification, array $channels, AnonymousNotifiable $notifiable) use ($package, $status) {
                $data = $notification->toArray($notifiable);

                return $notifiable->routes['mail'] === self::RECIPIENT_EMAIL
                    && $data['status'] === $status
                    && ($package === null || $data['package_id'] === $package->id);
            }
        );
    }

    public function test_reception_at_destination_agency_notifies_the_recipient_once(): void
    {
        $origin = $this->createAlly();
        $pickupAlly = $this->verifiedPickupAlly();

        $package = $this->createPackage($origin, [
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
            'requires_delivery' => false,
            'pickup_mode' => Package::PICKUP_MODE_ALLY,
            'pickup_ally_id' => $pickupAlly->id,
        ]);

        $received = app(DestinationReceptionService::class)->receive(
            package: $package,
            userId: $pickupAlly->user_id,
            destinationLocation: $pickupAlly->business_name,
        );

        $this->assertSame(Package::STATUS_LISTO_RETIRO, $received->current_status);
        $this->assertReadyNotificationSentTimes(1, $package);
    }

    public function test_hub_pickup_release_notifies_the_recipient_once(): void
    {
        $ally = $this->createAlly();
        $hub = $this->destinationHub();

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_HUB,
            'current_warehouse_id' => $hub->id,
            'destination_warehouse_id' => $hub->id,
            'destination_resolution_status' => LogisticsResolutionResult::STATUS_RESOLVED,
            'requires_delivery' => false,
            'pickup_mode' => Package::PICKUP_MODE_HUB,
        ]);

        $released = app(HubReleaseService::class)->release($package, $ally->user_id);

        $this->assertSame(Package::STATUS_LISTO_RETIRO, $released->current_status);
        $this->assertReadyNotificationSentTimes(1, $package);
    }

    public function test_delivery_release_notifies_the_recipient_once(): void
    {
        $ally = $this->createAlly();
        $hub = $this->destinationHub();

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_HUB,
            'current_warehouse_id' => $hub->id,
            'destination_warehouse_id' => $hub->id,
            'destination_resolution_status' => LogisticsResolutionResult::STATUS_RESOLVED,
            'requires_delivery' => true,
            'delivery_address' => 'Calle 1',
        ]);

        $released = app(HubReleaseService::class)->release($package, $ally->user_id);

        $this->assertSame(Package::STATUS_PENDIENTE_ENTREGA, $released->current_status);
        $this->assertReadyNotificationSentTimes(1, $package, Package::STATUS_PENDIENTE_ENTREGA);
    }

    public function test_an_invalid_reception_does_not_send_a_false_notification(): void
    {
        $origin = $this->createAlly();
        $pickupAlly = $this->verifiedPickupAlly();

        // Todavía en la agencia de origen: no puede recibirse en destino.
        $package = $this->createPackage($origin, [
            'current_status' => Package::STATUS_RECIBIDO_AGENCIA,
            'requires_delivery' => false,
            'pickup_mode' => Package::PICKUP_MODE_ALLY,
            'pickup_ally_id' => $pickupAlly->id,
        ]);

        try {
            app(DestinationReceptionService::class)->receive($package, $pickupAlly->user_id, $pickupAlly->business_name);
            $this->fail('La recepción debía rechazarse.');
        } catch (RuntimeException) {
            // esperado
        }

        $this->assertSame(Package::STATUS_RECIBIDO_AGENCIA, $package->fresh()->current_status);
        $this->assertReadyNotificationSentTimes(0);
    }

    public function test_a_second_reception_of_the_same_package_does_not_notify_again(): void
    {
        $origin = $this->createAlly();
        $pickupAlly = $this->verifiedPickupAlly();

        $package = $this->createPackage($origin, [
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
            'requires_delivery' => false,
            'pickup_mode' => Package::PICKUP_MODE_ALLY,
            'pickup_ally_id' => $pickupAlly->id,
        ]);

        $service = app(DestinationReceptionService::class);
        $service->receive($package, $pickupAlly->user_id, $pickupAlly->business_name);

        try {
            $service->receive($package->fresh(), $pickupAlly->user_id, $pickupAlly->business_name);
        } catch (RuntimeException) {
            // ya está LISTO_RETIRO: se rechaza
        }

        $this->assertReadyNotificationSentTimes(1, $package);
    }

    public function test_no_notification_is_sent_when_the_transaction_is_rolled_back(): void
    {
        $origin = $this->createAlly();
        $pickupAlly = $this->verifiedPickupAlly();

        $package = $this->createPackage($origin, [
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
            'requires_delivery' => false,
            'pickup_mode' => Package::PICKUP_MODE_ALLY,
            'pickup_ally_id' => $pickupAlly->id,
        ]);

        try {
            DB::transaction(function () use ($package, $pickupAlly) {
                app(DestinationReceptionService::class)->receive($package, $pickupAlly->user_id, $pickupAlly->business_name);

                throw new RuntimeException('Algo falló después, en la misma operación.');
            });
        } catch (RuntimeException) {
            // esperado
        }

        $this->assertSame(Package::STATUS_EN_TRANSITO_NACIONAL, $package->fresh()->current_status);
        $this->assertReadyNotificationSentTimes(0);
    }

    public function test_pickup_mail_says_the_package_is_ready_to_be_picked_up(): void
    {
        $origin = $this->createAlly();
        $pickupAlly = $this->verifiedPickupAlly();

        $package = $this->createPackage($origin, [
            'current_status' => Package::STATUS_LISTO_RETIRO,
            'requires_delivery' => false,
            'pickup_mode' => Package::PICKUP_MODE_ALLY,
            'pickup_ally_id' => $pickupAlly->id,
        ]);

        $mail = (new PackageStatusUpdated($package->id, Package::STATUS_LISTO_RETIRO))
            ->toMail(new AnonymousNotifiable);

        $text = implode(' ', $mail->introLines);

        $this->assertStringContainsString('ya está disponible para ser retirado', $text);
        $this->assertStringContainsString($pickupAlly->business_name, $text);
    }

    public function test_delivery_mail_says_the_package_is_ready_for_home_delivery(): void
    {
        $ally = $this->createAlly();

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_PENDIENTE_ENTREGA,
            'requires_delivery' => true,
        ]);

        $mail = (new PackageStatusUpdated($package->id, Package::STATUS_PENDIENTE_ENTREGA))
            ->toMail(new AnonymousNotifiable);

        $this->assertStringContainsString('lista para entrega a domicilio', $mail->subject);
        $this->assertStringContainsString('PIN de entrega', implode(' ', $mail->introLines));
    }
}
