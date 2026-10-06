<?php

namespace App\Services;

use App\Jobs\GeocodePackageDeliveryAddress;
use App\Models\Ally;
use App\Models\AuditLog;
use App\Models\Package;
use App\Models\PackageHistory;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Cambio de modalidad de destino final de una guía: entrega a domicilio
 * <-> retiro en persona (en el almacén destino o en una agencia aliada).
 *
 * Solo antes de asignarla a reparto: mientras no haya salido con un
 * repartidor (EN_RUTA en adelante) ni se haya despachado a la agencia
 * de retiro. Lo hace Admin. El total cobrado no cambia (sin reembolso
 * ni cargo extra, igual que en las devoluciones).
 *
 * Si el paquete ya espera en su almacén destino (LISTO_RETIRO o
 * PENDIENTE_ENTREGA), vuelve a EN_HUB y se libera de nuevo con
 * HubReleaseService::release(), que decide según la modalidad nueva
 * (listo para retiro, pendiente de entrega o despacho a la agencia) y
 * valida la coherencia igual que siempre.
 */
class PackageModalityService
{
    /**
     * Estados en los que todavía se puede cambiar la modalidad: no ha
     * salido a reparto ni se entregó. Ajustar aquí si cambia el límite.
     */
    public const CHANGEABLE_STATUSES = [
        Package::STATUS_RECIBIDO_AGENCIA,
        Package::STATUS_RECOLECTADO_VENEXPRESS,
        Package::STATUS_EN_HUB,
        Package::STATUS_EN_TRANSITO_NACIONAL,
        Package::STATUS_LISTO_RETIRO,
        Package::STATUS_PENDIENTE_ENTREGA,
    ];

    public const MODALITY_DELIVERY = 'domicilio';

    public const MODALITY_HUB = Package::PICKUP_MODE_HUB;

    public const MODALITY_ALLY = Package::PICKUP_MODE_ALLY;

    public function __construct(
        protected HubReleaseService $hubReleaseService,
    ) {}

    public static function modalityOf(Package $package): string
    {
        if ($package->requires_delivery) {
            return self::MODALITY_DELIVERY;
        }

        return $package->pickup_mode === Package::PICKUP_MODE_ALLY ? self::MODALITY_ALLY : self::MODALITY_HUB;
    }

    /**
     * Por qué no se puede cambiar la modalidad, o null si se puede.
     */
    public function blockedReason(Package $package): ?string
    {
        if (! in_array($package->current_status, self::CHANGEABLE_STATUSES, true)) {
            return 'La modalidad solo se puede cambiar antes de que la guía salga a reparto. Estado actual: '
                .$package->statusLabel().'.';
        }

        if ($this->alreadyDispatchedToAlly($package)) {
            return 'Esta guía ya va o está en su agencia de retiro: ya no se puede cambiar la modalidad.';
        }

        return null;
    }

    /**
     * Retiro en agencia ya despachado desde el almacén destino (en camino
     * a la agencia) o ya recibido por ella.
     */
    protected function alreadyDispatchedToAlly(Package $package): bool
    {
        if ($package->pickup_mode !== Package::PICKUP_MODE_ALLY) {
            return false;
        }

        if ($package->current_status === Package::STATUS_LISTO_RETIRO) {
            return true;
        }

        return $package->current_status === Package::STATUS_EN_TRANSITO_NACIONAL
            && $package->destination_warehouse_id !== null
            && (int) $package->current_warehouse_id === (int) $package->destination_warehouse_id;
    }

    /**
     * @param  array{modality: string, delivery_address?: ?string, delivery_sector?: ?string, delivery_reference?: ?string, pickup_ally_id?: ?int}  $data
     */
    public function change(Package $package, array $data, int $userId): Package
    {
        $modality = $data['modality'] ?? null;

        if (! in_array($modality, [self::MODALITY_DELIVERY, self::MODALITY_HUB, self::MODALITY_ALLY], true)) {
            throw new RuntimeException('Elige la nueva modalidad.');
        }

        $changed = DB::transaction(function () use ($package, $data, $modality, $userId) {
            $locked = Package::query()
                ->whereKey($package->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($reason = $this->blockedReason($locked)) {
                throw new RuntimeException($reason);
            }

            $previous = self::modalityOf($locked);
            $previousLabel = $this->label($locked);

            if ($modality === self::MODALITY_DELIVERY) {
                $address = trim((string) ($data['delivery_address'] ?? ''));

                if ($address === '') {
                    throw new RuntimeException('Indica la dirección de entrega.');
                }

                $needsGeocoding = ! $locked->requires_delivery
                    || $address !== (string) $locked->delivery_address;

                $locked->fill([
                    'requires_delivery' => true,
                    'pickup_mode' => null,
                    'pickup_ally_id' => null,
                    'delivery_address' => $address,
                    'delivery_sector' => trim((string) ($data['delivery_sector'] ?? '')) ?: null,
                    'delivery_reference' => trim((string) ($data['delivery_reference'] ?? '')) ?: null,
                    'delivery_status' => Package::DELIVERY_PENDING,
                ]);

                if ($needsGeocoding) {
                    $locked->delivery_latitude = null;
                    $locked->delivery_longitude = null;
                    $locked->delivery_geocoded_at = null;
                }
            } else {
                $pickupAllyId = null;

                if ($modality === self::MODALITY_ALLY) {
                    $pickupAlly = Ally::query()
                        ->verifiedDestinations()
                        ->where('state', $locked->destination_state)
                        ->find($data['pickup_ally_id'] ?? null);

                    if (! $pickupAlly) {
                        throw new RuntimeException(
                            'Elige una agencia de retiro activa y verificada del estado destino.'
                        );
                    }

                    $pickupAllyId = $pickupAlly->id;
                }

                $locked->fill([
                    'requires_delivery' => false,
                    'pickup_mode' => $modality,
                    'pickup_ally_id' => $pickupAllyId,
                ]);
            }

            if ($previous === $modality && ! $locked->isDirty()) {
                throw new RuntimeException('La guía ya tiene esa modalidad.');
            }

            $locked->save();

            $newLabel = $this->label($locked);

            AuditLog::create([
                'actor_user_id' => $userId,
                'action' => 'package.modality_changed',
                'target_type' => Package::class,
                'target_id' => $locked->id,
                'description' => "Cambió la modalidad de la guía {$locked->tracking_number}: {$previousLabel} → {$newLabel}.",
                'metadata' => [
                    'from' => $previous,
                    'to' => $modality,
                    'pickup_ally_id' => $locked->pickup_ally_id,
                    'status' => $locked->current_status,
                ],
                'ip_address' => request()?->ip(),
            ]);

            // Ya esperaba en su almacén destino: se vuelve a liberar con
            // la modalidad nueva (solo si cambió; corregir la dirección
            // de un envío que sigue siendo a domicilio no lo mueve).
            if (
                $previous !== $modality
                && in_array($locked->current_status, [Package::STATUS_LISTO_RETIRO, Package::STATUS_PENDIENTE_ENTREGA], true)
            ) {
                $locked->update(['current_status' => Package::STATUS_EN_HUB, 'driver_id' => null]);

                PackageHistory::create([
                    'package_id' => $locked->id,
                    'status' => Package::STATUS_EN_HUB,
                    'event_type' => PackageHistory::EVENT_CORRECCION,
                    'origin_location' => $previousLabel,
                    'destination_location' => $newLabel,
                    'location_description' => "Modalidad cambiada: {$previousLabel} → {$newLabel}.",
                    'scanned_by_user_id' => $userId,
                ]);

                return $this->hubReleaseService->release($locked, $userId);
            }

            PackageHistory::create([
                'package_id' => $locked->id,
                'status' => $locked->current_status,
                'event_type' => PackageHistory::EVENT_CORRECCION,
                'origin_location' => $previousLabel,
                'destination_location' => $newLabel,
                'location_description' => "Modalidad cambiada: {$previousLabel} → {$newLabel}.",
                'scanned_by_user_id' => $userId,
            ]);

            return $locked->fresh();
        });

        if ($changed->requires_delivery && $changed->delivery_latitude === null) {
            GeocodePackageDeliveryAddress::dispatch($changed->id);
        }

        return $changed;
    }

    public function label(Package $package): string
    {
        return match (self::modalityOf($package)) {
            self::MODALITY_DELIVERY => 'Entrega a domicilio',
            self::MODALITY_ALLY => 'Retiro en agencia'.($package->pickupAlly ? " ({$package->pickupAlly->business_name})" : ''),
            default => 'Retiro en almacén',
        };
    }
}
