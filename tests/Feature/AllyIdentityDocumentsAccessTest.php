<?php

namespace Tests\Feature;

use App\Services\AllyStaffService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * La cédula, el RIF y el registro mercantil del titular son del dueño de
 * la agencia: el personal de Taquilla no debe poder verlos (sí la foto
 * de la fachada).
 */
class AllyIdentityDocumentsAccessTest extends TestCase
{
    use CreatesTestPackages;
    use RefreshDatabase;

    public function test_taquilla_cannot_see_the_owner_identity_documents_but_the_owner_can(): void
    {
        Storage::fake('documents');
        Storage::disk('documents')->put('allies/id.jpg', UploadedFile::fake()->image('id.jpg')->getContent());
        Storage::disk('documents')->put('allies/front.jpg', UploadedFile::fake()->image('front.jpg')->getContent());

        $ally = $this->createAlly([
            'owner_id_document_path' => 'allies/id.jpg',
            'rif_document_path' => 'allies/id.jpg',
            'storefront_photo_path' => 'allies/front.jpg',
        ]);

        $taquilla = app(AllyStaffService::class)->create($ally, [
            'name' => 'Taquilla', 'username' => 'taq-docs', 'password' => 'password-seguro',
        ]);

        $this->actingAs($taquilla)->get(route('allies.documents.owner-id', $ally))->assertForbidden();
        $this->actingAs($taquilla)->get(route('allies.documents.rif', $ally))->assertForbidden();
        $this->actingAs($taquilla)->get(route('allies.documents.storefront', $ally))->assertOk();

        $this->actingAs($ally->user)->get(route('allies.documents.owner-id', $ally))->assertOk();
    }
}
