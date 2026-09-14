<?php

namespace App\Services;

use App\Models\Ally;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AllyStaffService
{
    /**
     * Registra un nuevo usuario de Taquilla para una agencia aliada
     * (RF-ALI-02). Es un User normal con role 'aliado_taquilla' y
     * ally_id apuntando a la agencia.
     *
     * Inicia sesión con un "usuario" simple (ej. "taquilla1"), no con
     * un correo real — un negocio con varias taquillas no debería
     * tener que inventarse un correo distinto para cada una. La
     * columna `email` sigue siendo NOT NULL en la base de datos, así
     * que se genera un correo técnico interno a partir del username
     * (nadie lo ve ni lo usa) y se marca como verificado de una vez:
     * esta cuenta la crea el propio Aliado Administrador, no se
     * autorregistra, así que no hay nada que verificar por correo.
     *
     * @param array{name:string, username:string, password:string} $data
     */
    public function create(Ally $ally, array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $this->syntheticEmail($data['username']),
            'email_verified_at' => now(),
            'password' => Hash::make($data['password']),
            'role' => User::ROLE_ALIADO_TAQUILLA,
            'ally_id' => $ally->id,
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    /**
     * Actualiza nombre/usuario y, opcionalmente, contraseña de un
     * usuario de Taquilla. La contraseña solo cambia si viene
     * presente y no vacía. Si el username cambia, el correo técnico
     * se regenera para mantenerlos en sincronía (a nadie le importa
     * su valor, pero debe seguir siendo único).
     */
    public function update(User $staff, array $data): User
    {
        $payload = [
            'name' => $data['name'] ?? $staff->name,
        ];

        if (! empty($data['username']) && $data['username'] !== $staff->username) {
            $payload['username'] = $data['username'];
            $payload['email'] = $this->syntheticEmail($data['username']);
        }

        if (! empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        $staff->update($payload);

        return $staff->fresh();
    }

    /**
     * Correo técnico interno, único, que satisface la columna NOT
     * NULL `users.email` sin exponer nada real. El dominio
     * ".invalid" está reservado por RFC 2606 justo para esto: nunca
     * se resuelve ni se puede registrar de verdad.
     */
    protected function syntheticEmail(string $username): string
    {
        return strtolower($username).'+'.Str::random(6).'@taquilla.invalid';
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
