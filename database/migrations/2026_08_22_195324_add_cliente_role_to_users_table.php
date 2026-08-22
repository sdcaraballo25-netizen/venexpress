<?php

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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', [
                'admin',
                'aliado',
                'chofer',
            ])->default('aliado')->change();
        });
    }
};