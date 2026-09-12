<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RouteStopResource extends JsonResource
{
    /**
     * Campos verificados contra app/Models/Ally.php: el nombre del
     * negocio es 'business_name' (no 'name'), y el teléfono vive en
     * el User dueño de la agencia, no en la tabla allies. Por eso el
     * controller debe cargar la relación 'ally.user'.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sequence' => $this->sequence,
            'status' => $this->status,
            'map_color' => $this->mapColor(),
            'visited_at' => $this->visited_at?->toIso8601String(),
            'packages_collected_count' => $this->packages_collected_count,
            'ally' => $this->ally ? [
                'id' => $this->ally->id,
                'name' => $this->ally->business_name,
                'address' => $this->ally->address,
                'phone' => $this->ally->user?->phone,
                'city' => $this->ally->city,
                'state' => $this->ally->state,
                'latitude' => $this->ally->latitude !== null ? (float) $this->ally->latitude : null,
                'longitude' => $this->ally->longitude !== null ? (float) $this->ally->longitude : null,
            ] : null,
        ];
    }
}
