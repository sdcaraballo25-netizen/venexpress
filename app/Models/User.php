<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_ADMIN_PRINCIPAL = 'admin_principal';
    public const ROLE_ADMIN_OPERATIVO = 'admin_operativo';
    public const ROLE_ALIADO = 'aliado';
    public const ROLE_REPARTIDOR = 'repartidor';
    public const ROLE_CLIENTE = 'cliente';

    // Alias de compatibilidad para código existente.
    public const ROLE_ADMIN = self::ROLE_ADMIN_PRINCIPAL;
    public const ROLE_CHOFER = self::ROLE_REPARTIDOR;

    public const STATUS_ACTIVE = 'activo';
    public const STATUS_INACTIVE = 'inactivo';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function ally(): HasOne
    {
        return $this->hasOne(Ally::class);
    }

    public function driver(): HasOne
    {
        return $this->hasOne(Driver::class);
    }

    public function packageHistories(): HasMany
    {
        return $this->hasMany(PackageHistory::class, 'scanned_by_user_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'actor_user_id');
    }

    public function isAdminPrincipal(): bool
    {
        return $this->role === self::ROLE_ADMIN_PRINCIPAL;
    }

    public function isAdminOperativo(): bool
    {
        return $this->role === self::ROLE_ADMIN_OPERATIVO;
    }

    public function isAdmin(): bool
    {
        return $this->isAdminPrincipal() || $this->isAdminOperativo();
    }

    public function isAliado(): bool
    {
        return $this->role === self::ROLE_ALIADO;
    }

    public function isRepartidor(): bool
    {
        return $this->role === self::ROLE_REPARTIDOR;
    }

    public function isChofer(): bool
    {
        return $this->isRepartidor();
    }

    public function isCliente(): bool
    {
        return $this->role === self::ROLE_CLIENTE;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function canManageUsers(): bool
    {
        return $this->isAdmin();
    }

    public function canCreateRole(string $role): bool
    {
        if ($this->isAdminPrincipal()) {
            return in_array($role, [
                self::ROLE_ADMIN_PRINCIPAL,
                self::ROLE_ADMIN_OPERATIVO,
                self::ROLE_ALIADO,
                self::ROLE_REPARTIDOR,
                self::ROLE_CLIENTE,
            ], true);
        }

        if ($this->isAdminOperativo()) {
            return in_array($role, [
                self::ROLE_ADMIN_OPERATIVO,
                self::ROLE_ALIADO,
                self::ROLE_REPARTIDOR,
                self::ROLE_CLIENTE,
            ], true);
        }

        return false;
    }

    public function canEditUser(User $target): bool
    {
        if (! $this->isAdmin()) {
            return false;
        }

        if ($this->isAdminOperativo() && $target->isAdmin()) {
            return false;
        }

        return true;
    }

    public function canDeactivateUser(User $target): bool
    {
        if (! $this->isAdmin() || $this->is($target)) {
            return false;
        }

        if ($this->isAdminOperativo() && $target->isAdmin()) {
            return false;
        }

        return true;
    }

    public function canDeleteUser(User $target): bool
    {
        return $this->isAdminPrincipal()
            && ! $this->is($target);
    }

    public static function roleLabels(): array
    {
        return [
            self::ROLE_ADMIN_PRINCIPAL => 'Administrador Principal',
            self::ROLE_ADMIN_OPERATIVO => 'Administrador Operativo',
            self::ROLE_ALIADO => 'Aliado',
            self::ROLE_REPARTIDOR => 'Repartidor',
            self::ROLE_CLIENTE => 'Cliente',
        ];
    }
}
