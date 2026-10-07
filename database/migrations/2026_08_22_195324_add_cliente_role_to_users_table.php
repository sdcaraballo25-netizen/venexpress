<?php

use App\Support\PostgresEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega el rol cliente al enum de usuarios.
     */
    public function up(): void
    {
        if (PostgresEnum::active()) {
            PostgresEnum::allow('users', 'role', ['admin', 'cliente', 'aliado', 'chofer'], 'cliente');

            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', [
                'admin',
                'cliente',
                'aliado',
                'chofer',
            ])->default('cliente')->change();
        });
    }

    /**
     * Revierte el cambio.
     */
    public function down(): void
    {
        if (PostgresEnum::active()) {
            PostgresEnum::allow('users', 'role', ['admin', 'aliado', 'chofer'], 'aliado');

            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', [
                'admin',
                'aliado',
                'chofer',
            ])->default('aliado')->change();
        });
    }
};