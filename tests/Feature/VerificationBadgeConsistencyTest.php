<?php

namespace Tests\Feature;

use App\Livewire\Ally\Verificacion as AllyVerificacion;
use App\Livewire\Driver\Verificacion as DriverVerificacion;
use App\Livewire\Emprendedor\Verificacion as EmprendedorVerificacion;
use App\Models\Ally;
use App\Models\Driver;
use App\Models\Emprendedor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestEmprendedores;
use Tests\TestCase;

/**
 * Fase 6: antes, "Mi Verificación" y account-pending usaban
 * str_replace('_', ' ', $status) crudo (ej. "EN REVISION") y colores
 * distintos para el mismo estado (EN_REVISION era ámbar en
 * account-pending pero celeste en "Mi Verificación"). Ambos ahora
 * comparten x-verification-status-badge, así que deben mostrar la
 * misma etiqueta legible y el mismo color.
 */
class VerificationBadgeConsistencyTest extends TestCase
{
    use CreatesTestEmprendedores;
    use RefreshDatabase;

    public function test_driver_in_review_shows_a_human_readable_label_not_the_raw_enum(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_REPARTIDOR, 'status' => User::STATUS_ACTIVE]);
        Driver::factory()->create([
            'user_id' => $user->id,
            'status' => Driver::STATUS_PENDING,
            'verification_status' => Driver::VERIFICATION_IN_REVIEW,
        ]);

        Livewire::actingAs($user)
            ->test(DriverVerificacion::class)
            ->assertSee('En revisión')
            ->assertDontSee('EN_REVISION')
            ->assertDontSee('EN REVISION');
    }

    public function test_account_pending_and_mi_verificacion_use_the_same_color_for_in_review(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_REPARTIDOR, 'status' => User::STATUS_ACTIVE]);
        Driver::factory()->create([
            'user_id' => $user->id,
            'status' => Driver::STATUS_PENDING,
            'verification_status' => Driver::VERIFICATION_IN_REVIEW,
        ]);

        $pendingHtml = $this->actingAs($user)->get(route('account.pending'))->getContent();

        Livewire::actingAs($user)
            ->test(DriverVerificacion::class)
            ->assertSee('bg-sky-50', false);

        $this->assertStringContainsString('bg-sky-50', $pendingHtml);
        $this->assertStringNotContainsString('bg-amber-50', $pendingHtml);
    }

    public function test_ally_rejected_shows_human_readable_label(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ALIADO, 'status' => User::STATUS_ACTIVE]);
        Ally::create([
            'user_id' => $user->id,
            'business_name' => 'Agencia de Prueba',
            'rif' => 'J-11111111-1',
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
            'address' => 'Av. Principal',
            'commission_percentage' => 10,
            'status' => Ally::STATUS_PENDING,
            'verification_status' => Ally::VERIFICATION_REJECTED,
            'verification_rejection_reason' => 'Motivo de prueba.',
        ]);

        Livewire::actingAs($user)
            ->test(AllyVerificacion::class)
            ->assertSee('Rechazado')
            ->assertDontSee('RECHAZADO');
    }

    public function test_emprendedor_verified_shows_human_readable_label(): void
    {
        $emprendedor = $this->createEmprendedor([
            'status' => Emprendedor::STATUS_ACTIVE,
            'verification_status' => Emprendedor::VERIFICATION_VERIFIED,
        ]);

        Livewire::actingAs($emprendedor->user)
            ->test(EmprendedorVerificacion::class)
            ->assertSee('Verificado')
            ->assertDontSee('VERIFICADO');
    }
}
