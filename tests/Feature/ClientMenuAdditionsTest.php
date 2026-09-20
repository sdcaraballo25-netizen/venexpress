<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

class ClientMenuAdditionsTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestPackages;

    protected function createClientUser(string $email): User
    {
        return User::factory()->create([
            'role' => User::ROLE_CLIENTE,
            'status' => User::STATUS_ACTIVE,
            'email' => $email,
            'email_verified_at' => now(),
            'account_verified_at' => now(),
        ]);
    }

    public function test_hasPendingCodPayments_is_false_without_any_pending_cod(): void
    {
        $user = $this->createClientUser('sin-deuda@example.com');

        Customer::create([
            'id_doc' => 'V-30303030',
            'name' => 'Sin Deuda',
            'phone' => '0414-0000010',
            'email' => $user->email,
        ]);

        $this->assertFalse($user->hasPendingCodPayments());
    }

    public function test_hasPendingCodPayments_is_true_with_a_pending_cod_package(): void
    {
        $ally = $this->createAlly();
        $user = $this->createClientUser('con-deuda@example.com');

        Customer::create([
            'id_doc' => 'V-40404040',
            'name' => 'Con Deuda',
            'phone' => '0414-0000011',
            'email' => $user->email,
        ]);

        $this->createPackage($ally, [
            'recipient_id_doc' => 'V-40404040',
            'is_cod' => true,
            'cod_amount_usd' => 10.00,
            'cod_status' => Package::COD_PENDIENTE,
        ]);

        $this->assertTrue($user->hasPendingCodPayments());
    }

    public function test_pending_payments_link_shows_a_dot_only_when_there_is_a_debt(): void
    {
        $ally = $this->createAlly();
        $user = $this->createClientUser('con-deuda-ui@example.com');

        Customer::create([
            'id_doc' => 'V-50505050',
            'name' => 'Con Deuda UI',
            'phone' => '0414-0000012',
            'email' => $user->email,
        ]);

        $this->createPackage($ally, [
            'recipient_id_doc' => 'V-50505050',
            'is_cod' => true,
            'cod_amount_usd' => 10.00,
            'cod_status' => Package::COD_PENDIENTE,
        ]);

        $this->actingAs($user)
            ->get(route('cliente.dashboard'))
            ->assertOk()
            ->assertSee('bg-red-500', false);
    }

    public function test_client_can_view_the_help_center(): void
    {
        $user = $this->createClientUser('ayuda@example.com');

        $this->actingAs($user)
            ->get(route('cliente.help'))
            ->assertOk()
            ->assertSee('Preguntas frecuentes');
    }
}
