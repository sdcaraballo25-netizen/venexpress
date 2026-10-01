<?php

namespace App\Console\Commands;

use App\Models\MensajePedido;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MoveChatAttachmentsToPrivateDisk extends Command
{
    protected $signature = 'venexpress:move-chat-attachments';

    protected $description = 'Mueve los adjuntos del chat de pedidos del disco "public" al disco privado "documents"';

    /**
     * Comando de una sola vez, para el despliegue que dejó de guardar
     * los adjuntos del chat (comprobantes de pago) en el disco
     * "public". Mientras no se corra, esos archivos siguen siendo
     * accesibles por su URL pública /storage/mensajes-pedido/...
     * DocumentPhotoController::pedidoAttachment() los sirve desde
     * cualquiera de los dos discos, así que se puede correr en
     * cualquier momento sin romper el chat.
     */
    public function handle(): int
    {
        $moved = 0;

        MensajePedido::query()
            ->whereNotNull('archivo_path')
            ->orderBy('id')
            ->each(function (MensajePedido $mensaje) use (&$moved) {
                $path = $mensaje->archivo_path;

                if (! Storage::disk('public')->exists($path)) {
                    return;
                }

                if (! Storage::disk('documents')->exists($path)) {
                    Storage::disk('documents')->put($path, Storage::disk('public')->get($path));
                }

                Storage::disk('public')->delete($path);
                $moved++;
            });

        $this->info("Adjuntos movidos al disco privado: {$moved}.");

        return self::SUCCESS;
    }
}
