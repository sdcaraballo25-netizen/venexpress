<?php

namespace Tests\Feature\Auth;

use App\Models\Ally;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Volt;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response
            ->assertOk()
            ->assertSeeVolt('pages.auth.register');
    }

    public function test_new_users_can_register(): void
    {
        $component = Volt::test('pages.auth.register')
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->set('id_doc', 'V-12345678')
            ->set('phone', '+58 412 1234567');

        $component->call('register');

        $component->assertRedirect('/verify-account');

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->assertDatabaseHas('customers', [
            'id_doc' => 'V-12345678',
            'email' => 'test@example.com',
        ]);

        $user = User::where('email', 'test@example.com')->first();

        $this->assertNotNull($user);
        $this->assertNull($user->email_verified_at);
    }

    public function test_new_ally_registers_as_pending_with_storefront_photo_and_location(): void
    {
        Storage::fake('public');

        $component = Volt::test('pages.auth.register')
            ->set('name', 'Dueño Agencia')
            ->set('email', 'agencia@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->set('role', 'aliado')
            ->set('business_name', 'Agencia de Prueba')
            ->set('rif', 'J-12345678-9')
            ->set('state', 'Distrito Capital')
            ->set('city', 'Caracas')
            ->set('address', 'Av. Principal, local 1')
            ->set('storefront_photo', UploadedFile::fake()->create('fachada.jpg', 100, 'image/jpeg'))
            ->set('latitude', 10.5)
            ->set('longitude', -66.9);

        $component->call('register');

        $component->assertRedirect(route('ally.dashboard', absolute: false));

        $ally = Ally::where('rif', 'J-12345678-9')->first();

        $this->assertNotNull($ally);
        $this->assertSame(Ally::STATUS_PENDING, $ally->status);
        $this->assertNotNull($ally->storefront_photo_path);
        $this->assertEquals(10.5, (float) $ally->latitude);
        $this->assertEquals(-66.9, (float) $ally->longitude);

        Storage::disk('public')->assertExists($ally->storefront_photo_path);
    }

    public function test_new_driver_registers_as_pending_with_documents(): void
    {
        Storage::fake('public');

        $component = Volt::test('pages.auth.register')
            ->set('name', 'Repartidor Nuevo')
            ->set('email', 'repartidor@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->set('role', 'repartidor')
            ->set('vehicle_plate', 'ABC123')
            ->set('vehicle_type', 'Moto')
            ->set('phone', '+58 412 1234567')
            ->set('license_photo', UploadedFile::fake()->create('licencia.jpg', 100, 'image/jpeg'))
            ->set('id_photo', UploadedFile::fake()->create('cedula.jpg', 100, 'image/jpeg'))
            ->set('vehicle_registration_photo', UploadedFile::fake()->create('carnet.jpg', 100, 'image/jpeg'));

        $component->call('register');

        $component->assertRedirect(route('repartidor.dashboard', absolute: false));

        $user = User::where('email', 'repartidor@example.com')->first();

        $this->assertNotNull($user->driver);
        $this->assertSame(Driver::STATUS_PENDING, $user->driver->status);
        $this->assertNotNull($user->driver->license_photo_path);
        $this->assertNotNull($user->driver->id_photo_path);
        $this->assertNotNull($user->driver->vehicle_registration_photo_path);

        Storage::disk('public')->assertExists($user->driver->license_photo_path);
        Storage::disk('public')->assertExists($user->driver->id_photo_path);
        Storage::disk('public')->assertExists($user->driver->vehicle_registration_photo_path);
    }

    public function test_driver_registration_requires_all_three_documents(): void
    {
        Storage::fake('public');

        Volt::test('pages.auth.register')
            ->set('name', 'Repartidor Incompleto')
            ->set('email', 'incompleto@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->set('role', 'repartidor')
            ->set('vehicle_plate', 'XYZ999')
            ->set('vehicle_type', 'Moto')
            ->set('phone', '+58 412 1234567')
            ->call('register')
            ->assertHasErrors(['license_photo', 'id_photo', 'vehicle_registration_photo']);
    }
}
