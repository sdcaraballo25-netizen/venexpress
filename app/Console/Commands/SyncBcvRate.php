<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\BcvRateSyncFailed;
use App\Services\BcvRateService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SyncBcvRate extends Command
{
    protected $signature = 'bcv:sync';

    protected $description = 'Consulta la tasa oficial del BCV y guarda un nuevo valor si cambió';

    /**
     * Este comando corre cada 15 minutos, pero solo dentro de la
     * ventana diaria donde el BCV suele publicar (1:30pm-6:30pm VET,
     * routes/console.php) — hasta 20 intentos por tarde. Sin este
     * límite, una falla sostenida de la API durante toda esa ventana
     * mandaría hasta 20 correos el mismo día. Con 6 horas de
     * throttle (más que la ventana completa) queda como mucho un
     * aviso por día hábil, y se resetea solo apenas un intento tenga
     * éxito.
     */
    private const NOTIFICATION_THROTTLE_KEY = 'bcv_sync_failure_notified_at';

    private const NOTIFICATION_THROTTLE_HOURS = 6;

    public function handle(BcvRateService $service): int
    {
        try {
            $rate = $service->syncFromApi();

            Cache::forget(self::NOTIFICATION_THROTTLE_KEY);

            if (! $rate) {
                $this->info('La tasa BCV no ha cambiado.');
                return self::SUCCESS;
            }

            $this->info(
                'Nueva tasa BCV registrada: ' .
                number_format((float) $rate->rate, 6, '.', ',') .
                ' VES/USD'
            );

            return self::SUCCESS;
        } catch (\Throwable $e) {
            report($e);
            $this->error('No se pudo actualizar la tasa BCV: ' . $e->getMessage());

            $this->notifyAdminsUnlessThrottled($e);

            return self::FAILURE;
        }
    }

    protected function notifyAdminsUnlessThrottled(\Throwable $e): void
    {
        if (Cache::has(self::NOTIFICATION_THROTTLE_KEY)) {
            return;
        }

        Cache::put(
            self::NOTIFICATION_THROTTLE_KEY,
            true,
            now()->addHours(self::NOTIFICATION_THROTTLE_HOURS)
        );

        try {
            $admins = User::query()
                ->whereIn('role', [
                    User::ROLE_ADMIN_PRINCIPAL,
                    User::ROLE_ADMIN_OPERATIVO,
                ])
                ->get();

            foreach ($admins as $admin) {
                $admin->notify(new BcvRateSyncFailed($e->getMessage()));
            }
        } catch (\Throwable $notifyError) {
            // Un fallo al avisar del fallo no debe tumbar el comando
            // ni ocultar el error original ya reportado arriba.
            report($notifyError);
        }
    }
}
