<?php

namespace App\Http\Controllers;

use App\Models\Ally;
use App\Models\Driver;
use App\Models\Package;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Sirve documentos de identidad (fotos de cédula, licencia, carnet de
 * circulación, fachada de agencia, evidencia de entrega) guardados en
 * el disco privado ("local"), verificando primero que el usuario
 * autenticado tenga permiso para verlos.
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

    public function driverVehicleRegistration(Request $request, Driver $driver): StreamedResponse
    {
        $this->authorizeDriverDocument($request->user(), $driver);

        return $this->serve($driver->vehicle_registration_photo_path);
    }

    public function packageDeliveryEvidence(Request $request, Package $package): StreamedResponse
    {
        $this->authorizePackageDocument($request->user(), $package);

        return $this->serve($package->delivery_photo_path);
    }

    /**
     * Administradores o el propio Aliado dueño del documento.
     */
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
        if (! $path || ! Storage::disk('local')->exists($path)) {
            abort(404);
        }

        return Storage::disk('local')->response($path);
    }
}
