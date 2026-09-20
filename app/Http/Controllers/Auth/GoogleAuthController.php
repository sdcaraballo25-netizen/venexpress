<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;

/**
 * "Continuar con Google" para login y registro.
 *
 * Una cuenta ya existente (mismo google_id o mismo email) inicia
 * sesión directo, igual que el login normal. Una cuenta nueva no se
 * crea aquí: a diferencia de login, registrarse requiere datos que
 * Google no entrega (rol, teléfono/cédula, y para aliado/repartidor
 * documentos que de todas formas hay que subir a mano), así que solo
 * guardamos el perfil de Google en sesión y mandamos al formulario de
 * registro normal, que ya sabe leerlo y saltarse nombre/correo/
 * contraseña.
 */
class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if (! $user) {
            Session::put('google_pending', [
                'google_id' => $googleUser->getId(),
                'name' => $googleUser->getName() ?? $googleUser->getNickname() ?? '',
                'email' => $googleUser->getEmail(),
            ]);

            return redirect()->route('register');
        }

        // Los administradores no inician sesión por Google: deben
        // usar el acceso privado (/admin/login), igual que con
        // correo/contraseña (ver login.blade.php).
        if ($user->isAdmin()) {
            return redirect()->route('login')->withErrors([
                'form.email' => 'Los administradores deben ingresar por el acceso correspondiente.',
            ]);
        }

        // Cuenta creada originalmente con correo/contraseña: la
        // vinculamos con este Google ID para que la próxima vez entre
        // directo por aquí también.
        if ($user->google_id === null) {
            $user->forceFill(['google_id' => $googleUser->getId()])->save();
        }

        Auth::login($user);

        Session::regenerate();

        return $this->redirectForUser($user);
    }

    /**
     * Mismo mapa rol -> panel que login.blade.php.
     */
    private function redirectForUser(User $user): RedirectResponse
    {
        if ($user->isCliente()) {
            return redirect()->route('cliente.dashboard');
        }

        if ($user->isChofer()) {
            return redirect()->route('repartidor.dashboard');
        }

        if ($user->isAliado()) {
            return redirect()->route('ally.dashboard');
        }

        if ($user->isAliadoTaquilla()) {
            return redirect()->route('ally.packages.create');
        }

        if ($user->isAlmacen()) {
            return redirect()->route('almacen.dashboard');
        }

        return redirect()->route('dashboard');
    }
}
