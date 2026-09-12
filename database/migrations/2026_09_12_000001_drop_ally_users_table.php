<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Elimina la tabla `ally_users`, remanente de un diseño anterior donde
 * los usuarios de Taquilla vivían en su propio modelo/tabla
 * (App\Models\AllyUser). Ese enfoque fue reemplazado por el modelo
 * único `User` con el rol string `aliado_taquilla` (ver
 * AllyStaffService), y AllyUser/AllyUserService quedaron huérfanos:
 * no los referencia nada más en el proyecto y, además,
 * AllyUserService llamaba a Ally::allyUsers(), una relación que ya
 * no existe en el modelo Ally.
 *
 * Se agrega esta migración nueva en lugar de borrar o editar la
 * migración original (2026_08_25_000001_create_ally_users_table)
 * para no romper el historial de `php artisan migrate` en entornos
 * donde esa migración ya haya corrido (ej. el entorno local de un
 * colaborador).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('ally_users');
    }

    /**
     * Recrea la tabla tal como la dejaba la migración original, por
     * si se necesita revertir. No restaura datos, solo la estructura.
     */
    public function down(): void
    {
        if (Schema::hasTable('ally_users')) {
            return;
        }

        Schema::create('ally_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ally_id')->constrained('allies')->cascadeOnDelete();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['ally_id', 'is_active']);
        });
    }
};
