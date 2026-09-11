<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverPackageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tracking_number' => $this->tracking_number,

            'current_status' => $this->current_status,
            'status_label' => $this->statusLabel(),
            'delivery_status' => $this->delivery_status,

            // Datos del remitente (necesarios para que el
            // repartidor pueda contactarlo si hay un problema).
            'sender' => [
                'name' => $this->sender_name,
                'id_doc' => $this->sender_id_doc,
                'phone' => $this->sender_phone,
            ],

            // Datos del destinatario, necesarios para entregar.
            'recipient' => [
                'name' => $this->recipient_name,
                'id_doc' => $this->recipient_id_doc,
                'phone' => $this->recipient_phone,
            ],

            'destination_city' => $this->destination_city,
            'requires_delivery' => (bool) $this->requires_delivery,
            'delivery_address' => $this->delivery_address,
            'delivery_sector' => $this->delivery_sector,
            'delivery_reference' => $this->delivery_reference,

            'package_type' => $this->package_type,
            'is_fragile' => (bool) $this->is_fragile,

            'is_cod' => (bool) $this->is_cod,
            'cod_amount_usd' => $this->when(
                $this->is_cod,
                fn () => (float) $this->cod_amount_usd
            ),
            'cod_status' => $this->when(
                $this->is_cod,
                fn () => $this->cod_status
            ),
            'cod_collected_at' => $this->when(
                $this->is_cod,
                fn () => $this->cod_collected_at?->toIso8601String()
            ),

            'driver_remuneration_usd' => $this->driver_remuneration_usd !== null
                ? (float) $this->driver_remuneration_usd
                : null,
            'driver_remuneration_status' => $this->driver_remuneration_status,

            // Alerta de seguridad: si el hash HMAC no coincide con
            // los datos actuales de la guía, la app debe mostrar una
            // advertencia visible antes de dejar continuar.
            'security_warning' => $this->security_hash
                ? ! $this->verifySecurityHash()
                : false,

            'incidents_count' => $this->whenCounted('incidents'),

            'updated_at' => $this->updated_at?->toIso8601String(),
            'delivery_completed_at' => $this->delivery_completed_at?->toIso8601String(),
        ];
    }
}
