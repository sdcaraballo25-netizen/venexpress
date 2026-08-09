<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración.
     */
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | IDENTIFICACIÓN DE LA GUÍA
            |--------------------------------------------------------------------------
            */

            /**
             * Número único de seguimiento.
             *
             * Ejemplo:
             * VEN-20260808-000001
             */
            $table->string('tracking_number', 50)
                ->unique();

            /*
            |--------------------------------------------------------------------------
            | TAQUILLA ALIADA
            |--------------------------------------------------------------------------
            */

            /**
             * Aliado que registró el envío.
             */
            $table->foreignId('ally_id')
                ->constrained('allies')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | REMITENTE
            |--------------------------------------------------------------------------
            */

            $table->string('sender_name', 150);

            /**
             * Cédula o RIF del remitente.
             *
             * Ejemplos:
             * V-12345678
             * E-12345678
             * J-12345678-9
             * G-12345678-9
             */
            $table->string('sender_id_doc', 30);

            $table->string('sender_phone', 30);

            /*
            |--------------------------------------------------------------------------
            | DESTINATARIO
            |--------------------------------------------------------------------------
            */

            $table->string('recipient_name', 150);

            /**
             * Cédula o RIF del destinatario.
             */
            $table->string('recipient_id_doc', 30);

            $table->string('recipient_phone', 30);

            /*
            |--------------------------------------------------------------------------
            | RUTA
            |--------------------------------------------------------------------------
            */

            /**
             * Ciudad donde se registra el envío.
             */
            $table->string('origin_city');

            /**
             * Ciudad donde será retirado/entregado.
             */
            $table->string('destination_city');

            /*
            |--------------------------------------------------------------------------
            | TIPO DE PAQUETE
            |--------------------------------------------------------------------------
            */

            $table->enum('package_type', [
                'sobre',
                'paquete',
            ]);

            /*
            |--------------------------------------------------------------------------
            | PESO Y DIMENSIONES
            |--------------------------------------------------------------------------
            */

            /**
             * Peso físico real en kilogramos.
             */
            $table->decimal(
                'physical_weight_kg',
                10,
                3
            );

            /**
             * Dimensiones en centímetros.
             */
            $table->decimal(
                'length_cm',
                10,
                2
            )->nullable();

            $table->decimal(
                'width_cm',
                10,
                2
            )->nullable();

            $table->decimal(
                'height_cm',
                10,
                2
            )->nullable();

            /**
             * Peso volumétrico:
             *
             * Largo × Ancho × Alto / 5000
             */
            $table->decimal(
                'volumetric_weight_kg',
                10,
                3
            )->default(0);

            /**
             * Peso utilizado para calcular
             * el precio final.
             *
             * MAX(peso físico, peso volumétrico)
             */
            $table->decimal(
                'billable_weight_kg',
                10,
                3
            );

            /*
            |--------------------------------------------------------------------------
            | FACTURACIÓN
            |--------------------------------------------------------------------------
            */

            /**
             * Precio total del envío en USD.
             */
            $table->decimal(
                'total_price_usd',
                12,
                2
            );

            /**
             * Precio total convertido a VES
             * utilizando la tasa BCV vigente
             * al momento de crear la guía.
             */
            $table->decimal(
                'total_price_ves',
                15,
                2
            );

            /**
             * Tasa BCV utilizada para esta guía.
             *
             * Se almacena una copia histórica.
             */
            $table->decimal(
                'bcv_rate_used',
                15,
                6
            );

            /*
            |--------------------------------------------------------------------------
            | ESTADO DEL ENVÍO
            |--------------------------------------------------------------------------
            */

            /**
             * Máquina de estados de Venexpress.
             */
            $table->enum('current_status', [
                'RECIBIDO_AGENCIA',
                'RECOLECTADO_VENEXPRESS',
                'EN_HUB',
                'EN_TRANSITO_NACIONAL',
                'LISTO_RETIRO',
                'ENTREGADO',
            ])->default('RECIBIDO_AGENCIA');

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | ÍNDICES
            |--------------------------------------------------------------------------
            */

            /**
             * Búsqueda pública de guías.
             */
            $table->index('tracking_number');

            /**
             * Consultas del aliado.
             */
            $table->index([
                'ally_id',
                'current_status',
            ]);

            /**
             * Filtrado por rutas.
             */
            $table->index([
                'origin_city',
                'destination_city',
            ]);

            /**
             * Consultas por estado.
             */
            $table->index('current_status');

            /**
             * Búsquedas por documentos.
             */
            $table->index('sender_id_doc');
            $table->index('recipient_id_doc');
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};