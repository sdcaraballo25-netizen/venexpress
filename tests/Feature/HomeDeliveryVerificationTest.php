<?php

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\Package;
use App\Models\User;
use App\Services\PackageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Confirmación de una entrega a domicilio (PackageService::
 * completeDelivery()): solo desde EN_RUTA, con el PIN del destinatario
 * o, sin él, con su cédula y una foto.
 */
class HomeDeliveryVerificationTest extends TestCase
{
    use CreatesTestPackages;
    use RefreshDatabase;

    private const PIN = '482915';

    private function driverWithUser(string $password = 'password-seguro'): array
    {
        $user = User::factory()->create([
            'role' => User::ROLE_REPARTIDOR,
            'status' => User::STATUS_ACTIVE,
            'password' => bcrypt($password),
        ]);

        $driver = Driver::factory()->create([
            'user_id' => $user->id,
            'status' => Driver::STATUS_ACTIVE,
            'driver_type' => Driver::TYPE_DELIVERY,
        ]);

        return [$user, $driver];
    }

    private function outForDelivery(Driver $driver, array $overrides = []): Package
    {
        return $this->createPackage($this->createAlly(), array_merge([
            'requires_delivery' => true,
            'driver_id' => $driver->id,
            'current_status' => Package::STATUS_EN_RUTA,
            'recipient_id_doc' => 'V-87654321',
            'delivery_pin_hash' => Hash::make(self::PIN),
            'delivery_pin_generated_at' => now(),
        ], $overrides));
    }

    private function complete(Package $package, Driver $driver, array $args = []): Package
    {
        return app(PackageService::class)->completeDelivery(...array_merge([
            'package' => $package,
            'driver' => $driver,
            'receiverName' => 'María Gómez',
        ], $args));
    }

    public function test_the_right_pin_confirms_the_delivery_without_id_or_photo(): void
    {
        [, $driver] = $this->driverWithUser();
        $package = $this->outForDelivery($driver);

        $delivered = $this->complete($package, $driver, ['deliveryPin' => self::PIN]);

        $this->assertSame(Package::STATUS_ENTREGADO, $delivered->current_status);
        $this->assertSame(Package::DELIVERY_CONFIRMATION_PIN, $delivered->delivery_confirmation_method);
        $this->assertNull($delivered->delivery_pin_hash);
        $this->assertStringContainsString('verificada con PIN', $delivered->histories()->latest('id')->first()->location_description);
    }

    public function test_wrong_pins_are_counted_and_then_the_pin_stops_being_accepted(): void
    {
        [, $driver] = $this->driverWithUser();
        $package = $this->outForDelivery($driver);

        for ($attempt = 1; $attempt <= Package::DELIVERY_PIN_MAX_ATTEMPTS; $attempt++) {
            try {
                $this->complete($package, $driver, ['deliveryPin' => '000000']);
                $this->fail('Se aceptó un PIN incorrecto.');
            } catch (RuntimeException $e) {
                $this->assertStringStartsWith('PIN incorrecto.', $e->getMessage());
            }

            $this->assertSame($attempt, $package->fresh()->delivery_pin_failed_attempts);
        }

        $this->assertFalse($package->fresh()->acceptsDeliveryPin());

        // Ni siquiera el PIN correcto sirve ya.
        try {
            $this->complete($package, $driver, ['deliveryPin' => self::PIN]);
            $this->fail('Se aceptó el PIN después de agotar los intentos.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('Se agotaron los intentos de PIN', $e->getMessage());
        }

        $this->assertSame(Package::STATUS_EN_RUTA, $package->fresh()->current_status);

        // Queda la vía de la cédula + foto.
        $delivered = $this->complete($package, $driver, [
            'receiverIdDoc' => 'V-87654321',
            'deliveryPhotoPath' => 'delivery-evidence/foto.jpg',
        ]);

        $this->assertSame(Package::DELIVERY_CONFIRMATION_ID_DOC, $delivered->delivery_confirmation_method);
    }

    public function test_without_pin_the_id_must_match_the_recipient(): void
    {
        [, $driver] = $this->driverWithUser();
        $package = $this->outForDelivery($driver);

        $this->expectExceptionMessage('La cédula no coincide con la del destinatario.');

        try {
            $this->complete($package, $driver, [
                'receiverIdDoc' => 'V-11111111',
                'deliveryPhotoPath' => 'delivery-evidence/foto.jpg',
            ]);
        } finally {
            $this->assertSame(Package::STATUS_EN_RUTA, $package->fresh()->current_status);
        }
    }

    public function test_without_pin_a_photo_is_required(): void
    {
        [, $driver] = $this->driverWithUser();
        $package = $this->outForDelivery($driver);

        $this->expectExceptionMessage('Sin PIN, adjunta una foto de la entrega para confirmarla.');

        $this->complete($package, $driver, ['receiverIdDoc' => 'V-87654321']);
    }

    public function test_the_id_comparison_ignores_formatting(): void
    {
        [, $driver] = $this->driverWithUser();
        $package = $this->outForDelivery($driver);

        $delivered = $this->complete($package, $driver, [
            'receiverIdDoc' => 'v 87.654.321',
            'deliveryPhotoPath' => 'delivery-evidence/foto.jpg',
        ]);

        $this->assertSame(Package::STATUS_ENTREGADO, $delivered->current_status);

        $this->assertTrue(Package::idDocsMatch('87654321', 'V-87654321'));
        $this->assertFalse(Package::idDocsMatch('E-87654321', 'V-87654321'));
        $this->assertFalse(Package::idDocsMatch('', 'V-87654321'));
    }

    public function test_a_delivery_can_only_be_completed_from_en_ruta(): void
    {
        [, $driver] = $this->driverWithUser();

        foreach ([Package::STATUS_PENDIENTE_ENTREGA, Package::STATUS_EN_TRANSITO_NACIONAL, Package::STATUS_LISTO_RETIRO] as $status) {
            $package = $this->outForDelivery($driver, ['current_status' => $status]);

            try {
                $this->complete($package, $driver, ['deliveryPin' => self::PIN]);
                $this->fail("Se entregó un paquete en {$status}.");
            } catch (RuntimeException $e) {
                $this->assertStringStartsWith('El paquete no está en ruta de entrega.', $e->getMessage());
            }

            $this->assertSame($status, $package->fresh()->current_status);
        }
    }

    public function test_the_api_confirms_with_the_pin_and_never_exposes_it(): void
    {
        [$user, $driver] = $this->driverWithUser();
        $package = $this->outForDelivery($driver);

        $token = $this->postJson('/api/driver/login', [
            'email' => $user->email,
            'password' => 'password-seguro',
            'device_name' => 'telefono-de-prueba',
        ])->json('token');

        $headers = ['Authorization' => "Bearer {$token}"];

        $this->getJson("/api/driver/packages/{$package->id}", $headers)
            ->assertOk()
            ->assertJsonPath('package.has_delivery_pin', true)
            ->assertJsonPath('package.delivery_pin_attempts_left', Package::DELIVERY_PIN_MAX_ATTEMPTS)
            ->assertJsonMissingPath('package.delivery_pin_hash');

        $this->postJson("/api/driver/packages/{$package->id}/complete-delivery", [
            'receiver_name' => 'María Gómez',
            'delivery_pin' => '111111',
        ], $headers)
            ->assertUnprocessable()
            ->assertJsonPath('message', 'PIN incorrecto. Te quedan 4 intento(s).')
            ->assertJsonPath('package.delivery_pin_attempts_left', 4);

        $this->postJson("/api/driver/packages/{$package->id}/complete-delivery", [
            'receiver_name' => 'María Gómez',
            'delivery_pin' => self::PIN,
        ], $headers)
            ->assertOk()
            ->assertJsonPath('package.current_status', Package::STATUS_ENTREGADO)
            ->assertJsonPath('package.delivery_confirmation_method', Package::DELIVERY_CONFIRMATION_PIN);
    }

    public function test_the_api_without_pin_requires_the_id_and_a_photo(): void
    {
        Storage::fake('documents');

        [$user, $driver] = $this->driverWithUser();
        $package = $this->outForDelivery($driver);

        $token = $this->postJson('/api/driver/login', [
            'email' => $user->email,
            'password' => 'password-seguro',
            'device_name' => 'telefono-de-prueba',
        ])->json('token');

        $headers = ['Authorization' => "Bearer {$token}", 'Accept' => 'application/json'];

        $this->post("/api/driver/packages/{$package->id}/complete-delivery", [
            'receiver_name' => 'María Gómez',
        ], $headers)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['receiver_id_doc', 'photo']);

        $this->post("/api/driver/packages/{$package->id}/complete-delivery", [
            'receiver_name' => 'María Gómez',
            'receiver_id_doc' => 'V-87654321',
            'photo' => UploadedFile::fake()->image('entrega.jpg'),
        ], $headers)
            ->assertOk()
            ->assertJsonPath('package.delivery_confirmation_method', Package::DELIVERY_CONFIRMATION_ID_DOC);

        Storage::disk('documents')->assertExists($package->fresh()->delivery_photo_path);
    }

    public function test_without_pin_an_authorized_third_party_can_receive_with_id_copies(): void
    {
        [, $driver] = $this->driverWithUser();
        $package = $this->outForDelivery($driver);

        try {
            $this->complete($package, $driver, [
                'receiverName' => 'Pedro Gómez',
                'receiverIdDoc' => 'V-11111111',
                'receivedByThirdParty' => true,
                'thirdPartyIdPhotoPath' => 'third-party-ids/tercero.jpg',
            ]);
            $this->fail('Se entregó a un tercero sin la copia de la cédula del destinatario.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('copia de la cédula del destinatario', $e->getMessage());
        }

        $delivered = $this->complete($package, $driver, [
            'receiverName' => 'Pedro Gómez',
            'receiverIdDoc' => 'V-11111111',
            'receivedByThirdParty' => true,
            'thirdPartyIdPhotoPath' => 'third-party-ids/tercero.jpg',
            'recipientIdCopyPath' => 'third-party-ids/copia.jpg',
        ]);

        $this->assertSame(Package::STATUS_ENTREGADO, $delivered->current_status);
        $this->assertSame(Package::DELIVERY_CONFIRMATION_THIRD_PARTY, $delivered->delivery_confirmation_method);
        $this->assertTrue($delivered->received_by_third_party);
        $this->assertSame('V-11111111', $delivered->receiver_id_doc);
        $this->assertSame('third-party-ids/copia.jpg', $delivered->recipient_id_copy_path);
    }

    public function test_the_api_accepts_a_third_party_with_both_id_photos(): void
    {
        Storage::fake('documents');

        [$user, $driver] = $this->driverWithUser();
        $package = $this->outForDelivery($driver);

        $token = $this->postJson('/api/driver/login', [
            'email' => $user->email,
            'password' => 'password-seguro',
            'device_name' => 'telefono-de-prueba',
        ])->json('token');

        $headers = ['Authorization' => "Bearer {$token}", 'Accept' => 'application/json'];

        $this->post("/api/driver/packages/{$package->id}/complete-delivery", [
            'receiver_name' => 'Pedro Gómez',
            'receiver_id_doc' => 'V-11111111',
            'received_by_third_party' => '1',
        ], $headers)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['third_party_id_photo', 'recipient_id_copy'])
            ->assertJsonMissingValidationErrors(['photo']);

        $this->post("/api/driver/packages/{$package->id}/complete-delivery", [
            'receiver_name' => 'Pedro Gómez',
            'receiver_id_doc' => 'V-11111111',
            'received_by_third_party' => '1',
            'third_party_id_photo' => UploadedFile::fake()->image('tercero.jpg'),
            'recipient_id_copy' => UploadedFile::fake()->image('copia.jpg'),
        ], $headers)
            ->assertOk()
            ->assertJsonPath('package.received_by_third_party', true)
            ->assertJsonPath('package.delivery_confirmation_method', Package::DELIVERY_CONFIRMATION_THIRD_PARTY);

        Storage::disk('documents')->assertExists($package->fresh()->third_party_id_photo_path);
    }

    public function test_only_admins_can_see_the_id_copies_and_payment_proof(): void
    {
        Storage::fake('documents');
        Storage::disk('documents')->put('third-party-ids/copia.jpg', 'img');

        [$driverUser, $driver] = $this->driverWithUser();
        $package = $this->outForDelivery($driver, ['recipient_id_copy_path' => 'third-party-ids/copia.jpg']);

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN_PRINCIPAL, 'status' => User::STATUS_ACTIVE]);

        $this->actingAs($admin)->get(route('packages.recipient-id-copy', $package))->assertOk();
        $this->actingAs($driverUser)->get(route('packages.recipient-id-copy', $package))->assertForbidden();
        $this->actingAs($package->ally->user)->get(route('packages.recipient-id-copy', $package))->assertForbidden();
        $this->actingAs($admin)->get(route('packages.cod-payment-proof', $package))->assertNotFound();
    }
}
