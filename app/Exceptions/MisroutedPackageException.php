<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Un paquete se intentó recibir en un punto que no es su destino
 * (HubReceptionService::receiveTransferAtWarehouse()). Quien lo atrapa
 * avisa con MisroutedPackageAlertService.
 */
class MisroutedPackageException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?int $expectedWarehouseId = null,
    ) {
        parent::__construct($message);
    }
}
