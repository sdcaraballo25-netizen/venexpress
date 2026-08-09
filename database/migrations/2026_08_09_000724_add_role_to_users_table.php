<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega el sistema de roles de Venexpress.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', [
                'admin',
                'aliado',
                'chofer',
            ])
                ->default('aliado')
                ->after('password')
                ->index();
        });
    }

    /**
     * Revierte el cambio.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex([
                'users_role_index',
            ]);

            $table->dropColumn('role');
        });
    }
};