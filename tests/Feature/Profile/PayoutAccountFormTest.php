<?php

namespace Tests\Feature\Profile;

use App\Models\Ally;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Un Aliado o Repartidor solo puede registrar la cuenta a la que se
 * le pagará su comisión/remuneración una vez su solicitud está
 * aprobada — antes de eso no hay nada que pagar, y el formulario ni
 * siquiera aparece en su perfil.
 */
class PayoutAccountFormTest extends TestCase
{
    use CreatesTestPackages;
    use RefreshDatabase;

    public function test_an_approved_ally_can_register_their_payout_account(): void
    {
        $ally = $this->createAlly(['status' => Ally::STATUS_ACTIVE]);

        $this->actingAs($ally->user);

        Volt::test('profile.payout-account-form')
            ->assertSet('approved', true)
            ->set('bank_account_number', '0102-1234-56-1234567890')
            ->set('bank_account_holder_name', 'Agencia de Prueba C.A.')
            ->set('bank_account_holder_id', 'J-12345678-9')
            ->call('save')
            ->assertHasNoErrors();

        $ally->refresh();

        $this->assertSame('0102-1234-56-1234567890', $ally->bank_account_number);
        $this->assertSame('Agencia de Prueba C.A.', $ally->bank_account_holder_name);
        $this->assertSame('J-12345678-9', $ally->bank_account_holder_id);
    }

    public function test_a_pending_ally_cannot_register_a_payout_account(): void
    {
        $ally = $this->createAlly(['status' => Ally::STATUS_PENDING]);

        $this->actingAs($ally->user);

        Volt::test('profile.payout-account-form')
            ->assertSet('approved', false)
            ->set('bank_account_number', '0102-1234-56-1234567890')
            ->set('bank_account_holder_name', 'Agencia de Prueba C.A.')
            ->set('bank_account_holder_id', 'J-12345678-9')
            ->call('save');

        $this->assertNull($ally->refresh()->bank_account_number);
    }

    public function test_an_approved_driver_can_register_their_payout_account(): void
    {
        $driverUser = User::factory()->create(['role' => User::ROLE_REPARTIDOR, 'status' => User::STATUS_ACTIVE]);
        $driver = Driver::factory()->create(['user_id' => $driverUser->id, 'status' => Driver::STATUS_ACTIVE]);

        $this->actingAs($driverUser);

        Volt::test('profile.payout-account-form')
            ->assertSet('approved', true)
            ->set('cedula', 'V-12345678')
            ->set('bank_account_number', '0102-1234-56-1234567890')
            ->set('bank_account_holder_name', 'Pedro Pérez')
            ->set('bank_account_holder_id', 'V-12345678')
            ->call('save')
            ->assertHasNoErrors();

        $driver->refresh();

        $this->assertSame('V-12345678', $driver->cedula);
        $this->assertSame('0102-1234-56-1234567890', $driver->bank_account_number);
        $this->assertSame('Pedro Pérez', $driver->bank_account_holder_name);
    }

    public function test_a_pending_driver_cannot_register_a_payout_account(): void
    {
        $driverUser = User::factory()->create(['role' => User::ROLE_REPARTIDOR, 'status' => User::STATUS_ACTIVE]);
        $driver = Driver::factory()->create(['user_id' => $driverUser->id, 'status' => Driver::STATUS_PENDING]);

        $this->actingAs($driverUser);

        Volt::test('profile.payout-account-form')
            ->assertSet('approved', false)
            ->set('cedula', 'V-12345678')
            ->set('bank_account_number', '0102-1234-56-1234567890')
            ->set('bank_account_holder_name', 'Pedro Pérez')
            ->set('bank_account_holder_id', 'V-12345678')
            ->call('save');

        $this->assertNull($driver->refresh()->bank_account_number);
    }

    public function test_the_form_is_not_shown_to_a_client(): void
    {
        $client = User::factory()->create(['role' => User::ROLE_CLIENTE]);

        $this->actingAs($client)
            ->get('/profile')
            ->assertOk()
            ->assertDontSee('Cuenta para recibir tus pagos');
    }

    public function test_the_form_is_shown_on_the_profile_page_for_an_approved_ally(): void
    {
        $ally = $this->createAlly(['status' => Ally::STATUS_ACTIVE]);

        $this->actingAs($ally->user)
            ->get('/profile')
            ->assertOk()
            ->assertSee('Cuenta para recibir tus pagos');
    }
}
