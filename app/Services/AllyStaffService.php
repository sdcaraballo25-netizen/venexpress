<?php

namespace App\Services;

use App\Models\Ally;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AllyStaffService
{
    /**
     * Registra un nuevo usuario de Taquilla para una agencia aliada
     * (RF-ALI-02). Es un User normal con role 'aliado_taquilla' y
     * ally_id apuntando a la agencia.
     *
     * @param array{name:string, email:string, password:string} $data
     */
    public function create(Ally $ally, array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => User::ROLE_ALIADO_TAQUILLA,
            'ally_id' => $ally->id,
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    /**
     * Actualiza nombre/correo y, opcionalmente, contraseña de un
     * usuario de Taquilla. La contraseña solo cambia si viene
     * presente y no vacía.
     */
    public function update(User $staff, array $data): User
    {
        $payload = [
            'name' => $data['name'] ?? $staff->name,
            'email' => $data['email'] ?? $staff->email,
        ];

        if (! empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        $staff->update($payload);

        return $staff->fresh();
    }

    public function activate(User $staff): User
    {
        $staff->update(['status' => User::STATUS_ACTIVE]);

        return $staff->fresh();
    }

    public function deactivate(User $staff): User
    {
        $staff->update(['status' => User::STATUS_INACTIVE]);

        return $staff->fresh();
    }
}
