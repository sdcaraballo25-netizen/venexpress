<?php

namespace Tests\Feature\Auth;

use App\Models\Ally;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Emprendedor;
use App\Models\User;
use App\Notifications\AccountPendingApproval;
use App\Notifications\WelcomeVerificationToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
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

    public function test_registering_never_overwrites_contact_data_an_ally_already_recorded(): void
    {
        Customer::create([
            'id_doc' => 'V-44445555',
            'name' => 'Nombre en Taquilla',
            'phone' => '0414-1111111',
            'email' => null,
        ]);

        Volt::test('pages.auth.register')
            ->set('name', 'Otro Nombre')
            ->set('email', 'nuevo-registro@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->set('id_doc', 'V-44445555')
            ->set('phone', '0424-9999999')
            ->call('register')
            ->assertHasNoErrors();

        $customer = Customer::where('id_doc', 'V-44445555')->first();

        $this->assertSame('Nombre en Taquilla', $customer->name);
        $this->assertSame('0414-1111111', $customer->phone);
        // Lo que faltaba sí se completa.
        $this->assertSame('nuevo-registro@example.com', $customer->email);
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

    /**
     * Registro ≠ Verificación: el registro de Aliado ya no pide
     * documentos ni ubicación en el mapa (eso se completa después
     * desde "Mi Verificación" / desde el panel de Admin — ver
     * App\Livewire\Ally\Verificacion y AlliesManager::editLocation()).
     */
    public function test_new_ally_registers_as_pending_without_documents_or_location(): void
    {
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
            ->set('address', 'Av. Principal, local 1');

        $component->call('register');

        $component->assertHasNoErrors();
        // Como el cliente: primero verifica el correo con el código,
        // sin sesión iniciada todavía (EnsureAccountIsVerified).
        $component->assertRedirect(route('verify-account', absolute: false));
        $this->assertGuest();

        $ally = Ally::where('rif', 'J-12345678-9')->first();

        $this->assertNotNull($ally);
        $this->assertSame(Ally::STATUS_PENDING, $ally->status);
        $this->assertSame(Ally::VERIFICATION_PENDING, $ally->verification_status);

        // Documentos y ubicación quedan vacíos: se completan después,
        // no durante el registro.
        $this->assertNull($ally->storefront_photo_path);
        $this->assertNull($ally->rif_document_path);
        $this->assertNull($ally->mercantile_registry_document_path);
        $this->assertNull($ally->owner_id_document_path);
        $this->assertNull($ally->latitude);
        $this->assertNull($ally->longitude);

        Notification::assertSentTo($ally->user, AccountPendingApproval::class);
        Notification::assertSentTo($ally->user, WelcomeVerificationToken::class);
        $this->assertFalse($ally->user->isAccountVerified());
    }

    /**
     * Registro ≠ Verificación: el registro de Repartidor ya no pide
     * documentos (eso se completa después desde "Mi Verificación" —
     * ver App\Livewire\Driver\Verificacion).
     */
    public function test_new_driver_registers_as_pending_without_documents(): void
    {
        Notification::fake();

        $component = Volt::test('pages.auth.register')
            ->set('name', 'Repartidor Nuevo')
            ->set('email', 'repartidor@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->set('role', 'repartidor')
            ->set('vehicle_plate', 'ABC123')
            ->set('vehicle_type', 'Moto')
            ->set('phone', '+58 412 1234567');

        $component->call('register');

        $component->assertHasNoErrors();
        $component->assertRedirect(route('verify-account', absolute: false));
        $this->assertGuest();

        $user = User::where('email', 'repartidor@example.com')->first();

        $this->assertNotNull($user->driver);
        $this->assertSame(Driver::STATUS_PENDING, $user->driver->status);
        $this->assertSame(Driver::VERIFICATION_PENDING, $user->driver->verification_status);

        // Documentos quedan vacíos: se completan después, no durante
        // el registro.
        $this->assertNull($user->driver->license_photo_path);
        $this->assertNull($user->driver->id_photo_path);
        $this->assertNull($user->driver->vehicle_registration_photo_path);

        Notification::assertSentTo($user, AccountPendingApproval::class);
        Notification::assertSentTo($user, WelcomeVerificationToken::class);
        $this->assertFalse($user->isAccountVerified());
    }

    private function createActiveAlly(): Ally
    {
        $user = User::factory()->create(['role' => User::ROLE_ALIADO]);

        return Ally::create([
            'user_id' => $user->id,
            'business_name' => 'Agencia Aliada de Prueba',
            'rif' => 'J-' . random_int(10000000, 99999999) . '-0',
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
            'address' => 'Av. Principal',
            'commission_percentage' => 10.00,
            'status' => Ally::STATUS_ACTIVE,
        ]);
    }

    public function test_new_emprendedor_registers_as_pending_with_a_pickup_ally(): void
    {
        Notification::fake();

        $pickupAlly = $this->createActiveAlly();

        $component = Volt::test('pages.auth.register')
            ->set('name', 'Dueño de Tienda')
            ->set('email', 'emprendedor@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->set('role', 'emprendedor')
            ->set('business_name', 'Tienda de Prueba')
            ->set('document_id', 'V-12345678')
            ->set('pickup_ally_id', $pickupAlly->id);

        $component->call('register');

        $component->assertRedirect(route('verify-account', absolute: false));
        $this->assertGuest();

        $user = User::where('email', 'emprendedor@example.com')->first();

        $this->assertNotNull($user);
        $this->assertNotNull($user->emprendedor);
        $this->assertSame(Emprendedor::STATUS_PENDING, $user->emprendedor->status);
        $this->assertSame('Tienda de Prueba', $user->emprendedor->business_name);
        $this->assertSame($pickupAlly->id, $user->emprendedor->pickup_ally_id);

        Notification::assertSentTo($user, AccountPendingApproval::class);
        Notification::assertSentTo($user, WelcomeVerificationToken::class);
        $this->assertFalse($user->isAccountVerified());
    }

    public function test_emprendedor_registration_requires_an_active_pickup_ally(): void
    {
        Volt::test('pages.auth.register')
            ->set('name', 'Dueño de Tienda')
            ->set('email', 'sinagencia@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->set('role', 'emprendedor')
            ->set('business_name', 'Tienda Sin Agencia')
            ->set('document_id', 'V-87654321')
            ->call('register')
            ->assertHasErrors(['pickup_ally_id']);

        $this->assertDatabaseMissing('users', [
            'email' => 'sinagencia@example.com',
        ]);
    }
}
