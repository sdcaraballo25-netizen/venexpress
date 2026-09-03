<?php

namespace App\Services;

use App\Models\BcvRate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class BcvRateService
{
    /**
     * Obtiene la tasa BCV vigente más reciente.
     */
    public function getCurrentRate(): BcvRate
    {
        $rate = BcvRate::current();

        if (! $rate) {
            throw new RuntimeException('No hay ninguna tasa BCV registrada todavía.');
        }

        return $rate;
    }

    /**
     * Consulta la cotización oficial del BCV a través de DolarAPI.
     *
     * DolarAPI indica que su fuente para el Dólar Oficial en Venezuela
     * es el BCV:
     * https://ve.dolarapi.com/v1/dolares/oficial
     */
    public function fetchFromApi(): array
    {
        $url = config(
            'services.bcv_api.url',
            'https://ve.dolarapi.com/v1/dolares/oficial'
        );

        $response = Http::acceptJson()
            ->timeout(10)
            ->retry(2, 500)
            ->get($url);

        $response->throw();

        $data = $response->json();

        $rate = (float) ($data['promedio'] ?? $data['venta'] ?? 0);

        if ($rate <= 0) {
            throw new RuntimeException('La API BCV no devolvió una tasa válida.');
        }

        $effectiveAt = now();

        if (! empty($data['fechaActualizacion'])) {
            try {
                $effectiveAt = Carbon::parse($data['fechaActualizacion'])
                    ->timezone(config('app.timezone', 'America/Caracas'));
            } catch (\Throwable) {
                // Si el formato de fecha de la API cambia, usamos la hora local.
            }
        }

        return [
            'rate' => $rate,
            'effective_at' => $effectiveAt,
            'source' => $data['fuente'] ?? 'BCV',
            'api_updated_at' => $data['fechaActualizacion'] ?? null,
        ];
    }

    /**
     * Consulta la API y guarda una nueva tasa solamente si cambió.
     *
     * Esto permite detectar las dos actualizaciones diarias del BCV sin
     * sobrescribir la primera tasa del día.
     */
    public function syncFromApi(): ?BcvRate
    {
        $data = $this->fetchFromApi();

        $current = BcvRate::current();

        if ($current) {
            // Comparación de floats con tolerancia: nunca comparar
            // decimales con === porque la representación en coma
            // flotante puede diferir en el último dígito aunque el
            // valor "real" sea el mismo, generando registros
            // duplicados en cada sincronización.
            $diff = abs((float) $current->rate - (float) $data['rate']);

            if ($diff < 0.005) {
                return null;
            }
        }

        return BcvRate::create([
            'rate' => $data['rate'],
            'effective_date' => $data['effective_at']->toDateString(),
            'effective_at' => $data['effective_at'],
            'source' => $data['source'],
            'api_updated_at' => $data['api_updated_at'],
        ]);
    }

    /**
     * Mantiene compatibilidad con el registro manual existente.
     */
    public function setRate(float $rate, ?Carbon $effectiveDate = null): BcvRate
    {
        $effectiveDate ??= now();

        return BcvRate::create([
            'rate' => $rate,
            'effective_date' => $effectiveDate->toDateString(),
            'effective_at' => $effectiveDate,
            'source' => 'manual',
        ]);
    }

    /**
     * Convierte un monto en USD a VES usando la tasa indicada,
     * o la tasa vigente si no se pasa ninguna.
     */
    public function convertUsdToVes(float $usdAmount, ?BcvRate $rate = null): float
    {
        $rate ??= $this->getCurrentRate();

        return round($usdAmount * (float) $rate->rate, 2);
    }
}
