<?php

namespace Tests\Feature\Emprendedor;

use App\Livewire\Emprendedor\Productos;
use App\Models\Producto;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestEmprendedores;
use Tests\TestCase;

class ProductosTest extends TestCase
{
    use CreatesTestEmprendedores;
    use RefreshDatabase;

    public function test_an_emprendedor_can_create_a_product(): void
    {
        Storage::fake('public');

        $emprendedor = $this->createEmprendedor();

        Livewire::actingAs($emprendedor->user)
            ->test(Productos::class)
            ->set('nombre', 'Camisa azul')
            ->set('precio_usd', '15.50')
            ->set('peso_kg', '0.5')
            ->set('stock', '10')
            ->set('foto', UploadedFile::fake()->create('foto.jpg', 100, 'image/jpeg'))
            ->call('save')
            ->assertHasNoErrors();

        $producto = Producto::where('emprendedor_id', $emprendedor->id)->first();

        $this->assertNotNull($producto);
        $this->assertSame('Camisa azul', $producto->nombre);
        $this->assertSame(10, $producto->stock);
        $this->assertTrue($producto->activo);
        $this->assertNotNull($producto->foto_path);

        Storage::disk('public')->assertExists($producto->foto_path);
    }

    public function test_an_emprendedor_can_create_a_product_with_a_category_and_gallery_photos(): void
    {
        Storage::fake('public');

        $emprendedor = $this->createEmprendedor();

        Livewire::actingAs($emprendedor->user)
            ->test(Productos::class)
            ->set('nombre', 'Camisa azul')
            ->set('precio_usd', '15.50')
            ->set('peso_kg', '0.5')
            ->set('stock', '10')
            ->set('categoria', 'ropa')
            ->set('fotosNuevas', [
                UploadedFile::fake()->create('galeria-1.jpg', 100, 'image/jpeg'),
                UploadedFile::fake()->create('galeria-2.jpg', 100, 'image/jpeg'),
            ])
            ->call('save')
            ->assertHasNoErrors();

        $producto = Producto::where('emprendedor_id', $emprendedor->id)->first();

        $this->assertSame('ropa', $producto->categoria);
        $this->assertCount(2, $producto->fotos);

        foreach ($producto->fotos as $ruta) {
            Storage::disk('public')->assertExists($ruta);
        }
    }

    public function test_creating_a_product_rejects_an_invalid_category(): void
    {
        $emprendedor = $this->createEmprendedor();

        Livewire::actingAs($emprendedor->user)
            ->test(Productos::class)
            ->set('nombre', 'Producto con categoría inválida')
            ->set('precio_usd', '10')
            ->set('peso_kg', '1')
            ->set('stock', '5')
            ->set('categoria', 'no-existe')
            ->call('save')
            ->assertHasErrors(['categoria']);
    }

    public function test_a_gallery_cannot_exceed_the_maximum_number_of_photos(): void
    {
        $emprendedor = $this->createEmprendedor();

        Livewire::actingAs($emprendedor->user)
            ->test(Productos::class)
            ->set('nombre', 'Producto con muchas fotos')
            ->set('precio_usd', '10')
            ->set('peso_kg', '1')
            ->set('stock', '5')
            ->set('fotosNuevas', array_map(
                fn ($i) => UploadedFile::fake()->create("galeria-{$i}.jpg", 100, 'image/jpeg'),
                range(1, 6)
            ))
            ->call('save')
            ->assertHasErrors(['fotosNuevas']);
    }

    public function test_an_emprendedor_can_remove_a_gallery_photo_while_editing(): void
    {
        Storage::fake('public');

        $emprendedor = $this->createEmprendedor();

        $producto = Producto::create([
            'emprendedor_id' => $emprendedor->id,
            'nombre' => 'Producto con galería',
            'precio_usd' => 10.00,
            'peso_kg' => 1.0,
            'stock' => 5,
            'activo' => true,
            'fotos' => ['productos/foto-1.jpg', 'productos/foto-2.jpg'],
        ]);

        Livewire::actingAs($emprendedor->user)
            ->test(Productos::class)
            ->call('editProducto', $producto->id)
            ->assertSet('existingFotos', ['productos/foto-1.jpg', 'productos/foto-2.jpg'])
            ->call('eliminarFotoExistente', 0)
            ->assertSet('existingFotos', ['productos/foto-2.jpg'])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(['productos/foto-2.jpg'], $producto->fresh()->fotos);
    }

    public function test_creating_a_product_requires_a_positive_price_and_weight(): void
    {
        $emprendedor = $this->createEmprendedor();

        Livewire::actingAs($emprendedor->user)
            ->test(Productos::class)
            ->set('nombre', 'Producto inválido')
            ->set('precio_usd', '0')
            ->set('peso_kg', '0')
            ->set('stock', '5')
            ->call('save')
            ->assertHasErrors(['precio_usd', 'peso_kg']);
    }

    public function test_an_emprendedor_can_edit_their_own_product(): void
    {
        $emprendedor = $this->createEmprendedor();

        $producto = Producto::create([
            'emprendedor_id' => $emprendedor->id,
            'nombre' => 'Nombre original',
            'precio_usd' => 10.00,
            'peso_kg' => 1.0,
            'stock' => 5,
            'activo' => true,
        ]);

        Livewire::actingAs($emprendedor->user)
            ->test(Productos::class)
            ->call('editProducto', $producto->id)
            ->set('nombre', 'Nombre actualizado')
            ->set('precio_usd', '20.00')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Nombre actualizado', $producto->fresh()->nombre);
        $this->assertSame('20.00', (string) $producto->fresh()->precio_usd);
    }

    public function test_an_emprendedor_cannot_edit_another_emprendedors_product(): void
    {
        $owner = $this->createEmprendedor();
        $intruder = $this->createEmprendedor();

        $producto = Producto::create([
            'emprendedor_id' => $owner->id,
            'nombre' => 'Producto ajeno',
            'precio_usd' => 10.00,
            'peso_kg' => 1.0,
            'stock' => 5,
            'activo' => true,
        ]);

        $this->expectException(ModelNotFoundException::class);

        Livewire::actingAs($intruder->user)
            ->test(Productos::class)
            ->call('editProducto', $producto->id);
    }

    public function test_toggle_activo_flips_the_products_active_state(): void
    {
        $emprendedor = $this->createEmprendedor();

        $producto = Producto::create([
            'emprendedor_id' => $emprendedor->id,
            'nombre' => 'Producto activo',
            'precio_usd' => 10.00,
            'peso_kg' => 1.0,
            'stock' => 5,
            'activo' => true,
        ]);

        Livewire::actingAs($emprendedor->user)
            ->test(Productos::class)
            ->call('toggleActivo', $producto->id);

        $this->assertFalse((bool) $producto->fresh()->activo);
    }

    public function test_the_screen_only_lists_the_emprendedors_own_products(): void
    {
        $emprendedor = $this->createEmprendedor();
        $other = $this->createEmprendedor();

        Producto::create([
            'emprendedor_id' => $emprendedor->id,
            'nombre' => 'Producto propio',
            'precio_usd' => 10.00,
            'peso_kg' => 1.0,
            'stock' => 5,
            'activo' => true,
        ]);

        Producto::create([
            'emprendedor_id' => $other->id,
            'nombre' => 'Producto ajeno',
            'precio_usd' => 10.00,
            'peso_kg' => 1.0,
            'stock' => 5,
            'activo' => true,
        ]);

        Livewire::actingAs($emprendedor->user)
            ->test(Productos::class)
            ->assertSee('Producto propio')
            ->assertDontSee('Producto ajeno');
    }
}
