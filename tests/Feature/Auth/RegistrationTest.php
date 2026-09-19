<?php

namespace Tests\Feature\Auth;

use App\Models\Ally;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\User;
use App\Notifications\AccountPendingApproval;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
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

        // El registro debe dejar el Customer vinculado a esta cuenta
        // (ver migración add_user_id_to_customers_table), no solo con
        // el email.
        $this->assertSame(
            $user->id,
            Customer::where('id_doc', 'V-12345678')->value('user_id')
        );
    }

    /**
     * Antes, esta validación solo miraba si el Customer ya tenía un
     * email — pero un aliado puede haber tecleado el email real de
     * esa persona al despachar una guía sin que ella hubiera
     * reclamado la cédula todavía. Eso bloqueaba injustamente el
     * registro de su verdadero dueño. Ahora la decisión es por
     * user_id: si nadie ha reclamado la cédula todavía, el registro
     * debe poder completarse aunque el Customer ya tenga un email.
     */
    public function test_registering_with_an_id_doc_that_has_an_unclaimed_email_succeeds(): void
    {
        Customer::create([
            'id_doc' => 'V-99999999',
            'name' => 'Cliente Real',
            'phone' => '0414-0000000',
            'email' => 'real@example.com',
        ]);

        $component = Volt::test('pages.auth.register')
            ->set('name', 'Cliente Real')
            ->set('email', 'real@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->set('id_doc', 'V-99999999')
            ->set('phone', '+58 412 1234567');

        $component->call('register');

        $component->assertHasNoErrors();

        $user = User::where('email', 'real@example.com')->first();
        $this->assertNotNull($user);

        $this->assertSame(
            $user->id,
            Customer::where('id_doc', 'V-99999999')->value('user_id')
        );
    }

    /**
     * Una vez que una cuenta reclamó una cédula (user_id fijado), un
     * segundo registro con la misma cédula debe rechazarse, sin
     * importar qué email use el atacante.
     */
    public function test_registering_with_an_already_claimed_id_doc_is_rejected(): void
    {
        $owner = User::factory()->create([
            'role' => User::ROLE_CLIENTE,
        ]);

        Customer::create([
            'id_doc' => 'V-11111111',
            'user_id' => $owner->id,
            'name' => 'Dueño Real',
            'phone' => '0414-1111111',
            'email' => $owner->email,
        ]);

        $component = Volt::test('pages.auth.register')
            ->set('name', 'Atacante')
            ->set('email', 'atacante@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->set('id_doc', 'V-11111111')
            ->set('phone', '+58 412 9999999');

        $component->call('register');

        $component->assertHasErrors(['id_doc']);

        $this->assertDatabaseMissing('users', [
            'email' => 'atacante@example.com',
        ]);
    }

    public function test_new_ally_registers_as_pending_with_storefront_photo_and_location(): void
    {
        Storage::fake('documents');
        Notification::fake();

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
            ->set('rif_document', UploadedFile::fake()->create('rif.pdf', 200, 'application/pdf'))
            ->set('mercantile_registry_document', UploadedFile::fake()->create('registro.pdf', 200, 'application/pdf'))
            ->set('owner_id_document', UploadedFile::fake()->create('cedula.jpg', 100, 'image/jpeg'))
            ->set('latitude', 10.5)
            ->set('longitude', -66.9);

        $component->call('register');

        $component->assertRedirect(route('ally.dashboard', absolute: false));

        $ally = Ally::where('rif', 'J-12345678-9')->first();

        $this->assertNotNull($ally);
        $this->assertSame(Ally::STATUS_PENDING, $ally->status);
        $this->assertNotNull($ally->storefront_photo_path);
        $this->assertNotNull($ally->rif_document_path);
        $this->assertNotNull($ally->mercantile_registry_document_path);
        $this->assertNotNull($ally->owner_id_document_path);
        $this->assertEquals(10.5, (float) $ally->latitude);
        $this->assertEquals(-66.9, (float) $ally->longitude);

        Storage::disk('documents')->assertExists($ally->storefront_photo_path);
        Storage::disk('documents')->assertExists($ally->rif_document_path);
        Storage::disk('documents')->assertExists($ally->mercantile_registry_document_path);
        Storage::disk('documents')->assertExists($ally->owner_id_document_path);

        Notification::assertSentTo($ally->user, AccountPendingApproval::class);
    }

    public function test_ally_registration_requires_the_verification_documents(): void
    {
        Storage::fake('documents');

        $component = Volt::test('pages.auth.register')
            ->set('name', 'Dueño Agencia')
            ->set('email', 'agencia2@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->set('role', 'aliado')
            ->set('business_name', 'Agencia de Prueba 2')
            ->set('rif', 'J-99999999-1')
            ->set('state', 'Distrito Capital')
            ->set('city', 'Caracas')
            ->set('address', 'Av. Principal, local 2')
            ->set('storefront_photo', UploadedFile::fake()->create('fachada.jpg', 100, 'image/jpeg'))
            ->set('latitude', 10.5)
            ->set('longitude', -66.9);

        $component->call('register');

        $component->assertHasErrors([
            'rif_document',
            'mercantile_registry_document',
            'owner_id_document',
        ]);

        $this->assertDatabaseMissing('allies', [
            'rif' => 'J-99999999-1',
        ]);
    }

    public function test_ally_registration_rejects_a_dangerous_file_extension_for_verification_documents(): void
    {
        Storage::fake('documents');

        $component = Volt::test('pages.auth.register')
            ->set('name', 'Dueño Agencia')
            ->set('email', 'agencia3@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->set('role', 'aliado')
            ->set('business_name', 'Agencia de Prueba 3')
            ->set('rif', 'J-88888888-2')
            ->set('state', 'Distrito Capital')
            ->set('city', 'Caracas')
            ->set('address', 'Av. Principal, local 3')
            ->set('storefront_photo', UploadedFile::fake()->create('fachada.jpg', 100, 'image/jpeg'))
            ->set('rif_document', UploadedFile::fake()->create('rif.exe', 100, 'application/x-msdownload'))
            ->set('mercantile_registry_document', UploadedFile::fake()->create('registro.pdf', 200, 'application/pdf'))
            ->set('owner_id_document', UploadedFile::fake()->create('cedula.jpg', 100, 'image/jpeg'))
            ->set('latitude', 10.5)
            ->set('longitude', -66.9);

        $component->call('register');

        $component->assertHasErrors(['rif_document']);

        $this->assertDatabaseMissing('allies', [
            'rif' => 'J-88888888-2',
        ]);
    }

    public function test_new_driver_registers_as_pending_with_documents(): void
    {
        Storage::fake('documents');
        Notification::fake();

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

        Storage::disk('documents')->assertExists($user->driver->license_photo_path);
        Storage::disk('documents')->assertExists($user->driver->id_photo_path);
        Storage::disk('documents')->assertExists($user->driver->vehicle_registration_photo_path);

        Notification::assertSentTo($user, AccountPendingApproval::class);
    }

    public function test_driver_registration_requires_all_three_documents(): void
    {
        Storage::fake('documents');

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
