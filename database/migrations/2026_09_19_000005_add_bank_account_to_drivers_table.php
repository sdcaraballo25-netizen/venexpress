<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cuenta bancaria a la que se le paga la remuneración al
     * Repartidor. Solo tiene sentido llenarla una vez está aprobado
     * (Driver::STATUS_ACTIVE) — ver Livewire\Profile\PayoutAccountForm.
     *
     * cedula: a diferencia de Ally (que ya tenía `rif`) o Customer
     * (que ya tenía `id_doc`), Driver nunca guardó el número de
     * cédula como texto — solo una foto de ella (id_photo_path). Se
     * necesita como texto para identificarlo en el reporte de
     * remuneraciones consolidado (columna "RIF o cédula").
     */
    public function up(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->string('cedula')
                ->nullable()
                ->after('phone');

            $table->string('bank_account_number')
                ->nullable()
                ->after('cedula');

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
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn([
                'cedula',
                'bank_account_number',
                'bank_account_holder_name',
                'bank_account_holder_id',
            ]);
        });
    }
};
