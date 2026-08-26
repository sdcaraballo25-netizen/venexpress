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
        Schema::create('ally_users', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | AGENCIA ALIADA
            |--------------------------------------------------------------------------
            */

            /**
             * Agencia aliada a la que pertenece este usuario de Taquilla.
             *
             * El Aliado Administrador (dueño de la agencia, users.id vía
             * allies.user_id) gestiona estos usuarios desde su panel
             * (RF-ALI-02). El dueño NO vive en esta tabla: sigue siendo
             * un User normal, esta tabla es exclusiva del rol Taquilla.
             */
            $table->foreignId('ally_id')
                ->constrained('allies')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | CREDENCIALES
            |--------------------------------------------------------------------------
            */

            $table->string('name');

            $table->string('email')->unique();

            $table->timestamp('email_verified_at')->nullable();

            $table->string('password');

            $table->rememberToken();

            /*
            |--------------------------------------------------------------------------
            | ESTADO
            |--------------------------------------------------------------------------
            */

            /**
             * Permite al Aliado Administrador activar/desactivar
             * usuarios de Taquilla sin eliminarlos (RF-ALI-02).
             */
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['ally_id', 'is_active']);
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('ally_users');
    }
};
