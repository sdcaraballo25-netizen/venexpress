<?php

namespace Tests\Feature\Concerns;

use App\Models\Ally;
use App\Models\Emprendedor;
use App\Models\User;

/**
 * Mismo motivo que CreatesTestPackages: Emprendedor no tiene factory
 * todavía, y su creación válida requiere una Ally activa de retiro.
 */
trait CreatesTestEmprendedores
{
    protected function createActivePickupAlly(array $overrides = []): Ally
    {
        $user = User::factory()->create([
            'role' => User::ROLE_ALIADO,
            'status' => User::STATUS_ACTIVE,
        ]);

        return Ally::create(array_merge([
            'user_id' => $user->id,
            'business_name' => 'Agencia de Retiro ' . str()->random(5),
            'rif' => 'J-' . random_int(10000000, 99999999) . '-0',
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
            'address' => 'Av. Principal',
            'commission_percentage' => 10.00,
            'status' => Ally::STATUS_ACTIVE,
        ], $overrides));
    }

    protected function createEmprendedor(array $overrides = []): Emprendedor
    {
        $user = User::factory()->create([
            'role' => User::ROLE_EMPRENDEDOR,
            'status' => User::STATUS_ACTIVE,
        ]);

        return Emprendedor::create(array_merge([
            'user_id' => $user->id,
            'pickup_ally_id' => $this->createActivePickupAlly()->id,
            'business_name' => 'Tienda de Prueba ' . str()->random(5),
            'document_id' => 'V-' . random_int(10000000, 99999999),
            'status' => Emprendedor::STATUS_ACTIVE,
        ], $overrides));
    }
}
