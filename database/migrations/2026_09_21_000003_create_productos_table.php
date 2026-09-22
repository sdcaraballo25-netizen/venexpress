<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catálogo mínimo del marketplace: un producto pertenece a un
     * Emprendedor. peso_kg es necesario porque TariffService cobra el
     * envío por peso, igual que cualquier paquete registrado por un
     * Aliado.
     */
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emprendedor_id')->constrained('emprendedores')->cascadeOnDelete();

            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->decimal('precio_usd', 12, 2);
            $table->decimal('peso_kg', 8, 3);
            $table->unsignedInteger('stock')->default(0);
            $table->boolean('activo')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
