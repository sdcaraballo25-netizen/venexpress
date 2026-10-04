<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Desde ahora Aliado, Repartidor y Emprendedor también deben
     * verificar su correo con el código de 6 dígitos
     * (EnsureAccountIsVerified, igual que Cliente). Las cuentas de esos
     * roles que ya existían nunca recibieron ese código — antes no se
     * les pedía — y ya pasaron por la revisión de un admin, así que se
     * dan por verificadas para no bloquearlas de golpe.
     *
     * Solo completa account_verified_at donde está vacío: no cambia
     * esquema ni toca a los clientes (a ellos ya se les exigía).
     */
    public function up(): void
    {
        DB::table('users')
            ->whereIn('role', ['aliado', 'repartidor', 'emprendedor'])
            ->whereNull('account_verified_at')
            ->update([
                'account_verified_at' => DB::raw('COALESCE(email_verified_at, created_at, CURRENT_TIMESTAMP)'),
            ]);
    }

    /**
     * Sin reversa: no hay forma de distinguir las cuentas completadas
     * aquí de las que se verificaron con el código después.
     */
    public function down(): void
    {
        //
    }
};
