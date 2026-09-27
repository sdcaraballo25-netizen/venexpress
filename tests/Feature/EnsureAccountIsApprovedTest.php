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
            'verification_status' => Ally::VERIFICATION_VERIFIED,
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
            'verification_status' => Emprendedor::VERIFICATION_VERIFIED,
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

    /**
     * account-pending.blade.php solo miraba isAliado()/isAliadoTaquilla()/
     * isRepartidor() para decidir el mensaje ("en revisión", "rechazada",
     * "suspendida"): un Emprendedor pendiente caía siempre en el genérico
     * "Tu cuenta no está activa" / "Inactiva", el mismo texto que ve una
     * cuenta rechazada — encontrado al recorrer el registro de un
     * emprendedor nuevo de punta a punta.
     */
    /**
     * Fase 4: verification_status === PENDIENTE (default de un
     * registro nuevo) muestra la invitación a completar la
     * verificación, con un botón a la pantalla dedicada — ya no el
     * texto genérico "Tu cuenta no está activa".
     */
    public function test_a_pending_emprendedor_sees_the_complete_verification_message_and_cta(): void
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
            'verification_status' => Emprendedor::VERIFICATION_PENDING,
        ]);

        $this->actingAs($user)
            ->get(route('account.pending'))
            ->assertOk()
            ->assertSee('Completa tu verificación')
            ->assertSee(route('emprendedor.verificacion'))
            ->assertDontSee('Tu cuenta no está activa');
    }

    /**
     * Fase 3 cambió reject() para que ya NO mueva status a RECHAZADO
     * (solo verification_status) — por eso este test ahora fija
     * verification_status explícitamente en vez de solo status, tal
     * como quedaría un rechazo real hecho desde el panel Admin.
     */
    public function test_a_rejected_emprendedor_sees_the_rejected_message_and_reason(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_EMPRENDEDOR,
            'status' => User::STATUS_ACTIVE,
        ]);

        Emprendedor::create([
            'user_id' => $user->id,
            'pickup_ally_id' => $this->createActiveAlly()->id,
            'business_name' => 'Tienda Rechazada',
            'document_id' => 'V-33333333',
            'status' => Emprendedor::STATUS_PENDING,
            'verification_status' => Emprendedor::VERIFICATION_REJECTED,
            'verification_rejection_reason' => 'El RIF no coincide con el nombre del negocio.',
        ]);

        $this->actingAs($user)
            ->get(route('account.pending'))
            ->assertOk()
            ->assertSee('Tu solicitud fue rechazada')
            ->assertSee('El RIF no coincide con el nombre del negocio.')
            ->assertSee(route('emprendedor.verificacion'));
    }

    public function test_an_emprendedor_in_review_sees_the_in_review_message_without_a_reason(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_EMPRENDEDOR,
            'status' => User::STATUS_ACTIVE,
        ]);

        Emprendedor::create([
            'user_id' => $user->id,
            'pickup_ally_id' => $this->createActiveAlly()->id,
            'business_name' => 'Tienda En Revisión',
            'document_id' => 'V-44444444',
            'status' => Emprendedor::STATUS_PENDING,
            'verification_status' => Emprendedor::VERIFICATION_IN_REVIEW,
        ]);

        $this->actingAs($user)
            ->get(route('account.pending'))
            ->assertOk()
            ->assertSee('Tu información está en revisión')
            ->assertDontSee('Tu solicitud fue rechazada');
    }

    /*
    |--------------------------------------------------------------------------
    | FASE 2 — canOperate(): verification_status === VERIFICADO
    | AND status === ACTIVO. VERIFICADO+ACTIVO ya está cubierto arriba
    | (test_active_ally_can_access_the_ally_dashboard,
    | test_active_driver_can_access_their_dashboard,
    | test_active_emprendedor_can_access_their_dashboard). Aquí se
    | cubre el resto de la matriz: cualquier otra combinación debe
    | quedar bloqueada.
    |--------------------------------------------------------------------------
    */

    private function createDriverUser(string $status, string $verificationStatus): User
    {
        $user = User::factory()->create([
            'role' => User::ROLE_REPARTIDOR,
            'status' => User::STATUS_ACTIVE,
        ]);

        Driver::factory()->create([
            'user_id' => $user->id,
            'status' => $status,
            'verification_status' => $verificationStatus,
        ]);

        return $user;
    }

    public function test_driver_pending_verification_with_active_status_cannot_operate(): void
    {
        $user = $this->createDriverUser(Driver::STATUS_ACTIVE, Driver::VERIFICATION_PENDING);

        $this->actingAs($user)
            ->get(route('repartidor.dashboard'))
            ->assertRedirect(route('account.pending'));
    }

    public function test_driver_in_review_verification_with_active_status_cannot_operate(): void
    {
        $user = $this->createDriverUser(Driver::STATUS_ACTIVE, Driver::VERIFICATION_IN_REVIEW);

        $this->actingAs($user)
            ->get(route('repartidor.dashboard'))
            ->assertRedirect(route('account.pending'));
    }

    public function test_driver_rejected_verification_with_active_status_cannot_operate(): void
    {
        $user = $this->createDriverUser(Driver::STATUS_ACTIVE, Driver::VERIFICATION_REJECTED);

        $this->actingAs($user)
            ->get(route('repartidor.dashboard'))
            ->assertRedirect(route('account.pending'));
    }

    public function test_driver_verified_but_suspended_cannot_operate(): void
    {
        $user = $this->createDriverUser(Driver::STATUS_SUSPENDED, Driver::VERIFICATION_VERIFIED);

        $this->actingAs($user)
            ->get(route('repartidor.dashboard'))
            ->assertRedirect(route('account.pending'));
    }

    private function createAllyUser(string $status, string $verificationStatus): User
    {
        $user = User::factory()->create([
            'role' => User::ROLE_ALIADO,
            'status' => User::STATUS_ACTIVE,
        ]);

        Ally::create([
            'user_id' => $user->id,
            'business_name' => 'Agencia de Prueba',
            'rif' => 'J-' . random_int(10000000, 99999999) . '-0',
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
            'address' => 'Av. Principal',
            'commission_percentage' => 10,
            'status' => $status,
            'verification_status' => $verificationStatus,
        ]);

        return $user;
    }

    public function test_ally_pending_verification_with_active_status_cannot_operate(): void
    {
        $user = $this->createAllyUser(Ally::STATUS_ACTIVE, Ally::VERIFICATION_PENDING);

        $this->actingAs($user)
            ->get(route('ally.dashboard'))
            ->assertRedirect(route('account.pending'));
    }

    public function test_ally_in_review_verification_with_active_status_cannot_operate(): void
    {
        $user = $this->createAllyUser(Ally::STATUS_ACTIVE, Ally::VERIFICATION_IN_REVIEW);

        $this->actingAs($user)
            ->get(route('ally.dashboard'))
            ->assertRedirect(route('account.pending'));
    }

    public function test_ally_rejected_verification_with_active_status_cannot_operate(): void
    {
        $user = $this->createAllyUser(Ally::STATUS_ACTIVE, Ally::VERIFICATION_REJECTED);

        $this->actingAs($user)
            ->get(route('ally.dashboard'))
            ->assertRedirect(route('account.pending'));
    }

    public function test_ally_verified_but_suspended_cannot_operate(): void
    {
        $user = $this->createAllyUser(Ally::STATUS_SUSPENDED, Ally::VERIFICATION_VERIFIED);

        $this->actingAs($user)
            ->get(route('ally.dashboard'))
            ->assertRedirect(route('account.pending'));
    }

    private function createEmprendedorUser(string $status, string $verificationStatus): User
    {
        $user = User::factory()->create([
            'role' => User::ROLE_EMPRENDEDOR,
            'status' => User::STATUS_ACTIVE,
        ]);

        Emprendedor::create([
            'user_id' => $user->id,
            'pickup_ally_id' => $this->createActiveAlly()->id,
            'business_name' => 'Tienda de Prueba',
            'document_id' => 'V-' . random_int(10000000, 99999999),
            'status' => $status,
            'verification_status' => $verificationStatus,
        ]);

        return $user;
    }

    public function test_emprendedor_verified_but_pending_status_cannot_operate(): void
    {
        $user = $this->createEmprendedorUser(Emprendedor::STATUS_PENDING, Emprendedor::VERIFICATION_VERIFIED);

        $this->actingAs($user)
            ->get(route('emprendedor.dashboard'))
            ->assertRedirect(route('account.pending'));
    }

    public function test_emprendedor_verified_but_suspended_cannot_operate(): void
    {
        $user = $this->createEmprendedorUser(Emprendedor::STATUS_SUSPENDED, Emprendedor::VERIFICATION_VERIFIED);

        $this->actingAs($user)
            ->get(route('emprendedor.dashboard'))
            ->assertRedirect(route('account.pending'));
    }

    public function test_emprendedor_in_review_with_active_status_cannot_operate(): void
    {
        $user = $this->createEmprendedorUser(Emprendedor::STATUS_ACTIVE, Emprendedor::VERIFICATION_IN_REVIEW);

        $this->actingAs($user)
            ->get(route('emprendedor.dashboard'))
            ->assertRedirect(route('account.pending'));
    }

    public function test_emprendedor_rejected_with_active_status_cannot_operate(): void
    {
        $user = $this->createEmprendedorUser(Emprendedor::STATUS_ACTIVE, Emprendedor::VERIFICATION_REJECTED);

        $this->actingAs($user)
            ->get(route('emprendedor.dashboard'))
            ->assertRedirect(route('account.pending'));
    }
}
