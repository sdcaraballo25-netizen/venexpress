<?php

namespace Tests\Feature\Emprendedor;

use App\Livewire\Emprendedor\Perfil;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestEmprendedores;
use Tests\TestCase;

class PerfilTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestEmprendedores;

    public function test_an_emprendedor_can_update_their_stores_description_and_address(): void
    {
        $emprendedor = $this->createEmprendedor();

        Livewire::actingAs($emprendedor->user)
            ->test(Perfil::class)
            ->set('descripcion', 'Vendemos ropa hecha a mano desde 2020.')
            ->set('address', 'Av. Bolívar, local 12, Valencia')
            ->call('save')
            ->assertHasNoErrors();

        $emprendedor->refresh();

        $this->assertSame('Vendemos ropa hecha a mano desde 2020.', $emprendedor->descripcion);
        $this->assertSame('Av. Bolívar, local 12, Valencia', $emprendedor->address);
    }

    public function test_an_emprendedor_can_upload_a_logo_and_cover_photo(): void
    {
        Storage::fake('public');

        $emprendedor = $this->createEmprendedor();

        Livewire::actingAs($emprendedor->user)
            ->test(Perfil::class)
            ->set('logo', UploadedFile::fake()->create('logo.jpg', 100, 'image/jpeg'))
            ->set('cover', UploadedFile::fake()->create('portada.jpg', 100, 'image/jpeg'))
            ->call('save')
            ->assertHasNoErrors();

        $emprendedor->refresh();

        $this->assertNotNull($emprendedor->logo_path);
        $this->assertNotNull($emprendedor->cover_photo_path);
        Storage::disk('public')->assertExists($emprendedor->logo_path);
        Storage::disk('public')->assertExists($emprendedor->cover_photo_path);
    }

    public function test_the_description_has_a_max_length(): void
    {
        $emprendedor = $this->createEmprendedor();

        Livewire::actingAs($emprendedor->user)
            ->test(Perfil::class)
            ->set('descripcion', str_repeat('a', 1001))
            ->call('save')
            ->assertHasErrors(['descripcion']);
    }

    public function test_the_logo_must_be_an_image(): void
    {
        $emprendedor = $this->createEmprendedor();

        Livewire::actingAs($emprendedor->user)
            ->test(Perfil::class)
            ->set('logo', UploadedFile::fake()->create('documento.pdf', 100, 'application/pdf'))
            ->call('save')
            ->assertHasErrors(['logo']);
    }

    public function test_the_form_prefills_with_the_emprendedors_existing_data(): void
    {
        $emprendedor = $this->createEmprendedor([
            'descripcion' => 'Descripción existente',
            'address' => 'Dirección existente',
        ]);

        Livewire::actingAs($emprendedor->user)
            ->test(Perfil::class)
            ->assertSet('descripcion', 'Descripción existente')
            ->assertSet('address', 'Dirección existente');
    }
}
