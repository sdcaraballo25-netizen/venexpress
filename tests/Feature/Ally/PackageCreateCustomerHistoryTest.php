<?php

namespace Tests\Feature\Ally;

use App\Livewire\Ally\PackageCreate;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Cuando el staff de un aliado busca a un cliente conocido por su
 * cédula al registrar un paquete, debe ver cuántos pedidos anteriores
 * tiene esa cédula en el sistema (como remitente o destinatario).
 */
class PackageCreateCustomerHistoryTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestPackages;

    public function test_shows_previous_packages_count_for_a_known_sender(): void
    {
        $ally = $this->createAlly();

        Customer::create([
            'id_doc' => 'V-12345678',
            'name' => 'Juan Pérez',
            'phone' => '0414-1234567',
        ]);

        // Dos guías anteriores con esta cédula: una como remitente, otra como destinatario.
        $this->createPackage($ally, ['sender_id_doc' => 'V-12345678', 'tracking_number' => 'VEN-HIST-1']);
        $this->createPackage($ally, ['recipient_id_doc' => 'V-12345678', 'tracking_number' => 'VEN-HIST-2']);

        Livewire::actingAs($ally->user)
            ->test(PackageCreate::class)
            ->set('sender_doc_number', '12345678')
            ->assertSet('senderCustomerFound', true)
            ->assertSet('senderPreviousPackagesCount', 2);
    }

    public function test_shows_zero_previous_packages_for_a_new_customer(): void
    {
        $ally = $this->createAlly();

        Customer::create([
            'id_doc' => 'V-99999999',
            'name' => 'Cliente Nuevo',
            'phone' => '0414-0000000',
        ]);

        Livewire::actingAs($ally->user)
            ->test(PackageCreate::class)
            ->set('recipient_doc_number', '99999999')
            ->assertSet('recipientCustomerFound', true)
            ->assertSet('recipientPreviousPackagesCount', 0);
    }
}
