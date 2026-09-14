<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            /**
             * Usuario de acceso alternativo al correo, pensado para
             * Taquilla (RF-ALI-02): un negocio con varias taquillas no
             * debería tener que inventarse un correo distinto para
             * cada una. El login acepta correo O usuario (ver
             * LoginForm::authenticate()). email sigue siendo
             * obligatorio en la tabla — para Taquilla se genera un
             * correo técnico interno que nadie necesita ver ni usar
             * (ver AllyStaffService).
             */
            $table->string('username')->nullable()->unique()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('username');
        });
    }
};
