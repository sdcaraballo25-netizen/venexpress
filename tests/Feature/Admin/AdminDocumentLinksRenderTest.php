<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\AlliesManager;
use App\Livewire\Admin\DriversApprovalManager;
use App\Livewire\Admin\EmprendedoresApprovalManager;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestEmprendedores;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Cubre un punto ciego real: ningún test previo renderizaba las filas
 * de AlliesManager/DriversApprovalManager con storefront_photo_path /
 * license_photo_path / etc. realmente presentes, así que un error en
 * los enlaces a los documentos privados (route() mal formado, nombre
 * de ruta incorrecto) habría pasado desapercibido. Ver
 * DocumentPhotoController y las rutas allies.documents.* /
 * drivers.documents.*.
 */
class AdminDocumentLinksRenderTest extends TestCase
{
    use CreatesTestEmprendedores;
    use CreatesTestPackages;
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_ADMIN_PRINCIPAL,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);
    }

    public function test_allies_manager_renders_storefront_photo_link_without_error(): void
    {
        $ally = $this->createAlly([
            'storefront_photo_path' => 'allies/fachada-test.jpg',
        ]);

        Livewire::actingAs($this->createAdmin())
            ->test(AlliesManager::class)
            ->assertOk()
            ->assertSee(route('allies.documents.storefront', $ally), false);
    }

    public function test_drivers_approval_manager_renders_document_links_without_error(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_REPARTIDOR,
            'status' => User::STATUS_ACTIVE,
        ]);

        $driver = Driver::factory()->create([
            'user_id' => $user->id,
            'status' => Driver::STATUS_PENDING,
            'license_photo_path' => 'drivers/licencia-test.jpg',
            'id_photo_path' => 'drivers/cedula-test.jpg',
            'vehicle_registration_photo_path' => 'drivers/carnet-test.jpg',
        ]);

        Livewire::actingAs($this->createAdmin())
            ->test(DriversApprovalManager::class)
            ->assertOk()
            ->assertSee(route('drivers.documents.license', $driver), false)
            ->assertSee(route('drivers.documents.id', $driver), false)
            ->assertSee(route('drivers.documents.vehicle-registration', $driver), false);
    }

    /**
     * Fase 3: el modal de detalle del repartidor (nuevo) debe listar
     * también los documentos agregados en esta fase (cédula reverso,
     * selfie, foto de vehículo, foto de la placa).
     */
    public function test_drivers_approval_manager_details_modal_renders_new_document_links(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_REPARTIDOR,
            'status' => User::STATUS_ACTIVE,
        ]);

        $driver = Driver::factory()->create([
            'user_id' => $user->id,
            'status' => Driver::STATUS_PENDING,
            'cedula_back_photo_path' => 'drivers/cedula-reverso-test.jpg',
            'selfie_photo_path' => 'drivers/selfie-test.jpg',
            'vehicle_photo_path' => 'drivers/vehiculo-test.jpg',
            'plate_photo_path' => 'drivers/placa-test.jpg',
        ]);

        Livewire::actingAs($this->createAdmin())
            ->test(DriversApprovalManager::class)
            ->call('viewDetails', $driver->id)
            ->assertOk()
            ->assertSee(route('drivers.documents.cedula-back', $driver), false)
            ->assertSee(route('drivers.documents.selfie', $driver), false)
            ->assertSee(route('drivers.documents.vehicle-photo', $driver), false)
            ->assertSee(route('drivers.documents.plate-photo', $driver), false);
    }

    /**
     * Fase 3: antes, esta pantalla solo mostraba business_name y
     * document_id. Ahora el modal de detalle debe mostrar los datos
     * completos y los 4 documentos de verificación.
     */
    public function test_emprendedores_approval_manager_details_modal_renders_full_data_and_documents(): void
    {
        $emprendedor = $this->createEmprendedor([
            'cedula' => 'V-12345678',
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
            'cedula_front_photo_path' => 'emprendedores/cedula-frente-test.jpg',
            'cedula_back_photo_path' => 'emprendedores/cedula-reverso-test.jpg',
            'rif_document_path' => 'emprendedores/rif-test.jpg',
            'product_or_workspace_photo_path' => 'emprendedores/productos-test.jpg',
        ]);

        Livewire::actingAs($this->createAdmin())
            ->test(EmprendedoresApprovalManager::class)
            ->call('viewDetails', $emprendedor->id)
            ->assertOk()
            ->assertSee('V-12345678')
            ->assertSee('Caracas')
            ->assertSee(route('emprendedores.documents.cedula-front', $emprendedor), false)
            ->assertSee(route('emprendedores.documents.cedula-back', $emprendedor), false)
            ->assertSee(route('emprendedores.documents.rif', $emprendedor), false)
            ->assertSee(route('emprendedores.documents.product-or-workspace', $emprendedor), false);
    }
}
