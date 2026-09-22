<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Perfil de negocio de un usuario con role=emprendedor (módulo de
     * marketplace). Mismo patrón que Ally: un perfil 1-a-1 sobre un
     * User, con flujo de aprobación PENDIENTE -> ACTIVO por el admin.
     *
     * pickup_ally_id es la agencia aliada donde este emprendedor
     * entrega su mercancía para que Venexpress la despache (no hay
     * recolección a domicilio) — la misma agencia se usa como
     * origin_city/origin_state al generar la guía de un pedido.
     */
    public function up(): void
    {
        Schema::create('emprendedores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignId('pickup_ally_id')->nullable()->constrained('allies')->nullOnDelete();

            $table->string('business_name');
            $table->string('document_id');

            $table->string('status')->default('PENDIENTE');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emprendedores');
    }
};
