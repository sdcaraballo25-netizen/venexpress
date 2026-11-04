<?php

namespace Tests\Feature\Ally;

use App\Livewire\Admin\AllyFinance;
use App\Livewire\Admin\UsersManager;
use App\Livewire\Ally\FranchiseFinance;
use App\Livewire\Ally\SalesCloseout;
use App\Livewire\Ally\StaffManager;
use App\Models\Ally;
use App\Models\FranchiseExpense;
use App\Models\User;
use App\Services\AllyStaffService;
use App\Services\PackageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Rol Franquiciado: mismas funciones que el Aliado Administrador, pero
 * lo crea un Admin (sin postulación), no genera comisión/liquidación
 * con la empresa y tiene su propio panel de Finanzas.
 */
class FranchiseTest extends TestCase
{
    use CreatesTestPackages;
    use RefreshDatabase;

    private function createFranchise(array $overrides = []): Ally
    {
        $ally = $this->createAlly(array_merge(['commission_percentage' => 0], $overrides));
        $ally->user->update(['role' => User::ROLE_FRANQUICIADO, 'account_verified_at' => now()]);

        return $ally->fresh();
    }

    private function createTaquilla(Ally $ally, string $username = 'taquilla1', string $name = 'Taquilla Centro'): User
    {
        return app(AllyStaffService::class)->create($ally, [
            'name' => $name,
            'username' => $username,
            'password' => 'password-seguro',
        ]);
    }

    private function admin(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_ADMIN_PRINCIPAL,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
            'password' => 'admin-password',
        ]);
    }

    public function test_admin_creates_a_franchise_that_can_operate_immediately_without_commission(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(UsersManager::class)
            ->set('name', 'Franquicia Maracay')
            ->set('email', 'franquicia@example.com')
            ->set('password', 'Password-seguro-123')
            ->set('password_confirmation', 'Password-seguro-123')
            ->set('role', User::ROLE_FRANQUICIADO)
            ->set('business_name', 'Venexpress Maracay')
            ->set('rif', 'J-12345678-9')
            ->set('state', 'Aragua')
            ->set('city', 'Maracay')
            ->set('address', 'Av. Bolívar')
            ->call('requestCreate')
            ->assertHasNoErrors()
            ->set('adminPassword', 'admin-password')
            ->call('createUser')
            ->assertHasNoErrors();

        $user = User::where('email', 'franquicia@example.com')->firstOrFail();

        $this->assertTrue($user->isFranquiciado());
        $this->assertTrue($user->isAgencyOwner());
        $this->assertTrue($user->isAccountVerified());
        $this->assertSame('ally.dashboard', $user->homeRouteName());

        $ally = $user->ally;
        $this->assertNotNull($ally);
        $this->assertTrue($ally->isFranchise());
        $this->assertTrue($ally->canOperate());
        $this->assertSame(0.0, (float) $ally->commission_percentage);
    }

    public function test_franchise_cannot_self_register_from_the_public_form(): void
    {
        $this->get(route('register', ['role' => 'franquiciado']))
            ->assertOk()
            ->assertDontSee('value="franquiciado"', false);
    }

    public function test_franchise_sales_never_generate_commission_even_if_a_percentage_is_set(): void
    {
        $franchise = $this->createFranchise(['commission_percentage' => 15]);
        $ally = $this->createAlly(['commission_percentage' => 10]);

        $calculate = function (Ally $target) {
            $method = new \ReflectionMethod(PackageService::class, 'calculateCommission');
            $method->setAccessible(true);

            return $method->invoke(app(PackageService::class), $target->id, 100.0);
        };

        $this->assertSame(0.0, (float) $calculate($franchise)['amount']);
        $this->assertSame(10.0, (float) $calculate($ally)['amount']);
    }

    public function test_franchise_has_owner_routes_but_not_commission_routes(): void
    {
        $franchise = $this->createFranchise();
        $user = $franchise->user;

        $this->actingAs($user);

        $this->get(route('ally.dashboard'))->assertOk()->assertSee('Ver finanzas');
        $this->get(route('ally.franchise-finance'))->assertOk()->assertSee('Utilidad neta');
        $this->get(route('ally.staff'))->assertOk();
        $this->get(route('ally.reports'))->assertOk();
        $this->get(route('ally.sales-closeout'))->assertOk();
        $this->get(route('ally.packages.create'))->assertOk();

        $this->get(route('ally.commissions'))->assertForbidden();
        $this->get(route('ally.cash-cut'))->assertForbidden();
    }

    public function test_regular_ally_and_taquilla_cannot_open_franchise_finance(): void
    {
        $ally = $this->createAlly();
        $franchise = $this->createFranchise();
        $taquilla = $this->createTaquilla($franchise);

        $this->actingAs($ally->user)->get(route('ally.franchise-finance'))->assertForbidden();
        $this->actingAs($taquilla)->get(route('ally.franchise-finance'))->assertForbidden();
    }

    public function test_franchise_manages_its_own_taquillas_and_sees_their_sales(): void
    {
        $franchise = $this->createFranchise();

        Livewire::actingAs($franchise->user)
            ->test(StaffManager::class)
            ->assertOk();

        $taquilla = $this->createTaquilla($franchise);

        $this->assertTrue($franchise->user->canEditUser($taquilla));
        $this->assertTrue($franchise->user->canCreateRole(User::ROLE_ALIADO_TAQUILLA));

        $this->createPackage($franchise, ['registered_by_user_id' => $taquilla->id, 'payment_method' => 'efectivo_usd', 'total_price_usd' => 25]);

        Livewire::actingAs($franchise->user)
            ->test(SalesCloseout::class)
            ->assertSet('registeredBy', 'all')
            ->assertSee('Taquilla Centro')
            ->assertSee('25.00');
    }

    public function test_finance_panel_computes_sales_per_taquilla_expenses_and_net_profit(): void
    {
        $franchise = $this->createFranchise();
        $taquilla = $this->createTaquilla($franchise);
        $other = $this->createAlly();

        $this->createPackage($franchise, ['registered_by_user_id' => $franchise->user_id, 'payment_method' => 'efectivo_usd', 'total_price_usd' => 40]);
        $this->createPackage($franchise, ['registered_by_user_id' => $taquilla->id, 'payment_method' => 'pago_movil', 'total_price_usd' => 60]);
        // Venta de otra agencia: nunca debe contarse.
        $this->createPackage($other, ['registered_by_user_id' => $other->user_id, 'total_price_usd' => 999]);

        $component = Livewire::actingAs($franchise->user)
            ->test(FranchiseFinance::class)
            ->set('expenseCategory', 'alquiler')
            ->set('expenseDescription', 'Alquiler del local')
            ->set('expenseAmount', '30')
            ->set('expenseDate', now()->toDateString())
            ->call('addExpense')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('franchise_expenses', [
            'ally_id' => $franchise->id,
            'category' => 'alquiler',
            'amount_usd' => 30,
        ]);

        $component
            ->assertViewHas('grossSales', 100.0)
            ->assertViewHas('guides', 2)
            ->assertViewHas('expensesTotal', 30.0)
            ->assertViewHas('netProfit', 70.0)
            ->assertSee('Taquilla Centro');

        // Filtrar por una taquilla afecta las ventas, no los gastos/utilidad.
        $component
            ->set('registeredBy', (string) $taquilla->id)
            ->assertViewHas('grossSales', 60.0)
            ->assertViewHas('netProfit', 70.0);

        // Un id ajeno a la franquicia se descarta.
        $component
            ->set('registeredBy', (string) $other->user_id)
            ->assertSet('registeredBy', 'all');
    }

    public function test_franchise_cannot_delete_another_franchise_expense(): void
    {
        $franchise = $this->createFranchise();
        $otherFranchise = $this->createFranchise();

        $foreign = FranchiseExpense::create([
            'ally_id' => $otherFranchise->id,
            'category' => 'otro',
            'description' => 'Ajeno',
            'amount_usd' => 10,
            'spent_on' => now()->toDateString(),
        ]);

        Livewire::actingAs($franchise->user)
            ->test(FranchiseFinance::class)
            ->call('deleteExpense', $foreign->id)
            ->assertNotFound();

        $this->assertDatabaseHas('franchise_expenses', ['id' => $foreign->id]);
    }

    public function test_admin_ally_finance_excludes_franchises(): void
    {
        $franchise = $this->createFranchise(['business_name' => 'Franquicia Excluida']);
        $ally = $this->createAlly(['business_name' => 'Aliado Visible']);

        Livewire::actingAs($this->admin())
            ->test(AllyFinance::class)
            ->assertSee('Aliado Visible')
            ->assertDontSee('Franquicia Excluida');
    }
}
