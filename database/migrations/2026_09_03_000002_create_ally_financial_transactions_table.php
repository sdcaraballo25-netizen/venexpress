<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ally_financial_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ally_id')
                ->constrained('allies')
                ->restrictOnDelete();

            /*
             * credit:
             *   aumenta el saldo disponible.
             *
             * debit:
             *   disminuye el saldo disponible.
             */
            $table->enum('direction', [
                'credit',
                'debit',
            ]);

            /*
             * commission:
             * comisión generada por una guía.
             *
             * settlement:
             * dinero liquidado/pagado al aliado.
             *
             * adjustment:
             * corrección manual autorizada.
             *
             * reversal:
             * reverso de un movimiento anterior.
             */
            $table->enum('type', [
                'commission',
                'settlement',
                'adjustment',
                'reversal',
            ]);

            /*
             * Siempre almacenamos el importe como positivo.
             * direction determina si suma o resta.
             */
            $table->decimal(
                'amount_usd',
                12,
                2
            );

            /*
             * Origen del movimiento.
             *
             * Ejemplos:
             * App\Models\Package
             * App\Models\AllySettlement
             */
            $table->string(
                'source_type'
            )->nullable();

            $table->unsignedBigInteger(
                'source_id'
            )->nullable();

            /*
             * Movimiento que este registro revierte.
             */
            $table->foreignId(
                'reversed_transaction_id'
            )
                ->nullable()
                ->constrained(
                    'ally_financial_transactions'
                )
                ->restrictOnDelete();

            $table->string(
                'reference',
                100
            )->nullable();

            $table->string(
                'description',
                500
            );

            $table->json(
                'metadata'
            )->nullable();

            $table->foreignId(
                'created_by_user_id'
            )
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index([
                'ally_id',
                'created_at',
            ]);

            $table->index([
                'ally_id',
                'direction',
                'type',
            ]);

            $table->index([
                'source_type',
                'source_id',
            ]);

            $table->index(
                'reference'
            );
        });

        /*
         * Migramos las comisiones que ya existen en packages.
         *
         * Esto permite que el nuevo saldo no comience desde cero.
         */
        $packages = DB::table('packages')
            ->select([
                'id',
                'ally_id',
                'commission_amount_usd',
                'commission_percentage_used',
                'created_at',
            ])
            ->whereNotNull('commission_amount_usd')
            ->where('commission_amount_usd', '>', 0)
            ->get();

        foreach ($packages as $package) {
            DB::table('ally_financial_transactions')->insert([
                'ally_id' => $package->ally_id,
                'direction' => 'credit',
                'type' => 'commission',
                'amount_usd' => $package->commission_amount_usd,
                'source_type' => 'App\\Models\\Package',
                'source_id' => $package->id,
                'reference' => 'COM-' . $package->id,
                'description' => 'Comisión histórica de la guía #' . $package->id,
                'metadata' => json_encode([
                    'commission_percentage_used' =>
                        $package->commission_percentage_used,
                    'migrated_from_package' => true,
                ]),
                'created_by_user_id' => null,
                'created_at' => $package->created_at,
                'updated_at' => $package->created_at,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'ally_financial_transactions'
        );
    }
};
