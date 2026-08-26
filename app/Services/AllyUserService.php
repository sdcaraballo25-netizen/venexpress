<?php

namespace App\Services;

use App\Models\Ally;
use App\Models\AllyUser;
use Illuminate\Support\Facades\Hash;

class AllyUserService
{
    /**
     * Registra un nuevo usuario de Taquilla para una agencia aliada
     * (RF-ALI-02).
     *
     * @param array{name:string, email:string, password:string} $data
     */
    public function create(Ally $ally, array $data): AllyUser
    {
        return $ally->allyUsers()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_active' => true,
        ]);
    }

    /**
     * Actualiza nombre/correo y, opcionalmente, contraseña de un
     * usuario de Taquilla. La contraseña solo se cambia si viene
     * presente y no vacía.
     */
    public function update(AllyUser $allyUser, array $data): AllyUser
    {
        $payload = [
            'name' => $data['name'] ?? $allyUser->name,
            'email' => $data['email'] ?? $allyUser->email,
        ];

        if (! empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        $allyUser->update($payload);

        return $allyUser->fresh();
    }

    public function activate(AllyUser $allyUser): AllyUser
    {
        $allyUser->update(['is_active' => true]);

        return $allyUser->fresh();
    }

    public function deactivate(AllyUser $allyUser): AllyUser
    {
        $allyUser->update(['is_active' => false]);

        return $allyUser->fresh();
    }
}
