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
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->string('email')
                ->unique();

            $table->timestamp('email_verified_at')
                ->nullable();

            $table->string('password');

            /**
             * Roles disponibles en Venexpress:
             *
             * admin   → Administración central
             * aliado  → Taquilla aliada
             * chofer  → Operaciones de campo
             */
            $table->enum('role', [
                'admin',
                'aliado',
                'chofer',
            ])->default('aliado')->index();

            $table->rememberToken();

            $table->timestamps();
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};