<?php

namespace App\Console\Commands;

use App\Services\BcvRateService;
use Illuminate\Console\Command;

class SyncBcvRate extends Command
{
    protected $signature = 'bcv:sync';

    protected $description = 'Consulta la tasa oficial del BCV y guarda un nuevo valor si cambió';

    public function handle(BcvRateService $service): int
    {
        try {
            $rate = $service->syncFromApi();

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

            return self::FAILURE;
        }
    }
}
