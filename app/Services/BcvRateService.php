<?php

namespace App\Services;

use App\Models\BcvRate;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class BcvRateService
{
    /**
     * Antigüedad de una tasa contando solo horas de días hábiles
     * (lunes a viernes) — el BCV no publica nada nuevo sábados,
     * domingos ni feriados bancarios, así que un fin de semana entero
     * NO debe contar como "atraso": la tasa del viernes en la tarde
     * sigue siendo, correctamente, la vigente durante todo el fin de
     * semana. Contar horas de reloj crudas bloquearía cotizaciones
     * todos los lunes por la mañana sin que nada estuviera realmente
     * roto.
     */
    public function businessHoursAge(BcvRate $rate): int
    {
        return $rate->effective_at->diffInHoursFiltered(
            fn (Carbon $date) => ! $date->isWeekend(),
            now()
        );
    }

    /**
     * Obtiene la tasa BCV vigente más reciente, para usarla en
     * cotizaciones/cobros reales. Bloquea con una excepción si esa
     * tasa ya es demasiado vieja (bcv_api.max_age_hours, contado en
     * horas hábiles) — señal de que bcv:sync lleva tiempo fallando en
     * silencio — en vez de seguir cotizando indefinidamente con un
     * valor desactualizado.
     *
     * Los usos puramente informativos (mostrar la tasa actual en el
     * dashboard de Admin o en BcvRateManager) NO pasan por aquí: usan
     * BcvRate::current() directamente, porque el admin necesita poder
     * ver y corregir una tasa vieja aunque esté vieja.
     */
    public function getCurrentRate(): BcvRate
    {
        $rate = BcvRate::current();

        if (! $rate) {
            throw new RuntimeException('No hay ninguna tasa BCV registrada todavía.');
        }

        $maxAgeHours = (int) config('services.bcv_api.max_age_hours', 72);
        $ageInHours = $this->businessHoursAge($rate);

        if ($ageInHours > $maxAgeHours) {
            throw new RuntimeException(
                "La tasa BCV vigente tiene {$ageInHours} horas hábiles de antigüedad (máximo "
                ."permitido: {$maxAgeHours}h). No se pueden generar cotizaciones ni registrar "
                .'paquetes hasta que un administrador actualice la tasa (sincronización automática '
                .'o manual en el panel de Tasa BCV).'
            );
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

        // La tasa BCV tiene 6 decimales y cambia a diario; usamos
        // aritmética decimal exacta (ver App\Support\Money) para que
        // esta conversión no acumule error de coma flotante frente al
        // resto de la cadena tarifa -> comisión -> liquidación.
        return Money::round(Money::mul($usdAmount, $rate->rate), 2);
    }
}
