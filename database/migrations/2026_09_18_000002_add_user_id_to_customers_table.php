<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Antes, un cliente se vinculaba a sus guías únicamente por
 * customers.email = users.email — una comparación intencional para
 * que varios familiares con cédulas distintas puedan compartir un
 * correo y ver sus paquetes juntos (ver comentario en
 * Client\Dashboard::customerIdDocsForCurrentUser()). Pero esa misma
 * comparación por string deja dos huecos:
 *
 * 1. Si el cliente cambia su email desde su perfil, pierde el acceso
 *    a su propio historial (el Customer sigue con el email viejo).
 * 2. Nada distingue "un aliado tecleó este email al despachar una
 *    guía" de "esta persona demostró ser dueña de esta cédula
 *    registrándose con ella" — la validación de registro
 *    (resources/views/livewire/pages/auth/register.blade.php) solo
 *    bloqueaba re-registrar una cédula si el Customer YA tenía algún
 *    email, no si ya había sido reclamada por una cuenta real.
 *
 * user_id ancla de forma durable "qué cuenta demostró ser dueña de
 * esta cédula al registrarse", sin tocar ni reemplazar el
 * comportamiento de compartir email entre familiares, que sigue
 * intacto.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->unique()
                ->after('id_doc')
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
