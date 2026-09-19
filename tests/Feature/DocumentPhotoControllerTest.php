<?php

namespace Tests\Feature;

use App\Models\Ally;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Los documentos de identidad (fachada de agencia, licencia, cédula,
 * carnet de circulación, evidencia de entrega) se guardan en el disco
 * privado y solo deben ser accesibles para: el Admin, el propio dueño
 * del documento, o (para evidencia de entrega) el repartidor asignado
 * al paquete. Ver DocumentPhotoController.
 */
class DocumentPhotoControllerTest extends TestCase
{
    use CreatesTestPackages;
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_ADMIN_PRINCIPAL,
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    private function createAllyWithStorefrontPhoto(): Ally
    {
        Storage::fake('documents');

        $ally = $this->createAlly();

        $ally->update([
            'storefront_photo_path' => UploadedFile::fake()
                ->image('fachada.jpg')
                ->store('allies', 'documents'),
        ]);

        return $ally;
    }

    private function createDriverWithDocuments(): Driver
    {
        Storage::fake('documents');

        $user = User::factory()->create([
            'role' => User::ROLE_REPARTIDOR,
            'status' => User::STATUS_ACTIVE,
        ]);

        return Driver::factory()->create([
            'user_id' => $user->id,
            'status' => Driver::STATUS_ACTIVE,
            'license_photo_path' => UploadedFile::fake()->image('licencia.jpg')->store('drivers', 'documents'),
            'id_photo_path' => UploadedFile::fake()->image('cedula.jpg')->store('drivers', 'documents'),
            'vehicle_registration_photo_path' => UploadedFile::fake()->image('carnet.jpg')->store('drivers', 'documents'),
        ]);
    }

    public function test_guest_cannot_view_any_document(): void
    {
        $ally = $this->createAllyWithStorefrontPhoto();

        $this->get(route('allies.documents.storefront', $ally))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_view_ally_storefront_photo(): void
    {
        $ally = $this->createAllyWithStorefrontPhoto();

        $this->actingAs($this->createAdmin())
            ->get(route('allies.documents.storefront', $ally))
            ->assertOk();
    }

    public function test_ally_owner_can_view_their_own_storefront_photo(): void
    {
        $ally = $this->createAllyWithStorefrontPhoto();

        $this->actingAs($ally->user)
            ->get(route('allies.documents.storefront', $ally))
            ->assertOk();
    }

    public function test_another_ally_cannot_view_someone_elses_storefront_photo(): void
    {
        $ally = $this->createAllyWithStorefrontPhoto();
        $otherAlly = $this->createAlly();

        $this->actingAs($otherAlly->user)
            ->get(route('allies.documents.storefront', $ally))
            ->assertForbidden();
    }

    public function test_driver_owner_can_view_their_own_license_photo(): void
    {
        $driver = $this->createDriverWithDocuments();

        $this->actingAs($driver->user)
            ->get(route('drivers.documents.license', $driver))
            ->assertOk();
    }

    public function test_another_driver_cannot_view_someone_elses_id_photo(): void
    {
        $driver = $this->createDriverWithDocuments();
        $otherDriver = $this->createDriverWithDocuments();

        $this->actingAs($otherDriver->user)
            ->get(route('drivers.documents.id', $driver))
            ->assertForbidden();
    }

    public function test_a_client_cannot_view_a_drivers_vehicle_registration_photo(): void
    {
        $driver = $this->createDriverWithDocuments();

        $client = User::factory()->create([
            'role' => User::ROLE_CLIENTE,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->actingAs($client)
            ->get(route('drivers.documents.vehicle-registration', $driver))
            ->assertForbidden();
    }

    public function test_assigned_driver_can_view_package_delivery_evidence(): void
    {
        Storage::fake('documents');

        $ally = $this->createAlly();
        $driver = $this->createDriverWithDocuments();

        $package = $this->createPackage($ally, [
            'driver_id' => $driver->id,
            'delivery_photo_path' => UploadedFile::fake()->image('evidencia.jpg')->store('delivery-evidence', 'documents'),
        ]);

        $this->actingAs($driver->user)
            ->get(route('packages.delivery-evidence', $package))
            ->assertOk();
    }

    public function test_unrelated_driver_cannot_view_package_delivery_evidence(): void
    {
        Storage::fake('documents');

        $ally = $this->createAlly();
        $driver = $this->createDriverWithDocuments();
        $otherDriver = $this->createDriverWithDocuments();

        $package = $this->createPackage($ally, [
            'driver_id' => $driver->id,
            'delivery_photo_path' => UploadedFile::fake()->image('evidencia.jpg')->store('delivery-evidence', 'documents'),
        ]);

        $this->actingAs($otherDriver->user)
            ->get(route('packages.delivery-evidence', $package))
            ->assertForbidden();
    }

    public function test_returns_404_when_document_path_is_missing(): void
    {
        $ally = $this->createAlly();

        $this->actingAs($this->createAdmin())
            ->get(route('allies.documents.storefront', $ally))
            ->assertNotFound();
    }
}
