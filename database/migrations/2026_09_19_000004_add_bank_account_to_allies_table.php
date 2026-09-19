<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cuenta bancaria a la que se le paga la comisión al Aliado. Solo
     * tiene sentido llenarla una vez la agencia está aprobada
     * (Ally::STATUS_ACTIVE) — ver Livewire\Profile\PayoutAccountForm.
     */
    public function up(): void
    {
        Schema::table('allies', function (Blueprint $table) {
            $table->string('bank_account_number')
                ->nullable()
                ->after('commission_percentage');

            $table->string('bank_account_holder_name')
                ->nullable()
                ->after('bank_account_number');

            $table->string('bank_account_holder_id')
                ->nullable()
                ->after('bank_account_holder_name');
        });
    }

    public function down(): void
    {
        Schema::table('allies', function (Blueprint $table) {
            $table->dropColumn([
                'bank_account_number',
                'bank_account_holder_name',
                'bank_account_holder_id',
            ]);
        });
    }
};
