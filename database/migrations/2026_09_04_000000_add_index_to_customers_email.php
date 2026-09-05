<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hallazgo de auditoría #6: customers.email no tenía ningún índice
 * (solo id_doc es unique). Client/Dashboard.php busca clientes por
 * email en cada carga del panel, así que sin índice esa consulta
 * hace un table scan completo a medida que crece la tabla.
 *
 * Deliberadamente NO se agrega un índice UNIQUE: como explica la
 * auditoría, es un caso raro pero legítimo que una persona administre
 * los envíos de varios familiares (cédulas distintas) usando un mismo
 * correo. Un unique rompería ese caso de uso real. La corrección de
 * fondo para ese escenario está en Client/Dashboard.php, que ahora
 * agrega los paquetes de TODOS los registros de Customer que
 * comparten el correo, en vez de tomar solo el primero encontrado.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['email']);
        });
    }
};
