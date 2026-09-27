<?php

namespace Tests\Feature;

use App\Models\Ally;
use App\Models\Driver;
use App\Models\Emprendedor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Concerns\CreatesTestEmprendedores;
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
    use CreatesTestEmprendedores;
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
            'cedula_back_photo_path' => UploadedFile::fake()->image('cedula-reverso.jpg')->store('drivers', 'documents'),
            'selfie_photo_path' => UploadedFile::fake()->image('selfie.jpg')->store('drivers', 'documents'),
            'vehicle_photo_path' => UploadedFile::fake()->image('vehiculo.jpg')->store('drivers', 'documents'),
            'plate_photo_path' => UploadedFile::fake()->image('placa.jpg')->store('drivers', 'documents'),
            'vehicle_registration_photo_path' => UploadedFile::fake()->image('carnet.jpg')->store('drivers', 'documents'),
        ]);
    }

    private function createEmprendedorWithDocuments(): Emprendedor
    {
        Storage::fake('documents');

        return $this->createEmprendedor([
            'cedula_front_photo_path' => UploadedFile::fake()->image('cedula-frente.jpg')->store('emprendedores', 'documents'),
            'cedula_back_photo_path' => UploadedFile::fake()->image('cedula-reverso.jpg')->store('emprendedores', 'documents'),
            'rif_document_path' => UploadedFile::fake()->image('rif.jpg')->store('emprendedores', 'documents'),
            'product_or_workspace_photo_path' => UploadedFile::fake()->image('productos.jpg')->store('emprendedores', 'documents'),
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

    /*
    |--------------------------------------------------------------------------
    | FASE 3 — nuevos documentos (cédula reverso, selfie, foto de
    | vehículo, foto de la placa para Repartidor; cédula reverso para
    | Aliado; los 4 de Emprendedor). Mismo criterio de autorización que
    | los documentos ya existentes.
    |--------------------------------------------------------------------------
    */

    public function test_guest_cannot_view_any_of_the_new_driver_documents(): void
    {
        $driver = $this->createDriverWithDocuments();

        $this->get(route('drivers.documents.cedula-back', $driver))->assertRedirect(route('login'));
        $this->get(route('drivers.documents.selfie', $driver))->assertRedirect(route('login'));
        $this->get(route('drivers.documents.vehicle-photo', $driver))->assertRedirect(route('login'));
        $this->get(route('drivers.documents.plate-photo', $driver))->assertRedirect(route('login'));
    }

    public function test_driver_owner_can_view_their_own_new_documents(): void
    {
        $driver = $this->createDriverWithDocuments();

        $this->actingAs($driver->user)->get(route('drivers.documents.cedula-back', $driver))->assertOk();
        $this->actingAs($driver->user)->get(route('drivers.documents.selfie', $driver))->assertOk();
        $this->actingAs($driver->user)->get(route('drivers.documents.vehicle-photo', $driver))->assertOk();
        $this->actingAs($driver->user)->get(route('drivers.documents.plate-photo', $driver))->assertOk();
    }

    public function test_another_driver_cannot_view_someone_elses_selfie(): void
    {
        $driver = $this->createDriverWithDocuments();
        $otherDriver = $this->createDriverWithDocuments();

        $this->actingAs($otherDriver->user)
            ->get(route('drivers.documents.selfie', $driver))
            ->assertForbidden();
    }

    public function test_guest_cannot_view_the_allys_cedula_back_document(): void
    {
        $ally = $this->createAllyWithStorefrontPhoto();
        $ally->update(['owner_id_back_document_path' => UploadedFile::fake()->image('cedula-reverso.jpg')->store('allies', 'documents')]);

        $this->get(route('allies.documents.owner-id-back', $ally))->assertRedirect(route('login'));
    }

    public function test_ally_owner_can_view_their_own_cedula_back_document(): void
    {
        $ally = $this->createAllyWithStorefrontPhoto();
        $ally->update(['owner_id_back_document_path' => UploadedFile::fake()->image('cedula-reverso.jpg')->store('allies', 'documents')]);

        $this->actingAs($ally->user)
            ->get(route('allies.documents.owner-id-back', $ally))
            ->assertOk();
    }

    public function test_guest_cannot_view_any_emprendedor_document(): void
    {
        $emprendedor = $this->createEmprendedorWithDocuments();

        $this->get(route('emprendedores.documents.cedula-front', $emprendedor))->assertRedirect(route('login'));
        $this->get(route('emprendedores.documents.cedula-back', $emprendedor))->assertRedirect(route('login'));
        $this->get(route('emprendedores.documents.rif', $emprendedor))->assertRedirect(route('login'));
        $this->get(route('emprendedores.documents.product-or-workspace', $emprendedor))->assertRedirect(route('login'));
    }

    public function test_emprendedor_owner_can_view_their_own_documents(): void
    {
        $emprendedor = $this->createEmprendedorWithDocuments();

        $this->actingAs($emprendedor->user)->get(route('emprendedores.documents.cedula-front', $emprendedor))->assertOk();
        $this->actingAs($emprendedor->user)->get(route('emprendedores.documents.cedula-back', $emprendedor))->assertOk();
        $this->actingAs($emprendedor->user)->get(route('emprendedores.documents.rif', $emprendedor))->assertOk();
        $this->actingAs($emprendedor->user)->get(route('emprendedores.documents.product-or-workspace', $emprendedor))->assertOk();
    }

    public function test_another_emprendedor_cannot_view_someone_elses_rif_document(): void
    {
        $emprendedor = $this->createEmprendedorWithDocuments();
        $otherEmprendedor = $this->createEmprendedor();

        $this->actingAs($otherEmprendedor->user)
            ->get(route('emprendedores.documents.rif', $emprendedor))
            ->assertForbidden();
    }

    public function test_admin_can_view_emprendedor_product_or_workspace_photo(): void
    {
        $emprendedor = $this->createEmprendedorWithDocuments();

        $this->actingAs($this->createAdmin())
            ->get(route('emprendedores.documents.product-or-workspace', $emprendedor))
            ->assertOk();
    }
}
