<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RouteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $stops = $this->stops;

        $totalStops = $stops->count();
        $visitedStops = $stops->where('status', \App\Models\RouteStop::STATUS_VISITED)->count();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'city' => $this->city,
            'state' => $this->state,
            'route_type' => $this->route_type,
            'status' => $this->status,
            'started_at' => $this->started_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'progress' => [
                'total_stops' => $totalStops,
                'visited_stops' => $visitedStops,
                'pending_stops' => max(0, $totalStops - $visitedStops),
                'percentage' => $totalStops > 0 ? (int) round(($visitedStops / $totalStops) * 100) : 0,
            ],
            'stops' => RouteStopResource::collection($stops),
        ];
    }
}
