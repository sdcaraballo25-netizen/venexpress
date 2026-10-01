<?php

namespace App\Http\Controllers;

use App\Models\Ally;
use App\Models\Driver;
use App\Models\Emprendedor;
use App\Models\MensajePedido;
use App\Models\Package;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Sirve documentos de identidad (fotos de cédula, licencia, carnet de
 * circulación, fachada de agencia, evidencia de entrega) guardados en
 * el disco "documents" (config/filesystems.php — local por defecto,
 * o S3/Spaces si DOCUMENTS_DISK_DRIVER=s3 en producción), verificando
 * primero que el usuario autenticado tenga permiso para verlos.
 */
class DocumentPhotoController extends Controller
{
    public function allyStorefront(Request $request, Ally $ally): StreamedResponse
    {
        $this->authorizeAllyDocument($request->user(), $ally);

        return $this->serve($ally->storefront_photo_path);
    }

    public function allyRifDocument(Request $request, Ally $ally): StreamedResponse
    {
        $this->authorizeAllyDocument($request->user(), $ally);

        return $this->serve($ally->rif_document_path);
    }

    public function allyMercantileRegistry(Request $request, Ally $ally): StreamedResponse
    {
        $this->authorizeAllyDocument($request->user(), $ally);

        return $this->serve($ally->mercantile_registry_document_path);
    }

    public function allyOwnerIdDocument(Request $request, Ally $ally): StreamedResponse
    {
        $this->authorizeAllyDocument($request->user(), $ally);

        return $this->serve($ally->owner_id_document_path);
    }

    public function allyOwnerIdBackDocument(Request $request, Ally $ally): StreamedResponse
    {
        $this->authorizeAllyDocument($request->user(), $ally);

        return $this->serve($ally->owner_id_back_document_path);
    }

    public function driverLicense(Request $request, Driver $driver): StreamedResponse
    {
        $this->authorizeDriverDocument($request->user(), $driver);

        return $this->serve($driver->license_photo_path);
    }

    public function driverId(Request $request, Driver $driver): StreamedResponse
    {
        $this->authorizeDriverDocument($request->user(), $driver);

        return $this->serve($driver->id_photo_path);
    }

    public function driverCedulaBack(Request $request, Driver $driver): StreamedResponse
    {
        $this->authorizeDriverDocument($request->user(), $driver);

        return $this->serve($driver->cedula_back_photo_path);
    }

    public function driverSelfie(Request $request, Driver $driver): StreamedResponse
    {
        $this->authorizeDriverDocument($request->user(), $driver);

        return $this->serve($driver->selfie_photo_path);
    }

    public function driverVehiclePhoto(Request $request, Driver $driver): StreamedResponse
    {
        $this->authorizeDriverDocument($request->user(), $driver);

        return $this->serve($driver->vehicle_photo_path);
    }

    public function driverPlatePhoto(Request $request, Driver $driver): StreamedResponse
    {
        $this->authorizeDriverDocument($request->user(), $driver);

        return $this->serve($driver->plate_photo_path);
    }

    public function driverVehicleRegistration(Request $request, Driver $driver): StreamedResponse
    {
        $this->authorizeDriverDocument($request->user(), $driver);

        return $this->serve($driver->vehicle_registration_photo_path);
    }

    public function emprendedorCedulaFront(Request $request, Emprendedor $emprendedor): StreamedResponse
    {
        $this->authorizeEmprendedorDocument($request->user(), $emprendedor);

        return $this->serve($emprendedor->cedula_front_photo_path);
    }

    public function emprendedorCedulaBack(Request $request, Emprendedor $emprendedor): StreamedResponse
    {
        $this->authorizeEmprendedorDocument($request->user(), $emprendedor);

        return $this->serve($emprendedor->cedula_back_photo_path);
    }

    public function emprendedorRifDocument(Request $request, Emprendedor $emprendedor): StreamedResponse
    {
        $this->authorizeEmprendedorDocument($request->user(), $emprendedor);

        return $this->serve($emprendedor->rif_document_path);
    }

    public function emprendedorProductOrWorkspacePhoto(Request $request, Emprendedor $emprendedor): StreamedResponse
    {
        $this->authorizeEmprendedorDocument($request->user(), $emprendedor);

        return $this->serve($emprendedor->product_or_workspace_photo_path);
    }

    public function packageDeliveryEvidence(Request $request, Package $package): StreamedResponse
    {
        $this->authorizePackageDocument($request->user(), $package);

        return $this->serve($package->delivery_photo_path);
    }

    /**
     * Administradores o el propio Aliado dueño del documento.
     */
    /**
     * Adjunto del chat de un pedido del marketplace (comprobante de
     * pago, foto de guía, etc.). Lo pueden ver quienes ya tienen
     * acceso al chat: el chat_token del pedido funciona como la
     * credencial, igual que en public.marketplace.pedido (el comprador
     * no necesita cuenta). Antes estos archivos vivían en el disco
     * "public", accesibles sin ninguna verificación.
     */
    public function pedidoAttachment(string $token, MensajePedido $mensaje): StreamedResponse
    {
        $pedido = $mensaje->pedido;

        if (! $pedido || ! is_string($pedido->chat_token) || ! hash_equals($pedido->chat_token, $token)) {
            abort(404);
        }

        $path = $mensaje->archivo_path;

        if (! $path) {
            abort(404);
        }

        // Adjuntos subidos antes del cambio siguen en el disco
        // "public" hasta que se corra `php artisan
        // venexpress:move-chat-attachments`; se sirven igual desde aquí.
        foreach (['documents', 'public'] as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                return Storage::disk($disk)->response($path, $mensaje->archivo_nombre);
            }
        }

        abort(404);
    }

    protected function authorizeAllyDocument(?User $user, Ally $ally): void
    {
        if (! $user) {
            abort(403);
        }

        if ($user->isAdmin()) {
            return;
        }

        if ($user->resolveAlly()?->id === $ally->id) {
            return;
        }

        abort(403, 'No tienes permiso para ver este documento.');
    }

    /**
     * Administradores o el propio Repartidor dueño del documento.
     */
    protected function authorizeDriverDocument(?User $user, Driver $driver): void
    {
        if (! $user) {
            abort(403);
        }

        if ($user->isAdmin()) {
            return;
        }

        if ($user->isRepartidor() && $user->driver?->id === $driver->id) {
            return;
        }

        abort(403, 'No tienes permiso para ver este documento.');
    }

    /**
     * Administradores o el propio Emprendedor dueño del documento.
     */
    protected function authorizeEmprendedorDocument(?User $user, Emprendedor $emprendedor): void
    {
        if (! $user) {
            abort(403);
        }

        if ($user->isAdmin()) {
            return;
        }

        if ($user->isEmprendedor() && $user->emprendedor?->id === $emprendedor->id) {
            return;
        }

        abort(403, 'No tienes permiso para ver este documento.');
    }

    /**
     * Mismo criterio que PackageLabelController::authorizeView():
     * admin, aliado dueño del paquete o repartidor asignado.
     */
    protected function authorizePackageDocument(?User $user, Package $package): void
    {
        if (! $user) {
            abort(403);
        }

        if ($user->isAdmin()) {
            return;
        }

        $ally = $user->resolveAlly();

        if ($ally && (int) $ally->id === (int) $package->ally_id) {
            return;
        }

        if ($user->isRepartidor() && $user->driver && (int) $user->driver->id === (int) $package->driver_id) {
            return;
        }

        abort(403, 'No tienes permiso para ver este documento.');
    }

    protected function serve(?string $path): StreamedResponse
    {
        if (! $path || ! Storage::disk('documents')->exists($path)) {
            abort(404);
        }

        return Storage::disk('documents')->response($path);
    }
}
