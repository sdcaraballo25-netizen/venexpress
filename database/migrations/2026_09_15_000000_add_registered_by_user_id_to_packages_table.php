<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            /**
             * Usuario (Aliado Administrador o Taquilla) que registró
             * esta guía. Antes solo quedaba en PackageHistory (evento
             * inmutable), sin columna propia — necesaria para poder
             * agrupar ventas por taquilla en el cierre del día y en
             * el resumen del Aliado Administrador (Gestión de
             * Taquillas).
             *
             * Nullable porque las guías creadas antes de esta
             * migración no tienen este dato.
             */
            $table->foreignId('registered_by_user_id')
                ->nullable()
                ->after('ally_id')
                ->constrained('users')
                ->nullOnDelete();

            $table->index(['ally_id', 'registered_by_user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropIndex(['ally_id', 'registered_by_user_id', 'created_at']);
            $table->dropConstrainedForeignId('registered_by_user_id');
        });
    }
};
