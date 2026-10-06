<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageHistory extends Model
{
    use HasFactory;

    public const EVENT_MOVIMIENTO = 'MOVIMIENTO';

    public const EVENT_RECEPCION = 'RECEPCION';

    public const EVENT_SALIDA = 'SALIDA';

    public const EVENT_TRANSFERENCIA = 'TRANSFERENCIA';

    public const EVENT_REPARTO = 'SALIDA_REPARTO';

    public const EVENT_ENTREGA = 'ENTREGA';

    public const EVENT_INCIDENCIA = 'INCIDENCIA';

    public const EVENT_CORRECCION = 'CORRECCION';

    /**
     * Devolución al remitente (inicio y entrega al remitente). Evento
     * propio, distinto de EVENT_ENTREGA, para que una devolución nunca
     * se cuente como una entrega al destinatario.
     */
    public const EVENT_DEVOLUCION = 'DEVOLUCION';

    /** Un repartidor no pudo entregar (EN_RUTA -> ENTREGA_FALLIDA). */
    public const EVENT_ENTREGA_FALLIDA = 'ENTREGA_FALLIDA';

    public const EVENTOS = [
        self::EVENT_MOVIMIENTO,
        self::EVENT_RECEPCION,
        self::EVENT_SALIDA,
        self::EVENT_TRANSFERENCIA,
        self::EVENT_REPARTO,
        self::EVENT_ENTREGA,
        self::EVENT_INCIDENCIA,
        self::EVENT_CORRECCION,
        self::EVENT_DEVOLUCION,
        self::EVENT_ENTREGA_FALLIDA,
    ];

    protected $fillable = [
        'package_id',
        'route_stop_id',
        'status',
        'event_type',
        'origin_location',
        'destination_location',
        'location_description',
        'scanned_by_user_id',
    ];

    protected static function booted(): void
    {
        /*
         * Los movimientos logísticos son históricos.
         * No permitimos modificar ni eliminar un registro existente.
         *
         * Las correcciones deberán crear un nuevo evento
         * de tipo CORRECCION.
         */
        static::updating(function () {
            throw new \RuntimeException(
                'Los movimientos logísticos no pueden modificarse.'
            );
        });

        static::deleting(function () {
            throw new \RuntimeException(
                'Los movimientos logísticos no pueden eliminarse.'
            );
        });
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function routeStop(): BelongsTo
    {
        return $this->belongsTo(RouteStop::class);
    }

    public function scannedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'scanned_by_user_id'
        );
    }

    public function eventTypeLabel(): string
    {
        return match ($this->event_type) {

            self::EVENT_RECEPCION =>
                'Recepción',

            self::EVENT_SALIDA =>
                'Salida',

            self::EVENT_TRANSFERENCIA =>
                'Transferencia',

            self::EVENT_REPARTO =>
                'Salida a reparto',

            self::EVENT_ENTREGA =>
                'Entrega',

            self::EVENT_INCIDENCIA =>
                'Incidencia',

            self::EVENT_CORRECCION =>
                'Corrección',

            self::EVENT_DEVOLUCION =>
                'Devolución',

            self::EVENT_ENTREGA_FALLIDA =>
                'Entrega fallida',

            default =>
                'Movimiento',
        };
    }
}
