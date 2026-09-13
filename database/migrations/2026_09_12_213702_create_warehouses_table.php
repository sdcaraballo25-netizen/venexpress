<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración.
     *
     * Almacén propio de Venexpress (destino de una ruta de
     * HUB Distribución). No es un Ally: no tiene comisión, RIF ni
     * usuario dueño — es infraestructura interna, no un socio
     * comercial.
     */
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('city');
            $table->string('state');
            $table->string('address')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('city');
            $table->index('is_active');
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
