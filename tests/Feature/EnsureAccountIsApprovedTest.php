<?php

namespace Tests\Feature;

use App\Models\Ally;
use App\Models\Driver;
use App\Models\Emprendedor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * EnsureAccountIsApproved trataba un User con rol aliado/repartidor
 * pero SIN su Ally/Driver asociado como si estuviera aprobado (dejaba
 * pasar), porque $status quedaba en null. Eso solo podía pasar si el
 * registro fallaba a mitad de camino (antes de que register.blade.php
 * envolviera la creación en una transacción) — pero si pasaba, el
 * middleware fallaba "abierto" en vez de "cerrado". Ahora un Ally/
 * Driver faltante se trata como PENDIENTE.
 */
class EnsureAccountIsApprovedTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_ally_can_access_the_ally_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_ALIADO,
            'status' => User::STATUS_ACTIVE,
        ]);

        Ally::create([
            'user_id' => $user->id,
            'business_name' => 'Agencia Activa',
            'rif' => 'J-11111111-1',
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
            'address' => 'Av. Principal',
            'commission_percentage' => 10,
            'status' => Ally::STATUS_ACTIVE,
        ]);

        $this->actingAs($user)
            ->get(route('ally.dashboard'))
            ->assertOk();
    }

    public function test_pending_ally_is_redirected_to_account_pending(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_ALIADO,
            'status' => User::STATUS_ACTIVE,
        ]);

        Ally::create([
            'user_id' => $user->id,
            'business_name' => 'Agencia Pendiente',
            'rif' => 'J-22222222-2',
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
            'address' => 'Av. Principal',
            'commission_percentage' => 10,
            'status' => Ally::STATUS_PENDING,
        ]);

        $this->actingAs($user)
            ->get(route('ally.dashboard'))
            ->assertRedirect(route('account.pending'));
    }

    /**
     * El caso que motivó el fix: un User con rol "aliado" sin ningún
     * registro Ally asociado (una cuenta huérfana) ya no debe poder
     * entrar al panel de aliado.
     */
    public function test_ally_role_user_without_an_ally_record_is_redirected_to_account_pending(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_ALIADO,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->actingAs($user)
            ->get(route('ally.dashboard'))
            ->assertRedirect(route('account.pending'));
    }

    public function test_driver_role_user_without_a_driver_record_is_redirected_to_account_pending(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_REPARTIDOR,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->actingAs($user)
            ->get(route('repartidor.dashboard'))
            ->assertRedirect(route('account.pending'));
    }

    public function test_active_driver_can_access_their_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_REPARTIDOR,
            'status' => User::STATUS_ACTIVE,
        ]);

        Driver::factory()->create([
            'user_id' => $user->id,
            'status' => Driver::STATUS_ACTIVE,
        ]);

        $this->actingAs($user)
            ->get(route('repartidor.dashboard'))
            ->assertOk();
    }

    private function createActiveAlly(): Ally
    {
        $allyUser = User::factory()->create(['role' => User::ROLE_ALIADO]);

        return Ally::create([
            'user_id' => $allyUser->id,
            'business_name' => 'Agencia de Retiro',
            'rif' => 'J-33333333-3',
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
            'address' => 'Av. Principal',
            'commission_percentage' => 10,
            'status' => Ally::STATUS_ACTIVE,
        ]);
    }

    public function test_active_emprendedor_can_access_their_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_EMPRENDEDOR,
            'status' => User::STATUS_ACTIVE,
        ]);

        Emprendedor::create([
            'user_id' => $user->id,
            'pickup_ally_id' => $this->createActiveAlly()->id,
            'business_name' => 'Tienda Activa',
            'document_id' => 'V-11111111',
            'status' => Emprendedor::STATUS_ACTIVE,
        ]);

        $this->actingAs($user)
            ->get(route('emprendedor.dashboard'))
            ->assertOk();
    }

    public function test_pending_emprendedor_is_redirected_to_account_pending(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_EMPRENDEDOR,
            'status' => User::STATUS_ACTIVE,
        ]);

        Emprendedor::create([
            'user_id' => $user->id,
            'pickup_ally_id' => $this->createActiveAlly()->id,
            'business_name' => 'Tienda Pendiente',
            'document_id' => 'V-22222222',
            'status' => Emprendedor::STATUS_PENDING,
        ]);

        $this->actingAs($user)
            ->get(route('emprendedor.dashboard'))
            ->assertRedirect(route('account.pending'));
    }

    public function test_emprendedor_role_user_without_an_emprendedor_record_is_redirected_to_account_pending(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_EMPRENDEDOR,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->actingAs($user)
            ->get(route('emprendedor.dashboard'))
            ->assertRedirect(route('account.pending'));
    }
}
