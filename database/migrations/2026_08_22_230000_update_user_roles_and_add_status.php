<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('cliente')->change();
            $table->string('status')->default('activo')->after('role')->index();
        });

        DB::table('users')->where('role', 'admin')->update(['role' => 'admin_principal']);
        DB::table('users')->where('role', 'chofer')->update(['role' => 'repartidor']);
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'admin_principal')->update(['role' => 'admin']);
        DB::table('users')->where('role', 'repartidor')->update(['role' => 'chofer']);

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['users_status_index']);
            $table->dropColumn('status');
            $table->enum('role', ['admin', 'cliente', 'aliado', 'chofer'])->default('cliente')->change();
        });
    }
};
