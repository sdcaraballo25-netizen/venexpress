<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Picqer\Barcode\BarcodeGeneratorSVG;

class PackageLabelController extends Controller
{
    /**
     * Genera la guía/etiqueta PDF de un paquete.
     */
    public function pdf(Request $request, Package $package): Response
    {
        $this->authorizeView($request, $package);

        $package->loadMissing('ally', 'driver');

        /*
        |--------------------------------------------------------------------------
        | CÓDIGO DE BARRAS
        |--------------------------------------------------------------------------
        */

        $barcodeGenerator = new BarcodeGeneratorSVG();

        $barcodeSvg = $barcodeGenerator->getBarcode(
            $package->tracking_number,
            $barcodeGenerator::TYPE_CODE_128,
            2,
            60
        );

        /*
        |--------------------------------------------------------------------------
        | QR
        |--------------------------------------------------------------------------
        |
        | Se genera como SVG. No necesita la extensión GD.
        |
        */

        $qrResult = (new Builder(
            writer: new SvgWriter(),
            writerOptions: [],
            data: $package->tracking_number,
            size: 180,
            margin: 10,
        ))->build();

        $qrDataUri = $qrResult->getDataUri();

        /*
        |--------------------------------------------------------------------------
        | PDF
        |--------------------------------------------------------------------------
        */

        $pdf = Pdf::loadView('pdf.package-label', [
            'package' => $package,
            'barcodeSvg' => $barcodeSvg,
            'qrDataUri' => $qrDataUri,
        ])->setPaper([0, 0, 288, 432]);

        return $request->boolean('download')
            ? $pdf->download(
                "guia-{$package->tracking_number}.pdf"
            )
            : $pdf->stream(
                "guia-{$package->tracking_number}.pdf"
            );
    }

    /**
     * Verifica que el usuario pueda consultar la guía.
     */
    protected function authorizeView(
        Request $request,
        Package $package
    ): void {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        /*
         * Administradores.
         */

        if ($user->isAdmin()) {
            return;
        }

        /*
         * Aliado propietario del paquete.
         */

        $ally = $user->resolveAlly();

        if (
            $ally
            && (int) $ally->id === (int) $package->ally_id
        ) {
            return;
        }

        /*
         * Repartidor asignado al paquete.
         */

        if (
            $user->isRepartidor()
            && $user->driver
            && (int) $user->driver->id === (int) $package->driver_id
        ) {
            return;
        }

        abort(
            403,
            'No tienes permiso para ver la guía de este paquete.'
        );
    }
}
