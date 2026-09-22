<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Perfil público de la tienda, mostrado en su página del
     * marketplace (public.marketplace.store) para que un comprador
     * que nunca oyó hablar del emprendedor pueda verificar que es un
     * negocio real antes de pagarle por fuera de la plataforma
     * (teléfono y correo ya existen en users.phone/email — Auth::user()
     * — no se duplican aquí).
     */
    public function up(): void
    {
        Schema::table('emprendedores', function (Blueprint $table) {
            $table->string('logo_path')->nullable()->after('business_name');
            $table->string('cover_photo_path')->nullable()->after('logo_path');
            $table->text('descripcion')->nullable()->after('cover_photo_path');
            $table->string('address')->nullable()->after('descripcion');
        });
    }

    public function down(): void
    {
        Schema::table('emprendedores', function (Blueprint $table) {
            $table->dropColumn(['logo_path', 'cover_photo_path', 'descripcion', 'address']);
        });
    }
};
