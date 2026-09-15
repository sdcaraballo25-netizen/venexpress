<?php

namespace App\Livewire\Driver;

use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Descarga de la app de repartidor. El APK se sube manualmente por
 * un administrador a storage/app/public/downloads/ (disco 'public',
 * ya enlazado con `storage:link`); mientras no exista el archivo, se
 * muestra un aviso en vez de un enlace roto.
 *
 * IMPORTANTE: esta página vive DETRÁS del login de repartidor
 * (routes/web.php, grupo 'repartidor'), no en el sitio público.
 * Exponer el APK sin autenticación permitiría a cualquiera
 * descargarlo y explorar la superficie de la API del driver sin ser
 * un repartidor real.
 */
#[Layout('layouts.driver', ['title' => 'Descargar app'])]
class AppDownload extends Component
{
    public const APK_PATH = 'downloads/venexpress-repartidor.apk';

    public function render()
    {
        return view('livewire.driver.app-download', [
            'apkAvailable' => Storage::disk('public')->exists(self::APK_PATH),
            'apkUrl' => Storage::disk('public')->url(self::APK_PATH),
        ]);
    }
}
