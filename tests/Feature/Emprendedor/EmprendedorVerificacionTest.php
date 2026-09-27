<?php

namespace Tests\Feature\Emprendedor;

use App\Livewire\Emprendedor\Verificacion;
use App\Models\Ally;
use App\Models\Emprendedor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Fase 4: pantalla de auto-servicio donde el emprendedor completa o
 * corrige su verificación. A propósito accesible SIN 'account.approved'
 * (ver routes/web.php), a diferencia de emprendedor.perfil que sí lo
 * exige.
 */
class EmprendedorVerificacionTest extends TestCase
{
    use RefreshDatabase;

    private function createPendingEmprendedor(): Emprendedor
    {
        $allyUser = User::factory()->create(['role' => User::ROLE_ALIADO]);

        $ally = Ally::create([
            'user_id' => $allyUser->id,
            'business_name' => 'Agencia de Retiro',
            'rif' => 'J-33333333-3',
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
            'address' => 'Av. Principal',
            'commission_percentage' => 10,
            'status' => Ally::STATUS_ACTIVE,
            'verification_status' => Ally::VERIFICATION_VERIFIED,
        ]);

        $user = User::factory()->create([
            'role' => User::ROLE_EMPRENDEDOR,
            'status' => User::STATUS_ACTIVE,
        ]);

        return Emprendedor::create([
            'user_id' => $user->id,
            'pickup_ally_id' => $ally->id,
            'business_name' => 'Tienda de Prueba',
            'document_id' => 'V-11111111',
            'status' => Emprendedor::STATUS_PENDING,
            'verification_status' => Emprendedor::VERIFICATION_PENDING,
        ]);
    }

    public function test_a_pending_emprendedor_can_reach_the_verification_screen_without_being_approved(): void
    {
        $emprendedor = $this->createPendingEmprendedor();

        $this->actingAs($emprendedor->user)
            ->get(route('emprendedor.verificacion'))
            ->assertOk();
    }

    public function test_submitting_complete_data_and_documents_moves_verification_to_in_review(): void
    {
        Storage::fake('documents');

        $emprendedor = $this->createPendingEmprendedor();

        Livewire::actingAs($emprendedor->user)
            ->test(Verificacion::class)
            ->set('cedula', 'V-12345678')
            ->set('city', 'Caracas')
            ->set('state', 'Distrito Capital')
            ->set('business_name', 'Tienda Verificada')
            ->set('descripcion', 'Vendemos artesanías.')
            ->set('document_id', 'J-44444444-4')
            ->set('cedula_front_photo', UploadedFile::fake()->image('cedula-frente.jpg'))
            ->set('cedula_back_photo', UploadedFile::fake()->image('cedula-reverso.jpg'))
            ->set('rif_document', UploadedFile::fake()->image('rif.jpg'))
            ->set('product_or_workspace_photo', UploadedFile::fake()->image('productos.jpg'))
            ->call('submit')
            ->assertHasNoErrors();

        $emprendedor->refresh();

        $this->assertSame('V-12345678', $emprendedor->cedula);
        $this->assertSame('Tienda Verificada', $emprendedor->business_name);
        $this->assertSame('J-44444444-4', $emprendedor->document_id);
        $this->assertSame(Emprendedor::VERIFICATION_IN_REVIEW, $emprendedor->verification_status);
        $this->assertNull($emprendedor->verification_rejection_reason);
        $this->assertNotNull($emprendedor->cedula_front_photo_path);
        $this->assertNotNull($emprendedor->cedula_back_photo_path);
        $this->assertNotNull($emprendedor->rif_document_path);
        $this->assertNotNull($emprendedor->product_or_workspace_photo_path);

        // status operativo no se toca desde este formulario.
        $this->assertSame(Emprendedor::STATUS_PENDING, $emprendedor->status);
    }

    public function test_missing_a_required_document_fails_validation(): void
    {
        Storage::fake('documents');

        $emprendedor = $this->createPendingEmprendedor();

        Livewire::actingAs($emprendedor->user)
            ->test(Verificacion::class)
            ->set('cedula', 'V-12345678')
            ->set('city', 'Caracas')
            ->set('state', 'Distrito Capital')
            ->set('business_name', 'Tienda Verificada')
            ->set('document_id', 'J-44444444-4')
            ->call('submit')
            ->assertHasErrors(['cedula_front_photo', 'cedula_back_photo', 'rif_document', 'product_or_workspace_photo']);

        $this->assertSame(Emprendedor::VERIFICATION_PENDING, $emprendedor->fresh()->verification_status);
    }

    public function test_resubmitting_after_a_rejection_clears_the_reason_and_moves_to_in_review(): void
    {
        Storage::fake('documents');

        $emprendedor = $this->createPendingEmprendedor();
        $emprendedor->update([
            'verification_status' => Emprendedor::VERIFICATION_REJECTED,
            'verification_rejection_reason' => 'La foto del RIF no es legible.',
            'cedula_front_photo_path' => 'emprendedores/cedula-frente-existente.jpg',
            'cedula_back_photo_path' => 'emprendedores/cedula-reverso-existente.jpg',
            'rif_document_path' => 'emprendedores/rif-existente.jpg',
            'product_or_workspace_photo_path' => 'emprendedores/productos-existente.jpg',
        ]);

        Livewire::actingAs($emprendedor->user)
            ->test(Verificacion::class)
            ->set('cedula', 'V-12345678')
            ->set('city', 'Caracas')
            ->set('state', 'Distrito Capital')
            ->set('rif_document', UploadedFile::fake()->image('rif-nuevo.jpg'))
            ->call('submit')
            ->assertHasNoErrors();

        $emprendedor->refresh();

        $this->assertSame(Emprendedor::VERIFICATION_IN_REVIEW, $emprendedor->verification_status);
        $this->assertNull($emprendedor->verification_rejection_reason);
    }

    public function test_a_verified_emprendedor_cannot_move_themselves_back_to_in_review(): void
    {
        $emprendedor = $this->createPendingEmprendedor();
        $emprendedor->update([
            'status' => Emprendedor::STATUS_ACTIVE,
            'verification_status' => Emprendedor::VERIFICATION_VERIFIED,
        ]);

        Livewire::actingAs($emprendedor->user)
            ->test(Verificacion::class)
            ->call('submit');

        $this->assertSame(Emprendedor::VERIFICATION_VERIFIED, $emprendedor->fresh()->verification_status);
    }
}
