<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('packages', function (Blueprint $table) {
            $table->string('delivery_confirmation_method', 30)->nullable()->after('delivery_completed_at');
            $table->string('receiver_name')->nullable()->after('delivery_confirmation_method');
            $table->string('receiver_id_doc')->nullable()->after('receiver_name');
            $table->string('receiver_phone')->nullable()->after('receiver_id_doc');
            $table->string('delivery_photo_path')->nullable()->after('receiver_phone');
            $table->timestamp('customer_confirmed_at')->nullable()->after('delivery_photo_path');
            $table->foreignId('customer_confirmed_by')->nullable()->after('customer_confirmed_at')->constrained('users')->nullOnDelete();
        });
    }
    public function down(): void {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropForeign(['customer_confirmed_by']);
            $table->dropColumn(['delivery_confirmation_method','receiver_name','receiver_id_doc','receiver_phone','delivery_photo_path','customer_confirmed_at','customer_confirmed_by']);
        });
    }
};
