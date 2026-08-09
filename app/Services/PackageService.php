<?php

namespace App\Services;

use App\Models\Package;
use App\Models\PackageHistory;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PackageService
{
    /**
     * Estados válidos y orden de la máquina de estados.
     */
    public const STATUSES = [
        'RECIBIDO_AGENCIA',
        'RECOLECTADO_VENEXPRESS',
        'EN_HUB',
        'EN_TRANSITO_NACIONAL',
        'LISTO_RETIRO',
        'ENTREGADO',
    ];

    /**
     * Crea una nueva guía y registra automáticamente
     * el primer evento de tracking.
     */
    public function create(
        array $data,
        int $allyId,
        int $userId
    ): Package {
        return DB::transaction(function () use (
            $data,
            $allyId,
            $userId
        ) {
            $package = Package::create([
                ...$data,

                'ally_id' => $allyId,

                'current_status' =>
                    'RECIBIDO_AGENCIA',
            ]);

            PackageHistory::create([
                'package_id' => $package->id,

                'status' =>
                    'RECIBIDO_AGENCIA',

                'location_description' =>
                    $package->origin_city,

                'scanned_by_user_id' =>
                    $userId,
            ]);

            return $package->fresh([
                'ally',
                'histories',
            ]);
        });
    }

    /**
     * Cambia el estado de una guía.
     *
     * Solo permite avanzar al siguiente estado.
     */
    public function transition(
        Package $package,
        string $newStatus,
        int $userId,
        ?string $location = null
    ): Package {
        if (!in_array(
            $newStatus,
            self::STATUSES,
            true
        )) {
            throw new InvalidArgumentException(
                'El estado indicado no es válido.'
            );
        }

        $currentIndex = array_search(
            $package->current_status,
            self::STATUSES,
            true
        );

        $newIndex = array_search(
            $newStatus,
            self::STATUSES,
            true
        );

        if ($currentIndex === false) {
            throw new InvalidArgumentException(
                'El estado actual de la guía no es válido.'
            );
        }

        /**
         * Evitamos saltos de estados.
         */
        if ($newIndex !== $currentIndex + 1) {
            throw new InvalidArgumentException(
                "No se puede cambiar de "
                . "{$package->current_status} a "
                . "{$newStatus}."
            );
        }

        return DB::transaction(
            function () use (
                $package,
                $newStatus,
                $userId,
                $location
            ) {
                $package->update([
                    'current_status' =>
                        $newStatus,
                ]);

                PackageHistory::create([
                    'package_id' =>
                        $package->id,

                    'status' =>
                        $newStatus,

                    'location_description' =>
                        $location,

                    'scanned_by_user_id' =>
                        $userId,
                ]);

                return $package->fresh([
                    'ally',
                    'histories',
                ]);
            }
        );
    }

    /**
     * Comprueba si una transición es válida.
     */
    public function canTransition(
        Package $package,
        string $newStatus
    ): bool {
        $currentIndex = array_search(
            $package->current_status,
            self::STATUSES,
            true
        );

        $newIndex = array_search(
            $newStatus,
            self::STATUSES,
            true
        );

        if (
            $currentIndex === false ||
            $newIndex === false
        ) {
            return false;
        }

        return $newIndex === $currentIndex + 1;
    }

    /**
     * Devuelve el siguiente estado disponible.
     */
    public function nextStatus(
        Package $package
    ): ?string {
        $currentIndex = array_search(
            $package->current_status,
            self::STATUSES,
            true
        );

        if ($currentIndex === false) {
            return null;
        }

        return self::STATUSES[$currentIndex + 1]
            ?? null;
    }

    /**
     * Indica si la guía ya fue entregada.
     */
    public function isDelivered(
        Package $package
    ): bool {
        return $package->current_status === 'ENTREGADO';
    }
}