<?php

namespace Tests\Feature\Driver;

use App\Livewire\Driver\Verificacion;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Fase 4: pantalla de auto-servicio donde el repartidor completa o
 * corrige su verificación. A propósito accesible SIN 'account.approved'
 * (ver routes/web.php) — si no, un repartidor PENDIENTE nunca podría
 * llegar aquí para completarla.
 */
class DriverVerificacionTest extends TestCase
{
    use RefreshDatabase;

    private function createPendingDriverUser(): User
    {
        $user = User::factory()->create([
            'role' => User::ROLE_REPARTIDOR,
            'status' => User::STATUS_ACTIVE,
        ]);

        Driver::factory()->create([
            'user_id' => $user->id,
            'status' => Driver::STATUS_PENDING,
            'verification_status' => Driver::VERIFICATION_PENDING,
            'city' => null,
            'state' => null,
        ]);

        return $user;
    }

    public function test_a_pending_driver_can_reach_the_verification_screen_without_being_approved(): void
    {
        $user = $this->createPendingDriverUser();

        $this->actingAs($user)
            ->get(route('repartidor.verificacion'))
            ->assertOk();
    }

    public function test_submitting_complete_data_and_documents_moves_verification_to_in_review(): void
    {
        Storage::fake('documents');

        $user = $this->createPendingDriverUser();
        $driver = $user->driver;

        Livewire::actingAs($user)
            ->test(Verificacion::class)
            ->set('cedula', 'V-12345678')
            ->set('city', 'Caracas')
            ->set('state', 'Distrito Capital')
            ->set('vehicle_plate', 'ABC-123')
            ->set('vehicle_type', 'Moto')
            ->set('id_photo', UploadedFile::fake()->image('cedula-frente.jpg'))
            ->set('cedula_back_photo', UploadedFile::fake()->image('cedula-reverso.jpg'))
            ->set('selfie_photo', UploadedFile::fake()->image('selfie.jpg'))
            ->set('license_photo', UploadedFile::fake()->image('licencia.jpg'))
            ->set('vehicle_photo', UploadedFile::fake()->image('vehiculo.jpg'))
            ->set('plate_photo', UploadedFile::fake()->image('placa.jpg'))
            ->call('submit')
            ->assertHasNoErrors();

        $driver->refresh();

        $this->assertSame('V-12345678', $driver->cedula);
        $this->assertSame('Caracas', $driver->city);
        $this->assertSame(Driver::VERIFICATION_IN_REVIEW, $driver->verification_status);
        $this->assertNull($driver->verification_rejection_reason);
        $this->assertNotNull($driver->id_photo_path);
        $this->assertNotNull($driver->cedula_back_photo_path);
        $this->assertNotNull($driver->selfie_photo_path);
        $this->assertNotNull($driver->vehicle_photo_path);
        $this->assertNotNull($driver->plate_photo_path);

        // status operativo no se toca desde este formulario.
        $this->assertSame(Driver::STATUS_PENDING, $driver->status);
    }

    public function test_missing_a_required_document_fails_validation(): void
    {
        Storage::fake('documents');

        $user = $this->createPendingDriverUser();

        Livewire::actingAs($user)
            ->test(Verificacion::class)
            ->set('cedula', 'V-12345678')
            ->set('city', 'Caracas')
            ->set('state', 'Distrito Capital')
            ->set('vehicle_plate', 'ABC-123')
            ->set('vehicle_type', 'Moto')
            // Ningún documento cargado.
            ->call('submit')
            ->assertHasErrors(['id_photo', 'cedula_back_photo', 'selfie_photo', 'license_photo', 'vehicle_photo', 'plate_photo']);

        $this->assertSame(Driver::VERIFICATION_PENDING, $user->driver->fresh()->verification_status);
    }

    public function test_does_not_require_re_uploading_a_document_that_already_exists(): void
    {
        Storage::fake('documents');

        $user = $this->createPendingDriverUser();
        $driver = $user->driver;

        $driver->update([
            'id_photo_path' => 'drivers/cedula-existente.jpg',
            'cedula_back_photo_path' => 'drivers/cedula-reverso-existente.jpg',
            'selfie_photo_path' => 'drivers/selfie-existente.jpg',
            'license_photo_path' => 'drivers/licencia-existente.jpg',
            'vehicle_photo_path' => 'drivers/vehiculo-existente.jpg',
            'plate_photo_path' => 'drivers/placa-existente.jpg',
        ]);

        Livewire::actingAs($user)
            ->test(Verificacion::class)
            ->set('cedula', 'V-12345678')
            ->set('city', 'Caracas')
            ->set('state', 'Distrito Capital')
            ->set('vehicle_plate', 'ABC-123')
            ->set('vehicle_type', 'Moto')
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertSame(Driver::VERIFICATION_IN_REVIEW, $driver->fresh()->verification_status);
        // Las rutas existentes se conservan porque no se reemplazaron.
        $this->assertSame('drivers/cedula-existente.jpg', $driver->fresh()->id_photo_path);
    }

    public function test_resubmitting_after_a_rejection_clears_the_reason_and_moves_to_in_review(): void
    {
        Storage::fake('documents');

        $user = $this->createPendingDriverUser();
        $driver = $user->driver;

        $driver->update([
            'verification_status' => Driver::VERIFICATION_REJECTED,
            'verification_rejection_reason' => 'La foto de la cédula está borrosa.',
            'id_photo_path' => 'drivers/cedula-existente.jpg',
            'cedula_back_photo_path' => 'drivers/cedula-reverso-existente.jpg',
            'selfie_photo_path' => 'drivers/selfie-existente.jpg',
            'license_photo_path' => 'drivers/licencia-existente.jpg',
            'vehicle_photo_path' => 'drivers/vehiculo-existente.jpg',
            'plate_photo_path' => 'drivers/placa-existente.jpg',
        ]);

        Livewire::actingAs($user)
            ->test(Verificacion::class)
            ->set('cedula', 'V-12345678')
            ->set('city', 'Caracas')
            ->set('state', 'Distrito Capital')
            ->set('vehicle_plate', 'ABC-123')
            ->set('vehicle_type', 'Moto')
            ->set('id_photo', UploadedFile::fake()->image('cedula-nueva.jpg'))
            ->call('submit')
            ->assertHasNoErrors();

        $driver->refresh();

        $this->assertSame(Driver::VERIFICATION_IN_REVIEW, $driver->verification_status);
        $this->assertNull($driver->verification_rejection_reason);
    }

    public function test_a_verified_driver_cannot_move_themselves_back_to_in_review(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_REPARTIDOR,
            'status' => User::STATUS_ACTIVE,
        ]);

        $driver = Driver::factory()->create([
            'user_id' => $user->id,
            'status' => Driver::STATUS_ACTIVE,
            'verification_status' => Driver::VERIFICATION_VERIFIED,
        ]);

        Livewire::actingAs($user)
            ->test(Verificacion::class)
            ->call('submit');

        $this->assertSame(Driver::VERIFICATION_VERIFIED, $driver->fresh()->verification_status);
    }
}
