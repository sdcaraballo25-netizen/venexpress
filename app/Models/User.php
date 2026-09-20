<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasPublicId, Notifiable;

    public const ROLE_ADMIN_PRINCIPAL = 'admin_principal';
    public const ROLE_ADMIN_OPERATIVO = 'admin_operativo';
    public const ROLE_ALIADO = 'aliado';
    public const ROLE_ALIADO_TAQUILLA = 'aliado_taquilla';
    public const ROLE_REPARTIDOR = 'repartidor';
    public const ROLE_CLIENTE = 'cliente';
    public const ROLE_ALMACEN = 'almacen';

    // Alias de compatibilidad para código existente.
    public const ROLE_ADMIN = self::ROLE_ADMIN_PRINCIPAL;
    public const ROLE_CHOFER = self::ROLE_REPARTIDOR;

    public const STATUS_ACTIVE = 'activo';
    public const STATUS_INACTIVE = 'inactivo';

    protected $fillable = [
        'name',
        'email',
        'username',
        'phone',
        'password',
        'google_id',
        'role',
        'ally_id',
        'warehouse_id',
        'status',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'verification_token',
        'password_change_code',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'account_verified_at' => 'datetime',
            'verification_token_expires_at' => 'datetime',
            'verification_token_last_sent_at' => 'datetime',
            'password_change_code_expires_at' => 'datetime',
            'password_change_code_last_sent_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | VERIFICACIÓN DE CUENTA POR TOKEN
    |--------------------------------------------------------------------------
    |
    | Distinta del sistema nativo de verificación de correo de
    | Laravel (email_verified_at / MustVerifyEmail, que este proyecto
    | no usa). Este es un código corto de 6 dígitos que se envía al
    | registrarse por primera vez y que el usuario debe introducir
    | antes de poder entrar a su panel.
    |
    */

    public const VERIFICATION_TOKEN_TTL_MINUTES = 15;

    public const VERIFICATION_RESEND_COOLDOWN_SECONDS = 60;

    /**
     * Genera un nuevo código de verificación de 6 dígitos, lo guarda
     * hasheado y devuelve el código EN CLARO para poder enviarlo por
     * correo/SMS/WhatsApp. Una vez guardado, el valor en claro no se
     * puede recuperar de nuevo desde la base de datos.
     */
    public function generateVerificationToken(): string
    {
        $plainToken = (string) random_int(100000, 999999);

        $this->forceFill([
            'verification_token' => Hash::make($plainToken),
            'verification_token_expires_at' => now()->addMinutes(
                self::VERIFICATION_TOKEN_TTL_MINUTES
            ),
            'verification_token_last_sent_at' => now(),
        ])->save();

        return $plainToken;
    }

    /**
     * Compara el código en claro que escribió el usuario contra el
     * hash guardado, y valida que no haya expirado.
     */
    public function verificationTokenIsValid(string $plainToken): bool
    {
        if (! $this->verification_token) {
            return false;
        }

        if (
            $this->verification_token_expires_at
            && $this->verification_token_expires_at->isPast()
        ) {
            return false;
        }

        return Hash::check($plainToken, $this->verification_token);
    }

    /**
     * Marca la cuenta como verificada y limpia el token para que no
     * pueda reutilizarse.
     */
    public function markAccountAsVerified(): void
    {
        $this->forceFill([
            'account_verified_at' => now(),
            'verification_token' => null,
            'verification_token_expires_at' => null,
        ])->save();
    }

    public function isAccountVerified(): bool
    {
        return ! is_null($this->account_verified_at);
    }

    /**
     * Evita que un usuario spamee el botón "Reenviar código":
     * solo permite un reenvío cada
     * VERIFICATION_RESEND_COOLDOWN_SECONDS segundos.
     */
    public function canResendVerificationToken(): bool
    {
        if (! $this->verification_token_last_sent_at) {
            return true;
        }

        return $this->verification_token_last_sent_at
            ->addSeconds(self::VERIFICATION_RESEND_COOLDOWN_SECONDS)
            ->isPast();
    }

    /**
     * Segundos que faltan para poder reenviar el código (0 si ya se
     * puede). Útil para mostrar una cuenta regresiva en la UI.
     */
    public function secondsUntilCanResendVerificationToken(): int
    {
        if ($this->canResendVerificationToken()) {
            return 0;
        }

        return (int) now()->diffInSeconds(
            $this->verification_token_last_sent_at->addSeconds(
                self::VERIFICATION_RESEND_COOLDOWN_SECONDS
            ),
            false
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CÓDIGO PARA CAMBIAR CONTRASEÑA
    |--------------------------------------------------------------------------
    |
    | Cambiar la contraseña desde el perfil ya no basta con escribir la
    | contraseña actual: si alguien más la conoce (o la cuenta ya está
    | comprometida), podría bloquear al dueño real cambiándola. Este
    | código de 6 dígitos enviado al correo registrado exige acceso a
    | ese correo, igual que el token de verificación de cuenta, pero
    | en columnas separadas porque son propósitos distintos.
    |
    */

    public const PASSWORD_CHANGE_CODE_TTL_MINUTES = 15;

    public const PASSWORD_CHANGE_CODE_RESEND_COOLDOWN_SECONDS = 60;

    public function generatePasswordChangeCode(): string
    {
        $plainCode = (string) random_int(100000, 999999);

        $this->forceFill([
            'password_change_code' => Hash::make($plainCode),
            'password_change_code_expires_at' => now()->addMinutes(
                self::PASSWORD_CHANGE_CODE_TTL_MINUTES
            ),
            'password_change_code_last_sent_at' => now(),
        ])->save();

        return $plainCode;
    }

    public function passwordChangeCodeIsValid(string $plainCode): bool
    {
        if (! $this->password_change_code) {
            return false;
        }

        if (
            $this->password_change_code_expires_at
            && $this->password_change_code_expires_at->isPast()
        ) {
            return false;
        }

        return Hash::check($plainCode, $this->password_change_code);
    }

    /**
     * Invalida el código para que no pueda reutilizarse, ya sea
     * porque se usó para cambiar la contraseña o porque el usuario
     * canceló el proceso.
     */
    public function clearPasswordChangeCode(): void
    {
        $this->forceFill([
            'password_change_code' => null,
            'password_change_code_expires_at' => null,
        ])->save();
    }

    public function canResendPasswordChangeCode(): bool
    {
        if (! $this->password_change_code_last_sent_at) {
            return true;
        }

        return $this->password_change_code_last_sent_at
            ->addSeconds(self::PASSWORD_CHANGE_CODE_RESEND_COOLDOWN_SECONDS)
            ->isPast();
    }

    public function secondsUntilCanResendPasswordChangeCode(): int
    {
        if ($this->canResendPasswordChangeCode()) {
            return 0;
        }

        return (int) now()->diffInSeconds(
            $this->password_change_code_last_sent_at->addSeconds(
                self::PASSWORD_CHANGE_CODE_RESEND_COOLDOWN_SECONDS
            ),
            false
        );
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

    /**
     * Registro de Customer que este usuario reclamó al registrarse
     * como cliente con su cédula (ver register.blade.php).
     */
    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class);
    }

    /**
     * Mismo criterio que Client\Dashboard/Client\PendingPayments
     * (customerIdDocsForCurrentUser): un cliente puede tener varios
     * id_doc asociados a su cuenta. Se usa para el puntito de aviso en
     * "Pagos" del panel de Cliente.
     */
    public function hasPendingCodPayments(): bool
    {
        $idDocs = Customer::query()
            ->where('email', $this->email)
            ->orWhere('user_id', $this->id)
            ->pluck('id_doc');

        if ($idDocs->isEmpty()) {
            return false;
        }

        return Package::query()
            ->whereIn('recipient_id_doc', $idDocs)
            ->where('is_cod', true)
            ->where('cod_status', Package::COD_PENDIENTE)
            ->exists();
    }

    /**
     * Almacén propio de Venexpress al que pertenece este usuario
     * cuando es personal de almacén (role 'almacen').
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
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

    public function isAlmacen(): bool
    {
        return $this->role === self::ROLE_ALMACEN;
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
                self::ROLE_ALMACEN,
            ], true);
        }

        if ($this->isAdminOperativo()) {
            return in_array($role, [
                self::ROLE_ADMIN_OPERATIVO,
                self::ROLE_ALIADO,
                self::ROLE_ALIADO_TAQUILLA,
                self::ROLE_REPARTIDOR,
                self::ROLE_CLIENTE,
                self::ROLE_ALMACEN,
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

    /**
     * Nombre de la ruta "principal" de este usuario según su rol,
     * usado por el nav genérico (layouts.app) para que el link
     * "Dashboard" lleve a cada quien a su panel real en vez del
     * dashboard genérico de Breeze. Misma lógica de destino que usa
     * el login (resources/views/Livewire/pages/auth/login.blade.php).
     */
    public function homeRouteName(): string
    {
        return match (true) {
            $this->isCliente() => 'cliente.dashboard',
            $this->isChofer() => 'repartidor.dashboard',
            $this->isAliado() => 'ally.dashboard',
            $this->isAliadoTaquilla() => 'ally.packages.create',
            $this->isAlmacen() => 'almacen.dashboard',
            $this->isAdmin() => 'admin.dashboard',
            default => 'dashboard',
        };
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
            self::ROLE_ALMACEN => 'Personal de Almacén',
        ];
    }
}
