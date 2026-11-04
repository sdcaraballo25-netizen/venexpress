<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cuentas por cobrar de franquicias.
 *
 * El Franquiciado no genera comisión (ver Ally::isFranchise()), pero
 * le paga a Venexpress una cuota mensual: un porcentaje de sus ventas
 * del mes que define el Admin (allies.franchise_fee_percentage).
 *
 * - franchise_invoices: factura de un mes (N° de factura, monto
 *   facturado) con una foto de las ventas y el % aplicados al emitirla.
 * - franchise_payments: pagos registrados manualmente por el Admin contra
 *   esa factura (monto pagado, N° de voucher/referencia, fecha). Una
 *   factura puede recibir varios abonos.
 *
 * Saldo = pagado - facturado: > 0 queda a favor del franquiciado, < 0
 * lo que debe. Todo aditivo: no toca el ledger de aliados.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('allies', function (Blueprint $table) {
            $table->decimal('franchise_fee_percentage', 5, 2)
                ->default(0)
                ->after('commission_percentage');
        });

        Schema::create('franchise_invoices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ally_id')
                ->constrained('allies')
                ->restrictOnDelete();

            // Primer día del mes facturado.
            $table->date('period_month');

            $table->string('invoice_number', 60);

            $table->decimal('sales_total_usd', 12, 2);
            $table->decimal('fee_percentage', 5, 2);
            $table->decimal('expected_amount_usd', 12, 2);
            $table->decimal('invoice_amount_usd', 12, 2);

            $table->text('notes')->nullable();

            $table->foreignId('created_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(['ally_id', 'invoice_number']);
            $table->index(['ally_id', 'period_month']);
        });

        Schema::create('franchise_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('franchise_invoice_id')
                ->constrained('franchise_invoices')
                ->restrictOnDelete();

            // Redundante con la factura, para filtrar/exportar por
            // franquicia sin join.
            $table->foreignId('ally_id')
                ->constrained('allies')
                ->restrictOnDelete();

            $table->decimal('amount_paid_usd', 12, 2);

            // N° del voucher / referencia bancaria del pago.
            $table->string('payment_reference', 120)->nullable();

            $table->date('paid_on');

            $table->text('notes')->nullable();

            $table->foreignId('recorded_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['ally_id', 'paid_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('franchise_payments');
        Schema::dropIfExists('franchise_invoices');

        Schema::table('allies', function (Blueprint $table) {
            $table->dropColumn('franchise_fee_percentage');
        });
    }
};
