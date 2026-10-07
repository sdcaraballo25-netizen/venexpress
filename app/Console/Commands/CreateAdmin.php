<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * Crea el primer Administrador Principal de un despliegue nuevo.
 *
 * Pensado para servidores sin consola (Render gratis): el contenedor lo
 * ejecuta al arrancar con ADMIN_EMAIL / ADMIN_PASSWORD. Si ya existe un
 * usuario con ese correo no hace nada, así que se puede correr en cada
 * arranque sin pisar la contraseña que el admin haya cambiado después.
 */
class CreateAdmin extends Command
{
    protected $signature = 'venexpress:create-admin
        {--email= : Correo del administrador}
        {--name=Administrador : Nombre visible}
        {--password= : Contraseña (si no se indica, se usa la variable ADMIN_PASSWORD o se pregunta)}';

    protected $description = 'Crea el Administrador Principal inicial si todavía no existe';

    public function handle(): int
    {
        $email = mb_strtolower(trim((string) $this->option('email')));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Indica un correo válido con --email.');

            return self::FAILURE;
        }

        if (User::where('email', $email)->exists()) {
            $this->info("Ya existe un usuario con el correo {$email}: no se modifica.");

            return self::SUCCESS;
        }

        $password = (string) ($this->option('password') ?: getenv('ADMIN_PASSWORD') ?: '');

        if ($password === '' && $this->input->isInteractive()) {
            $password = (string) $this->secret('Contraseña');
        }

        if (mb_strlen($password) < 10) {
            $this->error('La contraseña del administrador debe tener al menos 10 caracteres.');

            return self::FAILURE;
        }

        User::create([
            'name' => trim((string) $this->option('name')) ?: 'Administrador',
            'email' => $email,
            'password' => $password,
            'role' => User::ROLE_ADMIN_PRINCIPAL,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $this->info("Administrador Principal {$email} creado.");

        return self::SUCCESS;
    }
}
