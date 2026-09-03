<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
    public const ROLE_ALIADO_TAQUILLA = 'aliado_taquilla';
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
        'ally_id',
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

    /**
     * Agencia aliada de la que este usuario es dueño (role 'aliado').
     * Relación inversa de Ally::user() — sin cambios respecto a antes.
     */
    public function ally(): HasOne
    {
        return $this->hasOne(Ally::class);
    }

    /**
     * Agencia aliada a la que pertenece este usuario cuando es
     * personal de Taquilla (role 'aliado_taquilla'). Usa la columna
     * users.ally_id, distinta de la relación de dueño de arriba.
     */
    public function alliedAgency(): BelongsTo
    {
        return $this->belongsTo(Ally::class, 'ally_id');
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

    public function isAliadoTaquilla(): bool
    {
        return $this->role === self::ROLE_ALIADO_TAQUILLA;
    }

    /**
     * True si el usuario opera dentro del módulo Aliado,
     * sin importar si es Administrador o Taquilla.
     */
    public function isAliadoModule(): bool
    {
        return $this->isAliado() || $this->isAliadoTaquilla();
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

    /**
     * Resuelve la agencia aliada de este usuario sin importar si es
     * el dueño (Administrador) o personal de Taquilla.
     */
    public function resolveAlly(): ?Ally
    {
        if ($this->isAliado()) {
            return $this->ally;
        }

        if ($this->isAliadoTaquilla()) {
            return $this->alliedAgency;
        }

        return null;
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
                self::ROLE_ALIADO_TAQUILLA,
                self::ROLE_REPARTIDOR,
                self::ROLE_CLIENTE,
            ], true);
        }

        if ($this->isAdminOperativo()) {
            return in_array($role, [
                self::ROLE_ADMIN_OPERATIVO,
                self::ROLE_ALIADO,
                self::ROLE_ALIADO_TAQUILLA,
                self::ROLE_REPARTIDOR,
                self::ROLE_CLIENTE,
            ], true);
        }

        // El Aliado Administrador puede crear usuarios de Taquilla
        // de su propia agencia (RF-ALI-02). El scoping por agencia
        // se valida en AllyStaffService, no aquí.
        if ($this->isAliado()) {
            return $role === self::ROLE_ALIADO_TAQUILLA;
        }

        return false;
    }

    public function canEditUser(User $target): bool
    {
        if ($this->isAdmin()) {
            return ! ($this->isAdminOperativo() && $target->isAdmin());
        }

        if ($this->isAliado()) {
            return $target->isAliadoTaquilla()
                && $target->ally_id === optional($this->ally)->id;
        }

        return false;
    }

    public function canDeactivateUser(User $target): bool
    {
        if ($this->is($target)) {
            return false;
        }

        if ($this->isAdmin()) {
            return ! ($this->isAdminOperativo() && $target->isAdmin());
        }

        if ($this->isAliado()) {
            return $target->isAliadoTaquilla()
                && $target->ally_id === optional($this->ally)->id;
        }

        return false;
    }

    public function canDeleteUser(User $target): bool
    {
        return $this->isAdminPrincipal()
            && ! $this->is($target);
    }

    /**
     * True si este usuario tiene historial operativo que impide
     * borrarlo de forma segura sin romper integridad referencial:
     *
     * - Un Aliado (dueño o taquilla) con guías registradas en su agencia.
     * - Un Repartidor con pagos ya generados.
     *
     * Se usa para bloquear el borrado desde la UI antes de intentar
     * el delete, en vez de dejar que la excepción de FK la detenga.
     */
    public function hasOperationalHistory(): bool
    {
        $ally = $this->resolveAlly();

        if ($ally && $ally->packages()->exists()) {
            return true;
        }

        if ($this->isRepartidor() && $this->driver?->payments()->exists()) {
            return true;
        }

        return false;
    }

    public static function roleLabels(): array
    {
        return [
            self::ROLE_ADMIN_PRINCIPAL => 'Administrador Principal',
            self::ROLE_ADMIN_OPERATIVO => 'Administrador Operativo',
            self::ROLE_ALIADO => 'Aliado Administrador',
            self::ROLE_ALIADO_TAQUILLA => 'Aliado Taquilla',
            self::ROLE_REPARTIDOR => 'Repartidor',
            self::ROLE_CLIENTE => 'Cliente',
        ];
    }
}
