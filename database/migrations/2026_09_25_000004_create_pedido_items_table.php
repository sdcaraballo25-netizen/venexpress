<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Convierte Pedido de "un producto por pedido" a un carrito real
     * (varios productos, siempre del mismo emprendedor — igual que
     * hoy, un pedido tiene un solo remitente/agencia de retiro). Cada
     * fila es una línea del carrito, con el mismo snapshot de precio
     * que ya tenía pedidos.precio_unitario_usd.
     *
     * pedidos conserva precio_total_usd (suma de las líneas) para no
     * tener que recalcularlo con un join en cada listado; pierde
     * producto_id/cantidad/precio_unitario_usd, que ahora viven en
     * pedido_items. Se migran los pedidos existentes antes de borrar
     * las columnas para no perder pedidos ya creados.
     */
    public function up(): void
    {
        Schema::create('pedido_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos');

            $table->unsignedInteger('cantidad');
            $table->decimal('precio_unitario_usd', 12, 2);
            $table->decimal('subtotal_usd', 12, 2);

            $table->timestamps();
        });

        $now = now();

        DB::table('pedidos')
            ->select('id', 'producto_id', 'cantidad', 'precio_unitario_usd', 'precio_total_usd')
            ->orderBy('id')
            ->each(function (object $pedido) use ($now) {
                DB::table('pedido_items')->insert([
                    'pedido_id' => $pedido->id,
                    'producto_id' => $pedido->producto_id,
                    'cantidad' => $pedido->cantidad,
                    'precio_unitario_usd' => $pedido->precio_unitario_usd,
                    'subtotal_usd' => $pedido->precio_total_usd,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            });

        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropForeign(['producto_id']);
            $table->dropColumn(['producto_id', 'cantidad', 'precio_unitario_usd']);
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->foreignId('producto_id')->nullable()->constrained('productos');
            $table->unsignedInteger('cantidad')->nullable();
            $table->decimal('precio_unitario_usd', 12, 2)->nullable();
        });

        DB::table('pedido_items')
            ->orderBy('id')
            ->each(function (object $item) {
                DB::table('pedidos')->where('id', $item->pedido_id)->update([
                    'producto_id' => $item->producto_id,
                    'cantidad' => $item->cantidad,
                    'precio_unitario_usd' => $item->precio_unitario_usd,
                ]);
            });

        Schema::dropIfExists('pedido_items');
    }
};
