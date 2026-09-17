<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\AlliesManager;
use App\Livewire\Admin\DriversApprovalManager;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
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
}
