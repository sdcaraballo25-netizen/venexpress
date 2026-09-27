<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Galería de fotos de un producto (antes solo foto_path, una
     * sola). productos.foto_path se conserva como respaldo para
     * productos creados antes de esta tabla — Producto::fotoPrincipal
     * usa la primera foto de la galería si existe, si no cae a
     * foto_path.
     */
    public function up(): void
    {
        Schema::create('producto_fotos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->string('path');
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_fotos');
    }
};
