<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->timestamp('cod_collected_at')->nullable()->after('cod_status');
            $table->foreignId('cod_collected_by_user_id')->nullable()->after('cod_collected_at')->constrained('users')->nullOnDelete();
            $table->index(['is_cod', 'cod_status', 'cod_collected_at']);
        });
    }
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropIndex(['is_cod', 'cod_status', 'cod_collected_at']);
            $table->dropConstrainedForeignId('cod_collected_by_user_id');
            $table->dropColumn('cod_collected_at');
        });
    }
};
