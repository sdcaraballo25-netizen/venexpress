<?php

namespace Tests\Feature\Concerns;

use App\Models\Ally;
use App\Models\Package;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Helpers compartidos para crear Allies y Packages válidos en tests,
 * sin depender de factories que el proyecto todavía no tiene para
 * estos modelos (solo existe UserFactory).
 */
trait CreatesTestPackages
{
    protected function createAlly(array $overrides = []): Ally
    {
        $user = User::factory()->create([
            'role' => User::ROLE_ALIADO,
            'status' => User::STATUS_ACTIVE,
        ]);

        return Ally::create(array_merge([
            'user_id' => $user->id,
            'business_name' => 'Agencia de prueba ' . Str::random(5),
            'rif' => 'J-' . random_int(10000000, 99999999) . '-' . random_int(0, 9),
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
            'address' => 'Av. Principal',
            'commission_percentage' => 10.00,
            'status' => Ally::STATUS_ACTIVE,
        ], $overrides));
    }

    /**
     * Crea un Package válido rellenando todas las columnas NOT NULL
     * sin default con valores razonables de prueba.
     */
    protected function createPackage(Ally $ally, array $overrides = []): Package
    {
        return Package::create(array_merge([
            'tracking_number' => 'VEN-TEST-' . Str::upper(Str::random(8)),
            'ally_id' => $ally->id,

            'sender_name' => 'Juan Pérez',
            'sender_id_doc' => 'V-12345678',
            'sender_phone' => '0414-1234567',

            'recipient_name' => 'María Gómez',
            'recipient_id_doc' => 'V-87654321',
            'recipient_phone' => '0424-7654321',

            'origin_city' => 'Caracas',
            'origin_state' => 'Distrito Capital',
            'destination_city' => 'Valencia',
            'destination_state' => 'Carabobo',
            'distance_km' => 150,

            'package_type' => Package::TYPE_PAQUETE,
            'physical_weight_kg' => 2.0,
            'billable_weight_kg' => 2.0,

            'total_price_usd' => 10.00,
            'total_price_ves' => 400.00,
            'bcv_rate_used' => 40.00,

            'current_status' => Package::STATUS_RECIBIDO_AGENCIA,
        ], $overrides));
    }
}
