<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega el soporte de verificación de cuenta por token (código
     * de 6 dígitos enviado al registrarse), y el teléfono del
     * usuario, que se necesita para poder notificar por SMS/WhatsApp
     * cuando esos canales se activen más adelante.
     *
     * verification_token nunca se guarda en claro: se almacena
     * hasheado (igual que la contraseña) para que un acceso a la
     * base de datos no permita verificar cuentas ajenas.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')
                ->nullable()
                ->after('email');

            $table->timestamp('account_verified_at')
                ->nullable()
                ->after('email_verified_at');

            $table->string('verification_token')
                ->nullable()
                ->after('account_verified_at');

            $table->timestamp('verification_token_expires_at')
                ->nullable()
                ->after('verification_token');

            $table->timestamp('verification_token_last_sent_at')
                ->nullable()
                ->after('verification_token_expires_at');
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'account_verified_at',
                'verification_token',
                'verification_token_expires_at',
                'verification_token_last_sent_at',
            ]);
        });
    }
};
