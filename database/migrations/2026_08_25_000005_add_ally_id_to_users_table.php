<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            /**
             * Agencia aliada a la que pertenece este usuario cuando
             * su role es 'aliado_taquilla' (RF-ALI-02).
             *
             * El Aliado Administrador (role 'aliado') NO usa esta
             * columna: su vínculo con la agencia sigue siendo
             * allies.user_id (ya existente, sin tocar).
             */
            $table->foreignId('ally_id')
                ->nullable()
                ->after('role')
                ->constrained('allies')
                ->nullOnDelete();

            $table->index(['ally_id', 'role']);
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['ally_id', 'role']);
            $table->dropConstrainedForeignId('ally_id');
        });
    }
};
