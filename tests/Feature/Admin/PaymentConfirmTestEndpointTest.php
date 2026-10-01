<?php

namespace Tests\Feature\Admin;

use App\Models\PaymentOrder;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * POST /admin/payments/{id}/confirm-test concilia un pago sin
 * verificarlo contra el banco. Antes cualquier admin (incluido el
 * Operativo) podía usarlo también en producción.
 */
class PaymentConfirmTestEndpointTest extends TestCase
{
    use RefreshDatabase;

    private function createOrder(): PaymentOrder
    {
        $payer = User::factory()->create();

        return PaymentOrder::query()->forceCreate([
            'order_number' => 'PO-TEST-1',
            'payer_type' => 'user',
            'payer_id' => $payer->id,
            'purpose' => 'other',
            'amount_usd' => 10,
            'payment_method' => 'pago_movil',
            'status' => 'pending',
        ]);
    }

    public function test_it_is_not_available_in_production(): void
    {
        $order = $this->createOrder();
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN_PRINCIPAL, 'status' => User::STATUS_ACTIVE]);

        $this->app['env'] = 'production';

        // CSRF se salta solo en el entorno "testing"; aquí no interesa.
        $this->withoutMiddleware(ValidateCsrfToken::class)
            ->actingAs($admin)
            ->postJson(route('admin.payments.confirm-test', $order), ['bank_reference' => 'REF-1'])
            ->assertNotFound();

        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_an_operational_admin_cannot_use_it(): void
    {
        $order = $this->createOrder();
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN_OPERATIVO, 'status' => User::STATUS_ACTIVE]);

        $this->actingAs($admin)
            ->postJson(route('admin.payments.confirm-test', $order), ['bank_reference' => 'REF-1'])
            ->assertForbidden();

        $this->assertSame('pending', $order->fresh()->status);
    }
}
