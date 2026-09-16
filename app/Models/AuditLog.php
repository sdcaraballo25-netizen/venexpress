<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    /**
     * Acciones de cliente que también quedan en esta tabla (no son
     * acciones administrativas, pero comparten el mismo AuditLog::create()
     * genérico) y que la Bitácora de auditoría oculta a propósito: pasan
     * una vez por cada paquete con entrega a domicilio, así que con el
     * tiempo terminan siendo la mayoría de las filas y ahogan las
     * acciones administrativas que sí importan revisar.
     */
    public const NOISY_CLIENT_ACTIONS = [
        'client.delivery_accepted',
        'client.delivery_rejected',
    ];

    /**
     * Etiquetas en español para las acciones registradas. Las que no
     * están aquí (por ejemplo, una acción nueva que todavía no se
     * agregó a esta lista) caen al fallback de humanizedAction(), que
     * nunca deja la celda vacía ni muestra el código crudo tal cual.
     */
    private const ACTION_LABELS = [
        'ally.approved' => 'Aliado aprobado',
        'ally.rejected' => 'Aliado rechazado',
        'ally.suspended' => 'Aliado suspendido',
        'ally.activated' => 'Aliado reactivado',
        'ally.location_updated' => 'Ubicación de aliado actualizada',
        'ally.destination_verification_toggled' => 'Verificación de destino de aliado modificada',
        'ally_financial.adjustment_created' => 'Ajuste financiero de aliado',
        'ally_settlement.requested' => 'Liquidación de aliado solicitada',
        'ally_settlement.paid' => 'Liquidación de aliado pagada',
        'ally_settlement.cancelled' => 'Liquidación de aliado cancelada',
        'ally_settlement.reversed' => 'Liquidación de aliado revertida',
        'bcv_rate.created' => 'Tasa BCV creada',
        'bcv_rate.updated' => 'Tasa BCV actualizada',
        'bcv_rate.deleted' => 'Tasa BCV eliminada',
        'bcv_rate.synced' => 'Tasa BCV sincronizada',
        'driver.approved' => 'Repartidor aprobado',
        'driver.rejected' => 'Repartidor rechazado',
        'driver.suspended' => 'Repartidor suspendido',
        'driver.activated' => 'Repartidor reactivado',
        'driver.payment.created' => 'Remuneración de repartidor generada',
        'driver.payment.paid' => 'Remuneración de repartidor pagada',
        'driver.payment.cancelled' => 'Remuneración de repartidor cancelada',
        'driver_remuneration_rate.updated' => 'Tarifa de remuneración actualizada',
        'incident.reported_by_driver' => 'Incidencia reportada por repartidor',
        'incident.status_updated' => 'Estatus de incidencia actualizado',
        'package.cod_liquidated' => 'Cobro contra entrega liquidado',
        'package.delivery_assigned' => 'Paquete asignado a reparto',
        'package.delivery_unassigned' => 'Asignación de reparto retirada',
        'rate_matrix.updated' => 'Tarifas actualizadas',
        'route.created' => 'Ruta creada',
        'route.updated' => 'Ruta actualizada',
        'route.stops_updated' => 'Paradas de ruta actualizadas',
        'route.claimed' => 'Ruta tomada por repartidor',
        'route.released' => 'Ruta liberada',
        'route.started' => 'Ruta iniciada',
        'route.stop_visited' => 'Parada de ruta visitada',
        'route.completed' => 'Ruta completada',
        'route.cancelled' => 'Ruta cancelada',
        'route.duplicated' => 'Ruta duplicada',
        'user.created' => 'Usuario creado',
        'user.updated' => 'Usuario actualizado',
        'user.deleted' => 'Usuario eliminado',
        'user.status_changed' => 'Estatus de usuario modificado',
    ];

    protected $fillable = [
        'actor_user_id',
        'action',
        'target_type',
        'target_id',
        'description',
        'metadata',
        'ip_address',
    ];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    /**
     * Etiqueta legible para mostrar en pantalla en vez del código
     * interno crudo (ej. "client.delivery_accepted"). Si la acción no
     * está mapeada, la humaniza en vez de mostrar el código tal cual.
     */
    public function actionLabel(): string
    {
        return self::labelFor($this->action);
    }

    /**
     * Misma traducción que actionLabel(), pero sin necesitar una
     * instancia — para poblar, por ejemplo, el <select> de filtro de
     * acción con las etiquetas en vez de los códigos crudos.
     */
    public static function labelFor(string $action): string
    {
        return self::ACTION_LABELS[$action] ?? self::humanizeAction($action);
    }

    private static function humanizeAction(string $action): string
    {
        $words = str_replace(['.', '_'], ' ', $action);

        return ucfirst($words);
    }
}
