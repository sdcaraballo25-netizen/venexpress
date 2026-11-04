<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Una franquicia (Ally cuyo dueño tiene role 'franquiciado') también
 * funciona como almacén de la red: recibe paquetes de los repartidores
 * HUB y los despacha a repartidores de reparto o a clientes. En vez de
 * duplicar esa lógica, la franquicia tiene su propio Warehouse
 * (misma dirección física), vinculado por warehouses.ally_id. Así
 * aparece como parada en las rutas HUB, en coberturas y en la app del
 * repartidor sin cambios en esos flujos.
 *
 * Aditiva: columna nullable; los almacenes propios de Venexpress quedan
 * con ally_id = null. Además crea el almacén de las franquicias que ya
 * existían antes de esta migración.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->foreignId('ally_id')
                ->nullable()
                ->after('id')
                ->unique()
                ->constrained('allies')
                ->nullOnDelete();
        });

        $franchises = DB::table('allies')
            ->join('users', 'users.id', '=', 'allies.user_id')
            ->where('users.role', User::ROLE_FRANQUICIADO)
            ->select('allies.*')
            ->get();

        foreach ($franchises as $ally) {
            DB::table('warehouses')->insert([
                'ally_id' => $ally->id,
                'name' => $ally->business_name,
                'city' => $ally->city ?? '',
                'state' => $ally->state ?? '',
                'address' => $ally->address,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Los almacenes creados para franquicias se conservan (no se
        // borran datos): solo se quita el vínculo.
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropForeign(['ally_id']);
            $table->dropUnique(['ally_id']);
        });

        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn('ally_id');
        });
    }
};
