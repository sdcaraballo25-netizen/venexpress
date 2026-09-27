<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Categorías del catálogo del marketplace. Globales (no por
     * emprendedor) y administradas por el admin — mismo criterio que
     * Warehouse: pocas filas, sin necesitar búsqueda ni paginación.
     */
    public function up(): void
    {
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        $now = now();

        DB::table('categorias')->insert(collect([
            'Ropa y Accesorios',
            'Alimentos y Bebidas',
            'Hogar y Decoración',
            'Belleza y Cuidado Personal',
            'Tecnología',
            'Artesanías',
            'Otros',
        ])->map(fn (string $nombre) => [
            'nombre' => $nombre,
            'slug' => str($nombre)->slug(),
            'created_at' => $now,
            'updated_at' => $now,
        ])->all());
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias');
    }
};
