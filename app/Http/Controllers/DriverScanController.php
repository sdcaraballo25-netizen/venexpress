<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriverScanController extends Controller
{
    /**
     * Verifica la integridad de una guía escaneada, comparando su
     * código de seguridad guardado contra los datos que tiene
     * actualmente en base de datos.
     *
     * Esto detecta si el registro del paquete fue alterado en el
     * sistema después de haberse impreso la etiqueta (ej. cambio de
     * peso, de agencia, o una guía duplicada). No detecta
     * alteraciones hechas directamente sobre el papel físico.
     */
    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tracking_number' => ['required', 'string'],
        ]);

        $package = Package::where('tracking_number', $validated['tracking_number'])->first();

        if (! $package) {
            return response()->json([
                'valid' => false,
                'message' => 'No existe ninguna guía con ese número de tracking.',
            ], 404);
        }

        $isValid = $package->verifySecurityHash();

        return response()->json([
            'valid' => $isValid,
            'tracking_number' => $package->tracking_number,
            'current_status' => $package->current_status,
            'message' => $isValid
                ? 'La guía es íntegra: sus datos no han sido alterados desde su creación.'
                : 'ALERTA: los datos de esta guía no coinciden con el código de seguridad original. Verifica manualmente antes de continuar.',
        ]);
    }
}
