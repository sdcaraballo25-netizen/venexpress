<?php

namespace App\Services;

use App\Models\Package;
use App\Models\WarehouseCoverage;

/**
 * Fase 4 — Resolución logística.
 *
 * Capa de LECTURA que determina a qué HUB propio de Venexpress
 * pertenece un destino (estado + ciudad), y si un paquete ya se
 * encuentra físicamente en su HUB de destino.
 *
 * Deliberadamente no ejecuta ninguna acción logística: no cambia
 * current_status, no cambia current_warehouse_id, no crea rutas, no
 * despacha, no escanea, no toca PackageHistory. Solo resuelve y
 * devuelve un resultado (LogisticsResolutionResult) para que fases
 * posteriores decidan qué hacer con él.
 */
class LogisticsResolutionService
{
    public function __construct(
        protected VenezuelaLocationService $locationService,
    ) {
    }

    /**
     * Resuelve el HUB que atiende un destino (estado + ciudad).
     *
     * Reglas, en orden:
     * 1. Si hay una cobertura específica de ciudad activa, gana sobre
     *    cualquier cobertura de estado (fallback).
     * 2. Si no hay cobertura de ciudad, se usa la cobertura de todo
     *    el estado (city = null), si existe.
     * 3. Si ninguna de las dos existe, se devuelve "sin cobertura" —
     *    nunca se inventa un HUB.
     * 4. Si más de un HUB activo cubre exactamente la misma zona
     *    (mismo estado + misma ciudad, o mismo estado con ambos como
     *    todo-el-estado), se devuelve "ambiguo" en vez de elegir uno
     *    al azar.
     */
    public function resolveDestinationWarehouse(?string $state, ?string $city = null): LogisticsResolutionResult
    {
        $state = $state !== null ? trim($state) : '';
        $city = $city !== null ? trim($city) : '';
        $city = $city === '' ? null : $city;

        if ($state === '') {
            return LogisticsResolutionResult::invalid(
                'El estado destino es obligatorio para resolver el HUB logístico.'
            );
        }

        if (! $this->isKnownState($state)) {
            return LogisticsResolutionResult::invalid(
                "\"{$state}\" no pertenece al catálogo geográfico unificado."
            );
        }

        if ($city !== null && ! $this->isKnownCityForState($state, $city)) {
            return LogisticsResolutionResult::invalid(
                "\"{$city}\" no pertenece al estado \"{$state}\" en el catálogo geográfico unificado."
            );
        }

        if ($city !== null) {
            $cityCoverage = $this->activeCoverageFor($state, $city);

            if ($cityCoverage->count() > 1) {
                return LogisticsResolutionResult::ambiguous(
                    "Existen {$cityCoverage->count()} almacenes con cobertura activa para \"{$city}, {$state}\". "
                    .'Corrige la configuración de cobertura antes de resolver este destino.'
                );
            }

            if ($cityCoverage->count() === 1) {
                return LogisticsResolutionResult::resolved(
                    $cityCoverage->first()->warehouse_id,
                    LogisticsResolutionResult::RULE_CITY,
                    "Cobertura específica de ciudad: \"{$city}, {$state}\"."
                );
            }
        }

        $stateCoverage = $this->activeCoverageFor($state, null);

        if ($stateCoverage->count() > 1) {
            return LogisticsResolutionResult::ambiguous(
                "Existen {$stateCoverage->count()} almacenes con cobertura activa de todo el estado \"{$state}\". "
                .'Corrige la configuración de cobertura antes de resolver este destino.'
            );
        }

        if ($stateCoverage->count() === 1) {
            return LogisticsResolutionResult::resolved(
                $stateCoverage->first()->warehouse_id,
                LogisticsResolutionResult::RULE_STATE,
                "Cobertura de todo el estado \"{$state}\" (sin cobertura específica para la ciudad)."
            );
        }

        $target = $city !== null ? "\"{$city}, {$state}\"" : "el estado \"{$state}\"";

        return LogisticsResolutionResult::noCoverage(
            "No existe ningún almacén con cobertura activa para {$target}."
        );
    }

    /**
     * Resuelve el HUB de destino de un Package a partir de su
     * destination_state/destination_city. No lee ni escribe
     * destination_warehouse_id: siempre resuelve en vivo contra la
     * cobertura configurada, para no depender de un valor persistido
     * que pueda haber quedado desactualizado.
     */
    public function resolveForPackage(Package $package): LogisticsResolutionResult
    {
        return $this->resolveDestinationWarehouse(
            $package->destination_state,
            $package->destination_city,
        );
    }

    /**
     * ¿El paquete ya se encuentra físicamente (current_warehouse_id)
     * en el HUB que le corresponde como destino logístico?
     *
     * Lectura pura: no cambia current_status ni current_warehouse_id.
     * Devuelve false tanto si el paquete no está en ningún HUB como
     * si el destino no se puede resolver con certeza (sin cobertura,
     * ambiguo o inválido) — nunca asume una coincidencia que no pueda
     * confirmar.
     */
    public function isAtDestinationWarehouse(Package $package): bool
    {
        if ($package->current_warehouse_id === null) {
            return false;
        }

        $resolution = $this->resolveForPackage($package);

        if (! $resolution->isResolved()) {
            return false;
        }

        return $resolution->warehouseId === $package->current_warehouse_id;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, WarehouseCoverage>
     */
    protected function activeCoverageFor(string $state, ?string $city): \Illuminate\Database\Eloquent\Collection
    {
        return WarehouseCoverage::query()
            ->active()
            ->where('state', $state)
            ->when(
                $city === null,
                fn ($query) => $query->whereNull('city'),
                fn ($query) => $query->where('city', $city),
            )
            ->get();
    }

    protected function isKnownState(string $state): bool
    {
        return in_array($state, $this->locationService->states(), true);
    }

    protected function isKnownCityForState(string $state, string $city): bool
    {
        return in_array($city, $this->locationService->citiesByState($state), true);
    }
}
