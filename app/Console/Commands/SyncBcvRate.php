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
     * Este comando corre cada 15 minutos (routes/console.php). Sin
     * este límite, una caída sostenida de la API del BCV mandaría un
     * correo de alerta cada 15 minutos hasta que alguien la resuelva.
     * Una vez cada 4 horas es suficiente para que un admin se entere
     * el mismo día sin saturarle la bandeja de entrada.
     */
    private const NOTIFICATION_THROTTLE_KEY = 'bcv_sync_failure_notified_at';

    private const NOTIFICATION_THROTTLE_HOURS = 4;

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
