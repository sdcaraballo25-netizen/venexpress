<?php

namespace Tests\Feature;

use App\Livewire\Client\PendingPayments;
use App\Models\Customer;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Panel de "Pagos pendientes" del Cliente.
 *
 * El pago en línea (Pago Móvil / Inmediato / Pagar todo) sigue
 * deshabilitado a propósito (ver docblock de PendingPayments): estos
 * tests solo cubren que el total y el listado se calculan bien, no un
 * flujo de cobro que todavía no existe en el sistema.
 */
class ClientPendingPaymentsTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestPackages;

    protected function createClientUser(string $email): User
    {
        return User::factory()->create([
            'role' => User::ROLE_CLIENTE,
            'status' => User::STATUS_ACTIVE,
            'email' => $email,
        ]);
    }

    public function test_pending_total_sums_only_unpaid_cod_packages_for_the_client(): void
    {
        $ally = $this->createAlly();

        $user = $this->createClientUser('cliente-cod@example.com');

        Customer::create([
            'id_doc' => 'V-20202020',
            'name' => 'Cliente COD',
            'phone' => '0414-0000009',
            'email' => $user->email,
        ]);

        $this->createPackage($ally, [
            'tracking_number' => 'VEN-TEST-COD1',
            'recipient_id_doc' => 'V-20202020',
            'is_cod' => true,
            'cod_status' => Package::COD_PENDIENTE,
            'cod_amount_usd' => 7.00,
        ]);

        $this->createPackage($ally, [
            'tracking_number' => 'VEN-TEST-COD2',
            'recipient_id_doc' => 'V-20202020',
            'is_cod' => true,
            'cod_status' => Package::COD_PENDIENTE,
            'cod_amount_usd' => 5.32,
        ]);

        // Ya liquidado: no debe sumar al total pendiente.
        $this->createPackage($ally, [
            'tracking_number' => 'VEN-TEST-COD3',
            'recipient_id_doc' => 'V-20202020',
            'is_cod' => true,
            'cod_status' => Package::COD_LIQUIDADO,
            'cod_amount_usd' => 100.00,
        ]);

        // No es COD: tampoco debe aparecer.
        $this->createPackage($ally, [
            'tracking_number' => 'VEN-TEST-NOCOD',
            'recipient_id_doc' => 'V-20202020',
            'is_cod' => false,
        ]);

        $component = Livewire::actingAs($user)
            ->test(PendingPayments::class);

        $this->assertSame(12.32, $component->viewData('totalPendingUsd'));

        $ids = $component->viewData('packages')->pluck('tracking_number')->all();

        $this->assertContains('VEN-TEST-COD1', $ids);
        $this->assertContains('VEN-TEST-COD2', $ids);
        $this->assertNotContains('VEN-TEST-COD3', $ids);
        $this->assertNotContains('VEN-TEST-NOCOD', $ids);
    }

    /**
     * El botón "Pagar todo" muestra el total consolidado, pero sigue
     * deshabilitado igual que los botones por paquete: el cobro en
     * línea no está implementado todavía.
     */
    public function test_pay_all_button_shows_total_and_stays_disabled(): void
    {
        $ally = $this->createAlly();

        $user = $this->createClientUser('cliente-pagartodo@example.com');

        Customer::create([
            'id_doc' => 'V-30303030',
            'name' => 'Cliente Pagar Todo',
            'phone' => '0414-0000010',
            'email' => $user->email,
        ]);

        $this->createPackage($ally, [
            'tracking_number' => 'VEN-TEST-PT1',
            'recipient_id_doc' => 'V-30303030',
            'is_cod' => true,
            'cod_status' => Package::COD_PENDIENTE,
            'cod_amount_usd' => 10.00,
        ]);

        Livewire::actingAs($user)
            ->test(PendingPayments::class)
            ->assertSee('Pagar todo ($10.00)')
            ->assertSeeHtml('disabled');
    }

    public function test_shows_empty_state_when_there_is_nothing_to_pay(): void
    {
        $user = $this->createClientUser('cliente-sin-pagos@example.com');

        Customer::create([
            'id_doc' => 'V-40404040',
            'name' => 'Cliente Sin Pagos',
            'phone' => '0414-0000011',
            'email' => $user->email,
        ]);

        Livewire::actingAs($user)
            ->test(PendingPayments::class)
            ->assertSee('No tienes pagos pendientes')
            ->assertDontSee('Pagar todo');
    }
}
