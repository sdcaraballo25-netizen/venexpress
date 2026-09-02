<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->string('delivery_status', 20)
                ->default('pendiente')
                ->after('current_status');

            $table->timestamp('delivery_accepted_at')
                ->nullable()
                ->after('delivery_status');

            $table->timestamp('delivery_rejected_at')
                ->nullable()
                ->after('delivery_accepted_at');

            $table->timestamp('delivery_completed_at')
                ->nullable()
                ->after('delivery_rejected_at');

            $table->text('delivery_rejection_reason')
                ->nullable()
                ->after('delivery_completed_at');

            $table->decimal('driver_remuneration_usd', 12, 2)
                ->nullable()
                ->after('delivery_rejection_reason');

            $table->string('driver_remuneration_status', 20)
                ->default('pendiente')
                ->after('driver_remuneration_usd');

            $table->timestamp('driver_remuneration_paid_at')
                ->nullable()
                ->after('driver_remuneration_status');

            $table->index([
                'delivery_status',
                'driver_remuneration_status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropIndex([
                'delivery_status',
                'driver_remuneration_status',
            ]);

            $table->dropColumn([
                'delivery_status',
                'delivery_accepted_at',
                'delivery_rejected_at',
                'delivery_completed_at',
                'delivery_rejection_reason',
                'driver_remuneration_usd',
                'driver_remuneration_status',
                'driver_remuneration_paid_at',
            ]);
        });
    }
};
