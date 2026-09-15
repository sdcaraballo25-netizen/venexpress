<?php

namespace Tests\Feature;

use App\Livewire\Client\Dashboard;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Cubre los flujos del panel de Cliente:
 *
 * - Confirmar la recepción de una entrega a domicilio (solo cuando el
 *   paquete está LISTO_RETIRO).
 * - Impedir una segunda respuesta sobre la misma entrega.
 * - Separar paquetes pendientes de los ya entregados (historial).
 * - Mostrar paquetes de varios clientes con el mismo correo.
 *
 * La opción de "rechazar entrega" se eliminó del panel de Cliente: no
 * tenía sentido de cara al usuario. No se tocó la columna
 * delivery_status ni la constante DELIVERY_REJECTED — solo dejó de
 * poder generarse desde aquí.
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

    public function test_client_can_accept_a_pending_delivery_once_the_package_is_ready_for_pickup(): void
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
            'current_status' => Package::STATUS_LISTO_RETIRO,
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

    public function test_client_cannot_accept_a_delivery_before_it_is_ready_for_pickup(): void
    {
        $ally = $this->createAlly();

        $user = $this->createClientUser(
            'cliente-temprano@example.com'
        );

        Customer::create([
            'id_doc' => 'V-99999999',
            'name' => 'Cliente Temprano',
            'phone' => '0414-0000099',
            'email' => $user->email,
        ]);

        $package = $this->createPackage($ally, [
            'recipient_id_doc' => 'V-99999999',
            'requires_delivery' => true,
            'current_status' => Package::STATUS_RECIBIDO_AGENCIA,
            'delivery_status' => Package::DELIVERY_PENDING,
        ]);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->call('acceptDelivery', $package->id)
            ->assertHasNoErrors();

        $package->refresh();

        $this->assertSame(
            Package::DELIVERY_PENDING,
            $package->delivery_status
        );

        $this->assertNull(
            $package->delivery_accepted_at
        );

        $this->assertSame(
            0,
            AuditLog::query()
                ->where('action', 'client.delivery_accepted')
                ->where('target_id', $package->id)
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
         * El paquete debe comenzar pendiente y listo para retiro.
         * De esta manera la primera llamada representa
         * una respuesta válida del cliente.
         */
        $package = $this->createPackage($ally, [
            'recipient_id_doc' => 'V-33333333',
            'requires_delivery' => true,
            'current_status' => Package::STATUS_LISTO_RETIRO,
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

    /**
     * La pestaña "Pendientes" (vista por defecto) nunca debe incluir
     * paquetes ya ENTREGADO: esos viven en "Historial".
     */
    public function test_pending_tab_excludes_delivered_packages(): void
    {
        $ally = $this->createAlly();

        $user = $this->createClientUser(
            'cliente-pendiente@example.com'
        );

        Customer::create([
            'id_doc' => 'V-66666666',
            'name' => 'Cliente Pendiente',
            'phone' => '0414-0000005',
            'email' => $user->email,
        ]);

        $pending = $this->createPackage($ally, [
            'tracking_number' => 'VEN-TEST-PEND',
            'recipient_id_doc' => 'V-66666666',
            'current_status' => Package::STATUS_EN_HUB,
        ]);

        $delivered = $this->createPackage($ally, [
            'tracking_number' => 'VEN-TEST-DELIV',
            'recipient_id_doc' => 'V-66666666',
            'current_status' => Package::STATUS_ENTREGADO,
            'delivery_completed_at' => now(),
        ]);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->assertViewHas('packages', function ($packages) use ($pending, $delivered) {
                $ids = $packages->pluck('id')->all();

                return in_array($pending->id, $ids, true)
                    && ! in_array($delivered->id, $ids, true);
            });
    }

    /**
     * La pestaña "Historial" solo muestra paquetes ENTREGADO, del más
     * reciente al más antiguo.
     */
    public function test_history_tab_shows_only_delivered_packages_most_recent_first(): void
    {
        $ally = $this->createAlly();

        $user = $this->createClientUser(
            'cliente-historial@example.com'
        );

        Customer::create([
            'id_doc' => 'V-77777777',
            'name' => 'Cliente Historial',
            'phone' => '0414-0000006',
            'email' => $user->email,
        ]);

        $pending = $this->createPackage($ally, [
            'tracking_number' => 'VEN-TEST-PEND2',
            'recipient_id_doc' => 'V-77777777',
            'current_status' => Package::STATUS_EN_HUB,
        ]);

        $olderDelivery = $this->createPackage($ally, [
            'tracking_number' => 'VEN-TEST-OLD',
            'recipient_id_doc' => 'V-77777777',
            'current_status' => Package::STATUS_ENTREGADO,
            'delivery_completed_at' => now()->subDays(5),
        ]);

        $recentDelivery = $this->createPackage($ally, [
            'tracking_number' => 'VEN-TEST-RECENT',
            'recipient_id_doc' => 'V-77777777',
            'current_status' => Package::STATUS_ENTREGADO,
            'delivery_completed_at' => now()->subDay(),
        ]);

        $component = Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->call('showHistory');

        $historyIds = $component
            ->viewData('historyPackages')
            ->pluck('id')
            ->all();

        $this->assertSame(
            [$recentDelivery->id, $olderDelivery->id],
            $historyIds
        );

        $this->assertNotContains($pending->id, $historyIds);
    }

    /**
     * El filtro de fecha del historial acota por delivery_completed_at.
     */
    public function test_history_tab_can_be_filtered_by_date_range(): void
    {
        $ally = $this->createAlly();

        $user = $this->createClientUser(
            'cliente-filtro@example.com'
        );

        Customer::create([
            'id_doc' => 'V-88888888',
            'name' => 'Cliente Filtro',
            'phone' => '0414-0000007',
            'email' => $user->email,
        ]);

        $outOfRange = $this->createPackage($ally, [
            'tracking_number' => 'VEN-TEST-FUERA',
            'recipient_id_doc' => 'V-88888888',
            'current_status' => Package::STATUS_ENTREGADO,
            'delivery_completed_at' => now()->subDays(30),
        ]);

        $inRange = $this->createPackage($ally, [
            'tracking_number' => 'VEN-TEST-DENTRO',
            'recipient_id_doc' => 'V-88888888',
            'current_status' => Package::STATUS_ENTREGADO,
            'delivery_completed_at' => now()->subDays(2),
        ]);

        $component = Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->call('showHistory')
            ->set('historyFrom', now()->subDays(5)->toDateString());

        $historyIds = $component
            ->viewData('historyPackages')
            ->pluck('id')
            ->all();

        $this->assertSame([$inRange->id], $historyIds);
        $this->assertNotContains($outOfRange->id, $historyIds);
    }

    /**
     * El panel de cliente ya no ofrece rechazar la entrega.
     */
    public function test_dashboard_does_not_offer_rejecting_a_delivery(): void
    {
        $ally = $this->createAlly();

        $user = $this->createClientUser(
            'cliente-sin-rechazo@example.com'
        );

        Customer::create([
            'id_doc' => 'V-10101010',
            'name' => 'Cliente Sin Rechazo',
            'phone' => '0414-0000008',
            'email' => $user->email,
        ]);

        $this->createPackage($ally, [
            'tracking_number' => 'VEN-TEST-NORECHAZO',
            'recipient_id_doc' => 'V-10101010',
            'requires_delivery' => true,
            'current_status' => Package::STATUS_LISTO_RETIRO,
            'delivery_status' => Package::DELIVERY_PENDING,
        ]);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->assertDontSee('Rechazar entrega')
            ->assertSee('Confirmar recepción a domicilio');

        $this->assertFalse(
            method_exists(Dashboard::class, 'rejectDelivery')
        );
    }
}
