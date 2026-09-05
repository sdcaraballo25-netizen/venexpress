<?php

namespace Tests\Feature;

use App\Livewire\Client\Dashboard;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Incident;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Cubre los flujos del panel de Cliente:
 *
 * - Aceptar una entrega a domicilio.
 * - Rechazar una entrega y abrir una incidencia.
 * - Impedir una segunda respuesta sobre la misma entrega.
 * - Mostrar paquetes de varios clientes con el mismo correo.
 */
class ClientDashboardTest extends TestCase
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

    public function test_client_can_accept_a_pending_delivery_and_it_is_audited(): void
    {
        $ally = $this->createAlly();

        $user = $this->createClientUser(
            'cliente@example.com'
        );

        Customer::create([
            'id_doc' => 'V-11111111',
            'name' => 'Cliente de Prueba',
            'phone' => '0414-0000000',
            'email' => $user->email,
        ]);

        $package = $this->createPackage($ally, [
            'recipient_id_doc' => 'V-11111111',
            'requires_delivery' => true,
            'delivery_status' => Package::DELIVERY_PENDING,
        ]);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->call('acceptDelivery', $package->id)
            ->assertHasNoErrors();

        $package->refresh();

        $this->assertSame(
            Package::DELIVERY_ACCEPTED,
            $package->delivery_status
        );

        $this->assertNotNull(
            $package->delivery_accepted_at
        );

        $this->assertSame(
            1,
            AuditLog::query()
                ->where(
                    'action',
                    'client.delivery_accepted'
                )
                ->where(
                    'target_id',
                    $package->id
                )
                ->count()
        );
    }

    public function test_client_can_reject_a_delivery_which_opens_an_incident_and_is_audited(): void
    {
        $ally = $this->createAlly();

        $user = $this->createClientUser(
            'cliente2@example.com'
        );

        Customer::create([
            'id_doc' => 'V-22222222',
            'name' => 'Cliente de Prueba 2',
            'phone' => '0414-0000001',
            'email' => $user->email,
        ]);

        $package = $this->createPackage($ally, [
            'recipient_id_doc' => 'V-22222222',
            'requires_delivery' => true,
            'delivery_status' => Package::DELIVERY_PENDING,
        ]);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set(
                'rejectingPackageId',
                $package->id
            )
            ->set(
                'rejectionReason',
                'Yo no hice este pedido'
            )
            ->call('rejectDelivery')
            ->assertHasNoErrors();

        $package->refresh();

        $this->assertSame(
            Package::DELIVERY_REJECTED,
            $package->delivery_status
        );

        $this->assertSame(
            Package::REMUNERATION_CANCELLED,
            $package->driver_remuneration_status
        );

        $this->assertSame(
            1,
            Incident::query()
                ->where(
                    'package_id',
                    $package->id
                )
                ->where(
                    'type',
                    'ENTREGA_RECHAZADA'
                )
                ->count()
        );

        $this->assertSame(
            1,
            AuditLog::query()
                ->where(
                    'action',
                    'client.delivery_rejected'
                )
                ->where(
                    'target_id',
                    $package->id
                )
                ->count()
        );
    }

    public function test_client_cannot_respond_twice_to_the_same_delivery(): void
    {
        $ally = $this->createAlly();

        $user = $this->createClientUser(
            'cliente3@example.com'
        );

        Customer::create([
            'id_doc' => 'V-33333333',
            'name' => 'Cliente de Prueba 3',
            'phone' => '0414-0000002',
            'email' => $user->email,
        ]);

        /*
         * El paquete debe comenzar pendiente.
         * De esta manera la primera llamada representa
         * una respuesta válida del cliente.
         */
        $package = $this->createPackage($ally, [
            'recipient_id_doc' => 'V-33333333',
            'requires_delivery' => true,
            'delivery_status' => Package::DELIVERY_PENDING,
            'delivery_accepted_at' => null,
        ]);

        $component = Livewire::actingAs($user)
            ->test(Dashboard::class);

        /*
         * Primera respuesta:
         * el cliente acepta la entrega.
         */
        $component
            ->call('acceptDelivery', $package->id)
            ->assertHasNoErrors();

        $package->refresh();

        $this->assertSame(
            Package::DELIVERY_ACCEPTED,
            $package->delivery_status
        );

        /*
         * Segunda respuesta:
         * el paquete ya no está pendiente, por lo tanto
         * el componente debe rechazar la operación.
         *
         * El método acceptDelivery utiliza session()->flash()
         * y no errores de validación de Livewire.
         */
        $component
            ->call('acceptDelivery', $package->id)
            ->assertHasNoErrors();

        $package->refresh();

        /*
         * La segunda llamada no debe modificar nuevamente
         * el estado ni crear otra auditoría de aceptación.
         */
        $this->assertSame(
            Package::DELIVERY_ACCEPTED,
            $package->delivery_status
        );

        $this->assertSame(
            1,
            AuditLog::query()
                ->where(
                    'action',
                    'client.delivery_accepted'
                )
                ->where(
                    'target_id',
                    $package->id
                )
                ->count()
        );
    }

    /**
     * Dos Customer con documentos distintos pueden compartir
     * el mismo correo electrónico.
     *
     * El panel debe mostrar los paquetes asociados a ambos
     * documentos y no únicamente los del primer Customer.
     */
    public function test_dashboard_shows_packages_for_every_customer_sharing_the_same_email(): void
    {
        $ally = $this->createAlly();

        $user = $this->createClientUser(
            'familia@example.com'
        );

        Customer::create([
            'id_doc' => 'V-44444444',
            'name' => 'Familiar Uno',
            'phone' => '0414-0000003',
            'email' => $user->email,
        ]);

        Customer::create([
            'id_doc' => 'V-55555555',
            'name' => 'Familiar Dos',
            'phone' => '0414-0000004',
            'email' => $user->email,
        ]);

        $packageOne = $this->createPackage($ally, [
            'tracking_number' => 'VEN-TEST-FAM1',
            'recipient_id_doc' => 'V-44444444',
        ]);

        $packageTwo = $this->createPackage($ally, [
            'tracking_number' => 'VEN-TEST-FAM2',
            'recipient_id_doc' => 'V-55555555',
        ]);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->assertViewHas('packages', function ($packages) use (
                $packageOne,
                $packageTwo
            ) {
                $ids = $packages
                    ->pluck('id')
                    ->all();

                return in_array(
                    $packageOne->id,
                    $ids,
                    true
                ) && in_array(
                    $packageTwo->id,
                    $ids,
                    true
                );
            });
    }
}
