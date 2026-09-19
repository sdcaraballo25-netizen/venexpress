<?php

namespace Tests\Feature\Profile;

use App\Models\Driver;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * La página /profile es compartida por los 5 roles del sistema, pero
 * cada uno debe verla envuelta en su propio layout branded (el que ya
 * usan sus paneles) en vez del layout genérico de Breeze.
 */
class ProfileShowLayoutTest extends TestCase
{
    use CreatesTestPackages;
    use RefreshDatabase;

    public function test_admin_sees_the_admin_layout(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN_PRINCIPAL,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->actingAs($admin)
            ->get('/profile')
            ->assertOk()
            ->assertSee('Panel Administrativo', escape: false);
    }

    public function test_ally_sees_the_ally_layout(): void
    {
        $ally = $this->createAlly();

        $this->actingAs($ally->user)
            ->get('/profile')
            ->assertOk()
            ->assertSee('Mi Perfil', escape: false);
    }

    public function test_driver_sees_the_driver_layout(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_REPARTIDOR,
            'status' => User::STATUS_ACTIVE,
        ]);
        Driver::factory()->create(['user_id' => $user->id, 'status' => Driver::STATUS_ACTIVE]);

        $this->actingAs($user)
            ->get('/profile')
            ->assertOk();
    }

    public function test_almacen_sees_the_almacen_layout(): void
    {
        $warehouse = Warehouse::factory()->create();

        $user = User::factory()->create([
            'role' => User::ROLE_ALMACEN,
            'status' => User::STATUS_ACTIVE,
            'warehouse_id' => $warehouse->id,
        ]);

        $this->actingAs($user)
            ->get('/profile')
            ->assertOk()
            ->assertSee('Panel Almacén', escape: false);
    }

    public function test_client_sees_the_client_layout(): void
    {
        $client = User::factory()->create([
            'role' => User::ROLE_CLIENTE,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->actingAs($client)
            ->get('/profile')
            ->assertOk()
            ->assertSee('Mis pedidos', escape: false);
    }

    public function test_phone_number_can_be_updated_from_the_profile_form(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Volt::test('profile.update-profile-information-form')
            ->set('name', $user->name)
            ->set('email', $user->email)
            ->set('phone', '0412-1234567')
            ->call('updateProfileInformation')
            ->assertHasNoErrors();

        $this->assertSame('0412-1234567', $user->refresh()->phone);
    }
}
