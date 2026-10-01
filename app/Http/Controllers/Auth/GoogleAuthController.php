<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

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

    public function callback(Request $request): RedirectResponse
    {
        // El usuario canceló o rechazó el permiso en la pantalla de Google:
        // Google redirige aquí con "error" en vez de "code", y sin esto
        // Socialite intenta canjear un código que no existe.
        if ($request->has('error')) {
            return redirect()->route('login')->withErrors([
                'form.email' => 'No se completó el inicio de sesión con Google.',
            ]);
        }

        // Sin stateless(): Socialite valida el parámetro "state" contra
        // la sesión, lo que impide que un atacante complete el login
        // con SU cuenta de Google en el navegador de otra persona
        // (login CSRF).
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (InvalidStateException) {
            return redirect()->route('login')->withErrors([
                'form.email' => 'La sesión con Google expiró. Inténtalo de nuevo.',
            ]);
        }

        // Google marca si el correo está verificado. Un correo no
        // verificado no prueba que la persona sea dueña de esa
        // dirección, así que no debe abrir ni vincular una cuenta
        // existente con ese mismo correo.
        $raw = (array) $googleUser->getRaw();
        $emailVerified = ($raw['email_verified'] ?? $raw['verified_email'] ?? true) !== false;

        if (! $emailVerified || ! $googleUser->getEmail()) {
            return redirect()->route('login')->withErrors([
                'form.email' => 'Tu correo de Google no está verificado. Verifícalo en Google o inicia sesión con tu contraseña.',
            ]);
        }

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

        // Mismo criterio que LoginForm::authenticate(): una cuenta
        // desactivada no inicia sesión, tampoco por Google.
        if (! $user->isActive()) {
            return redirect()->route('login')->withErrors([
                'form.email' => 'Esta cuenta está inactiva. Contacta a un administrador.',
            ]);
        }

        // Cuenta creada originalmente con correo/contraseña: la
        // vinculamos con este Google ID para que la próxima vez entre
        // directo por aquí también.
        if ($user->google_id === null) {
            $user->forceFill(['google_id' => $googleUser->getId()])->save();
        }

        // Google acaba de probar que esta persona controla el correo
        // de la cuenta: un cliente que nunca completó el código de
        // verificación queda verificado (si no, EnsureAccountIsVerified
        // lo devolvería a /verify-account en cada intento).
        if ($user->isCliente() && ! $user->isAccountVerified()) {
            $user->markAccountAsVerified();
        }

        Auth::login($user);

        Session::regenerate();

        // Mismo mapa rol -> panel que login.blade.php (incluye
        // Emprendedor, que antes caía en el dashboard genérico).
        return redirect()->route($user->homeRouteName());
    }
}
