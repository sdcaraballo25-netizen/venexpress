<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Picqer\Barcode\BarcodeGeneratorSVG;

class PackageLabelController extends Controller
{
    /**
     * Genera la etiqueta/guía en PDF de un paquete.
     *
     * A diferencia del comprobante que se ve una sola vez justo
     * después de registrar la guía (App\Livewire\Ally\PackageCreate),
     * esta ruta se puede volver a visitar en cualquier momento: sirve
     * para reimprimir una etiqueta dañada o extraviada, o para que un
     * administrador la revise sin tener que ir a la taquilla que la
     * originó.
     */
    public function pdf(Request $request, Package $package): Response
    {
        $this->authorizeView($request, $package);

        $package->loadMissing('ally', 'driver');

        $barcodeGenerator = new BarcodeGeneratorSVG();

        $barcodeSvg = $barcodeGenerator->getBarcode(
            $package->tracking_number,
            $barcodeGenerator::TYPE_CODE_128,
            2,
            60
        );

        $pdf = Pdf::loadView('pdf.package-label', [
            'package' => $package,
            'barcodeSvg' => $barcodeSvg,
        ])->setPaper([0, 0, 288, 432]); // ~ 4in x 6in, tamaño típico de etiqueta térmica.

        return $request->boolean('download')
            ? $pdf->download("guia-{$package->tracking_number}.pdf")
            : $pdf->stream("guia-{$package->tracking_number}.pdf");
    }

    /**
     * Solo puede ver la etiqueta de un paquete:
     * - un administrador (principal u operativo);
     * - el aliado (dueño o taquilla) que despachó el paquete;
     * - el repartidor asignado actualmente al paquete.
     *
     * No usamos una Policy formal aquí para mantener el cambio
     * pequeño y localizado, siguiendo el patrón ya usado en otros
     * puntos del proyecto (comprobaciones de rol en el propio
     * controlador/servicio). Si en el futuro se necesitan más reglas
     * de autorización sobre Package, vale la pena migrar esto a una
     * PackagePolicy real.
     */
    protected function authorizeView(Request $request, Package $package): void
    {
        $user = $request->user();

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

        abort(403, 'No tienes permiso para ver la guía de este paquete.');
    }
}
