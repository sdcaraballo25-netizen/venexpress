<?php

namespace Tests\Feature\Ally;

use App\Livewire\Ally\Verificacion;
use App\Models\Ally;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Fase 4: pantalla de auto-servicio donde el aliado completa o
 * corrige su verificación. A propósito accesible SIN 'account.approved'
 * (ver routes/web.php) — está declarada fuera del Route::prefix('ally')
 * ->group() que sí lo aplica al resto de rutas de aliado.
 */
class AllyVerificacionTest extends TestCase
{
    use RefreshDatabase;

    private function createPendingAllyUser(): Ally
    {
        $user = User::factory()->create([
            'role' => User::ROLE_ALIADO,
            'status' => User::STATUS_ACTIVE,
        ]);

        return Ally::create([
            'user_id' => $user->id,
            'business_name' => 'Agencia de Prueba',
            'rif' => 'J-11111111-1',
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
            'address' => 'Av. Principal',
            'commission_percentage' => 10,
            'status' => Ally::STATUS_PENDING,
            'verification_status' => Ally::VERIFICATION_PENDING,
        ]);
    }

    public function test_a_pending_ally_can_reach_the_verification_screen_without_being_approved(): void
    {
        $ally = $this->createPendingAllyUser();

        $this->actingAs($ally->user)
            ->get(route('ally.verificacion'))
            ->assertOk();
    }

    public function test_ally_staff_role_cannot_access_the_ally_owners_verification_screen(): void
    {
        $staff = User::factory()->create([
            'role' => User::ROLE_ALIADO_TAQUILLA,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->actingAs($staff)
            ->get(route('ally.verificacion'))
            ->assertForbidden();
    }

    public function test_submitting_complete_data_and_documents_moves_verification_to_in_review(): void
    {
        Storage::fake('documents');

        $ally = $this->createPendingAllyUser();

        Livewire::actingAs($ally->user)
            ->test(Verificacion::class)
            ->set('business_name', 'Agencia Verificada C.A.')
            ->set('rif', 'J-22222222-2')
            ->set('address', 'Av. Bolívar')
            ->set('city', 'Valencia')
            ->set('state', 'Carabobo')
            ->set('owner_id_document', UploadedFile::fake()->image('cedula-frente.jpg'))
            ->set('owner_id_back_document', UploadedFile::fake()->image('cedula-reverso.jpg'))
            ->set('rif_document', UploadedFile::fake()->image('rif.jpg'))
            ->set('storefront_photo', UploadedFile::fake()->image('fachada.jpg'))
            ->call('submit')
            ->assertHasNoErrors();

        $ally->refresh();

        $this->assertSame('Agencia Verificada C.A.', $ally->business_name);
        $this->assertSame('J-22222222-2', $ally->rif);
        $this->assertSame(Ally::VERIFICATION_IN_REVIEW, $ally->verification_status);
        $this->assertNull($ally->verification_rejection_reason);
        $this->assertNotNull($ally->owner_id_document_path);
        $this->assertNotNull($ally->owner_id_back_document_path);
        $this->assertNotNull($ally->rif_document_path);
        $this->assertNotNull($ally->storefront_photo_path);

        // status operativo no se toca desde este formulario.
        $this->assertSame(Ally::STATUS_PENDING, $ally->status);
    }

    public function test_missing_a_required_document_fails_validation(): void
    {
        Storage::fake('documents');

        $ally = $this->createPendingAllyUser();

        Livewire::actingAs($ally->user)
            ->test(Verificacion::class)
            ->set('business_name', 'Agencia Verificada C.A.')
            ->set('rif', 'J-22222222-2')
            ->set('address', 'Av. Bolívar')
            ->set('city', 'Valencia')
            ->set('state', 'Carabobo')
            ->call('submit')
            ->assertHasErrors(['owner_id_document', 'owner_id_back_document', 'rif_document', 'storefront_photo']);

        $this->assertSame(Ally::VERIFICATION_PENDING, $ally->fresh()->verification_status);
    }

    /**
     * Antes esta cobertura vivía en el registro; ahora que los
     * documentos se suben desde "Mi Verificación" (Registro ≠
     * Verificación), la lista blanca de 'mimes' sigue rechazando
     * extensiones peligrosas (.exe) aquí.
     */
    public function test_rejects_a_dangerous_file_extension_for_verification_documents(): void
    {
        Storage::fake('documents');

        $ally = $this->createPendingAllyUser();

        Livewire::actingAs($ally->user)
            ->test(Verificacion::class)
            ->set('business_name', 'Agencia Verificada C.A.')
            ->set('rif', 'J-22222222-2')
            ->set('address', 'Av. Bolívar')
            ->set('city', 'Valencia')
            ->set('state', 'Carabobo')
            ->set('owner_id_document', UploadedFile::fake()->image('cedula-frente.jpg'))
            ->set('owner_id_back_document', UploadedFile::fake()->image('cedula-reverso.jpg'))
            ->set('rif_document', UploadedFile::fake()->create('rif.exe', 100, 'application/x-msdownload'))
            ->set('storefront_photo', UploadedFile::fake()->image('fachada.jpg'))
            ->call('submit')
            ->assertHasErrors(['rif_document']);

        $this->assertSame(Ally::VERIFICATION_PENDING, $ally->fresh()->verification_status);
    }

    public function test_rif_must_stay_unique_across_allies(): void
    {
        Storage::fake('documents');

        Ally::create([
            'user_id' => User::factory()->create(['role' => User::ROLE_ALIADO])->id,
            'business_name' => 'Otra Agencia',
            'rif' => 'J-99999999-9',
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
            'address' => 'Av. Principal',
            'commission_percentage' => 10,
            'status' => Ally::STATUS_ACTIVE,
            'verification_status' => Ally::VERIFICATION_VERIFIED,
        ]);

        $ally = $this->createPendingAllyUser();

        Livewire::actingAs($ally->user)
            ->test(Verificacion::class)
            ->set('business_name', 'Agencia Verificada C.A.')
            ->set('rif', 'J-99999999-9')
            ->set('address', 'Av. Bolívar')
            ->set('city', 'Valencia')
            ->set('state', 'Carabobo')
            ->set('owner_id_document', UploadedFile::fake()->image('cedula-frente.jpg'))
            ->set('owner_id_back_document', UploadedFile::fake()->image('cedula-reverso.jpg'))
            ->set('rif_document', UploadedFile::fake()->image('rif.jpg'))
            ->set('storefront_photo', UploadedFile::fake()->image('fachada.jpg'))
            ->call('submit')
            ->assertHasErrors(['rif']);
    }

    public function test_resubmitting_after_a_rejection_clears_the_reason_and_moves_to_in_review(): void
    {
        Storage::fake('documents');

        $ally = $this->createPendingAllyUser();
        $ally->update([
            'verification_status' => Ally::VERIFICATION_REJECTED,
            'verification_rejection_reason' => 'La foto de la fachada no es clara.',
            'owner_id_document_path' => 'allies/cedula-existente.jpg',
            'owner_id_back_document_path' => 'allies/cedula-reverso-existente.jpg',
            'rif_document_path' => 'allies/rif-existente.jpg',
            'storefront_photo_path' => 'allies/fachada-existente.jpg',
        ]);

        Livewire::actingAs($ally->user)
            ->test(Verificacion::class)
            ->set('storefront_photo', UploadedFile::fake()->image('fachada-nueva.jpg'))
            ->call('submit')
            ->assertHasNoErrors();

        $ally->refresh();

        $this->assertSame(Ally::VERIFICATION_IN_REVIEW, $ally->verification_status);
        $this->assertNull($ally->verification_rejection_reason);
    }

    public function test_a_verified_ally_cannot_move_themselves_back_to_in_review(): void
    {
        $ally = $this->createPendingAllyUser();
        $ally->update([
            'status' => Ally::STATUS_ACTIVE,
            'verification_status' => Ally::VERIFICATION_VERIFIED,
        ]);

        Livewire::actingAs($ally->user)
            ->test(Verificacion::class)
            ->call('submit');

        $this->assertSame(Ally::VERIFICATION_VERIFIED, $ally->fresh()->verification_status);
    }
}
