<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class DriverAuthController extends Controller
{
    /**
     * Login exclusivo para usuarios con rol 'repartidor'.
     *
     * Devuelve un token de Sanctum con la habilidad 'driver', para
     * que el resto de los endpoints puedan exigir explícitamente que
     * el token fue emitido para la app del repartidor y no
     * reutilizado de otro contexto.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['required', 'string', 'max:100'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Auth::validate([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ])) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales no son correctas.'],
            ]);
        }

        if (! $user->isRepartidor()) {
            throw ValidationException::withMessages([
                'email' => ['Esta cuenta no tiene un rol de repartidor.'],
            ]);
        }

        if (! $user->isActive()) {
            throw ValidationException::withMessages([
                'email' => ['Esta cuenta está inactiva. Contacta al administrador.'],
            ]);
        }

        $driver = $user->driver;

        if (! $driver) {
            throw ValidationException::withMessages([
                'email' => ['Tu usuario no tiene un perfil de repartidor asociado.'],
            ]);
        }

        // Un dispositivo = un token. Si el repartidor reinstala la
        // app o cambia de equipo, el token viejo con ese mismo
        // nombre de dispositivo se invalida.
        $user->tokens()
            ->where('name', $credentials['device_name'])
            ->delete();

        $token = $user->createToken(
            $credentials['device_name'],
            ['driver'],
        );

        return response()->json([
            'token' => $token->plainTextToken,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
            'driver' => [
                'id' => $driver->id,
                'status' => $driver->status,
                'driver_type' => $driver->driver_type,
                'vehicle_plate' => $driver->vehicle_plate,
                'vehicle_type' => $driver->vehicle_type,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente.',
        ]);
    }

    /**
     * Devuelve el usuario/repartidor autenticado. Útil para que la
     * app valide el token guardado al abrir (splash screen) sin
     * tener que volver a pedir email/password.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $driver = $user->driver;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
            'driver' => $driver ? [
                'id' => $driver->id,
                'status' => $driver->status,
                'driver_type' => $driver->driver_type,
                'vehicle_plate' => $driver->vehicle_plate,
                'vehicle_type' => $driver->vehicle_type,
            ] : null,
        ]);
    }
}
