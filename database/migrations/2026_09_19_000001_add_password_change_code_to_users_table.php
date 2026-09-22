<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Código de 6 dígitos enviado por correo para autorizar un cambio
     * de contraseña desde el perfil, en vez de pedir solo la
     * contraseña actual. Mismo patrón que verification_token (hash,
     * expiración, cooldown de reenvío), pero en columnas separadas
     * porque es un propósito distinto: uno verifica la cuenta al
     * registrarse, el otro autoriza un cambio de contraseña puntual.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('password_change_code')
                ->nullable()
                ->after('verification_token_last_sent_at');

            $table->timestamp('password_change_code_expires_at')
                ->nullable()
                ->after('password_change_code');

            $table->timestamp('password_change_code_last_sent_at')
                ->nullable()
                ->after('password_change_code_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'password_change_code',
                'password_change_code_expires_at',
                'password_change_code_last_sent_at',
            ]);
        });
    }
};
