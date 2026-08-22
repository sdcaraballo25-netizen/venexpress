<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Roles disponibles en Venexpress.
     */
    public const ROLE_ADMIN = 'admin';
    public const ROLE_ALIADO = 'aliado';
    public const ROLE_CLIENTE = 'cliente';
    public const ROLE_CHOFER = 'chofer';

    /**
     * Los atributos que se pueden asignar de forma masiva.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * Los atributos que deben ocultarse en la serialización.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Obtiene los atributos que deben convertirse.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    /**
     * Registro de taquilla aliada asociado a este usuario (si el rol es aliado).
     */
    public function ally(): HasOne
    {
        return $this->hasOne(Ally::class);
    }

    /**
     * Registro de chofer asociado a este usuario (si el rol es chofer).
     */
    public function driver(): HasOne
    {
        return $this->hasOne(Driver::class);
    }

    /**
     * Eventos de historial de paquetes escaneados/registrados por este usuario.
     */
    public function packageHistories(): HasMany
    {
        return $this->hasMany(PackageHistory::class, 'scanned_by_user_id');
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS DE ROL
    |--------------------------------------------------------------------------
    */

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isAliado(): bool
    {
        return $this->role === self::ROLE_ALIADO;
    }

    public function isChofer(): bool
    {
        return $this->role === self::ROLE_CHOFER;
    }

    public function isCliente(): bool
    {
        return $this->role === self::ROLE_CLIENTE;
    }
}
