<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Un pedido del marketplace. El pago entre cliente y emprendedor
     * se acuerda fuera de la plataforma (por eso no hay columnas de
     * pago aquí); lo único que Venexpress hace al confirmarlo es
     * generar la guía de envío (package_id) hacia el cliente.
     *
     * precio_unitario_usd es una foto del precio del catálogo al
     * momento del pedido (no cambia si el emprendedor edita el precio
     * después), igual que cualquier snapshot de precio en el sistema.
     */
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos');
            $table->foreignId('emprendedor_id')->constrained('emprendedores');
            $table->foreignId('package_id')->nullable()->constrained('packages')->nullOnDelete();

            $table->unsignedInteger('cantidad');
            $table->decimal('precio_unitario_usd', 12, 2);
            $table->decimal('precio_total_usd', 12, 2);

            $table->string('cliente_nombre');
            $table->string('cliente_id_doc');
            $table->string('cliente_telefono');
            $table->string('destino_ciudad');
            $table->string('destino_estado');

            $table->string('status')->default('PENDIENTE');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
