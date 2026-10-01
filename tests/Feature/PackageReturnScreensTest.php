<?php

namespace Tests\Feature;

use App\Livewire\Admin\PackageReturns as AdminPackageReturns;
use App\Livewire\Ally\PackageReturns as AllyPackageReturns;
use App\Livewire\Client\Dashboard as ClientDashboard;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Incident;
use App\Models\Package;
use App\Models\User;
use App\Services\PackageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

class PackageReturnScreensTest extends TestCase
{
    use CreatesTestPackages;
    use RefreshDatabase;

    private function admin(string $role = User::ROLE_ADMIN_PRINCIPAL): User
    {
        return User::factory()->create(['role' => $role, 'status' => User::STATUS_ACTIVE]);
    }

    private function startReturn(Package $package): Package
    {
        return app(PackageService::class)->startReturn($package, $this->admin()->id, 'Destinatario ausente');
    }

    public function test_an_admin_starts_a_return_from_the_returns_page_and_it_is_audited(): void
    {
        $admin = $this->admin(User::ROLE_ADMIN_OPERATIVO);
        $package = $this->createPackage($this->createAlly(), ['current_status' => Package::STATUS_LISTO_RETIRO]);

        $this->actingAs($admin)
            ->get(route('admin.package-returns', ['guia' => $package->tracking_number]))
            ->assertOk()
            ->assertSee($package->tracking_number)
            ->assertSee('Iniciar devolución');

        Livewire::actingAs($admin)
            ->test(AdminPackageReturns::class, ['trackingNumber' => $package->tracking_number])
            ->set('returnReason', 'No lo retiraron en 15 días')
            ->call('startReturn')
            ->assertSet('errorMessage', null)
            ->assertHasNoErrors();

        $this->assertSame(Package::STATUS_EN_DEVOLUCION, $package->fresh()->current_status);
        $this->assertTrue(AuditLog::where('action', 'package.return_started')->where('target_id', $package->id)->exists());
    }

    public function test_the_reason_is_required_on_the_admin_page(): void
    {
        $package = $this->createPackage($this->createAlly(), ['current_status' => Package::STATUS_EN_HUB]);

        Livewire::actingAs($this->admin())
            ->test(AdminPackageReturns::class, ['trackingNumber' => $package->tracking_number])
            ->set('returnReason', '')
            ->call('startReturn')
            ->assertHasErrors(['returnReason' => 'required']);

        $this->assertSame(Package::STATUS_EN_HUB, $package->fresh()->current_status);
    }

    public function test_non_admins_cannot_open_the_admin_returns_page(): void
    {
        $ally = $this->createAlly();

        $this->actingAs($ally->user)->get(route('admin.package-returns'))->assertForbidden();
    }

    public function test_incidents_link_to_starting_a_return(): void
    {
        $ally = $this->createAlly();
        $package = $this->createPackage($ally, ['current_status' => Package::STATUS_LISTO_RETIRO]);

        Incident::create([
            'ally_id' => $ally->id,
            'package_id' => $package->id,
            'type' => 'CLIENTE_AUSENTE',
            'description' => 'Nadie atendió',
            'status' => Incident::STATUS_OPEN,
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.incidents'))
            ->assertOk()
            ->assertSee(route('admin.package-returns', ['guia' => $package->tracking_number]), false);
    }

    public function test_the_origin_agency_hands_the_package_back_to_the_sender(): void
    {
        $ally = $this->createAlly();
        $package = $this->startReturn($this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_HUB,
            'sender_id_doc' => 'V-12345678',
        ]));

        $this->actingAs($ally->user)
            ->get(route('ally.packages.returns'))
            ->assertOk()
            ->assertSee($package->tracking_number);

        Livewire::actingAs($ally->user)
            ->test(AllyPackageReturns::class)
            ->call('select', $package->id)
            ->set('senderIdDoc', 'V-00000000')
            ->call('handBack')
            ->assertSet('error', 'El documento no coincide con el del remitente.')
            ->set('senderIdDoc', 'V-12345678')
            ->call('handBack')
            ->assertSet('error', null)
            ->assertSet('message', "Guía {$package->tracking_number} devuelta al remitente.");

        $this->assertSame(Package::STATUS_DEVUELTO, $package->fresh()->current_status);
    }

    public function test_taquilla_staff_of_the_origin_agency_can_hand_it_back_too(): void
    {
        $ally = $this->createAlly();
        $taquilla = User::factory()->create([
            'role' => User::ROLE_ALIADO_TAQUILLA,
            'status' => User::STATUS_ACTIVE,
            'ally_id' => $ally->id,
        ]);
        $package = $this->startReturn($this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_HUB,
            'sender_id_doc' => 'V-12345678',
        ]));

        Livewire::actingAs($taquilla)
            ->test(AllyPackageReturns::class)
            ->call('select', $package->id)
            ->set('senderIdDoc', 'V-12345678')
            ->call('handBack')
            ->assertSet('error', null);

        $this->assertSame(Package::STATUS_DEVUELTO, $package->fresh()->current_status);
    }

    public function test_another_agency_cannot_see_or_select_the_return(): void
    {
        $origin = $this->createAlly();
        $otherAlly = $this->createAlly();
        $package = $this->startReturn($this->createPackage($origin, ['current_status' => Package::STATUS_EN_HUB]));

        $this->actingAs($otherAlly->user)
            ->get(route('ally.packages.returns'))
            ->assertOk()
            ->assertDontSee($package->tracking_number);

        Livewire::actingAs($otherAlly->user)
            ->test(AllyPackageReturns::class)
            ->call('select', $package->id)
            ->assertSet('selectedPackageId', null)
            ->assertSet('error', 'Esa guía ya no está pendiente de devolución.');
    }

    public function test_a_returned_package_moves_to_the_clients_history(): void
    {
        $ally = $this->createAlly();
        $client = User::factory()->create([
            'role' => User::ROLE_CLIENTE,
            'status' => User::STATUS_ACTIVE,
            'account_verified_at' => now(),
        ]);
        Customer::create(['id_doc' => 'V-12345678', 'user_id' => $client->id, 'name' => 'Remitente', 'phone' => '0414']);

        $package = $this->startReturn($this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_HUB,
            'sender_id_doc' => 'V-12345678',
        ]));

        Livewire::actingAs($client)->test(ClientDashboard::class)
            ->assertSee($package->tracking_number);

        app(PackageService::class)->completeReturn($package, $ally->user->id, 'V-12345678', $ally);

        Livewire::actingAs($client)->test(ClientDashboard::class)
            ->assertDontSee($package->tracking_number)
            ->call('showHistory')
            ->assertSee($package->tracking_number)
            ->assertSee('Devuelto al remitente');
    }

    public function test_public_tracking_explains_the_return(): void
    {
        $ally = $this->createAlly();
        $package = $this->startReturn($this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_HUB,
            'sender_id_doc' => 'V-12345678',
        ]));

        $this->get(route('tracking.show', ['guia' => $package->tracking_number]))
            ->assertOk()
            ->assertSee('En devolución')
            ->assertSee('está siendo devuelto al remitente')
            ->assertDontSee('estado especial');

        app(PackageService::class)->completeReturn($package, $ally->user->id, 'V-12345678', $ally);

        $this->get(route('tracking.show', ['guia' => $package->tracking_number]))
            ->assertOk()
            ->assertSee('fue devuelto al remitente');
    }
}
