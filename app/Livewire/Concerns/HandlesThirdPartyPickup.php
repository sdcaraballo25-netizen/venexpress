<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Retiro en persona por un tercero autorizado por el destinatario
 * (Ally\PackagePickup, Almacen\Dashboard): en vez de la cédula del
 * destinatario se registra la de quien retira, su nombre, una foto de
 * su cédula y una de la copia de la cédula del destinatario. Ver
 * PackageService::completeAgencyPickup().
 *
 * El componente que lo usa debe usar también Livewire\WithFileUploads.
 */
trait HandlesThirdPartyPickup
{
    public bool $byThirdParty = false;

    public string $thirdPartyName = '';

    /** @var TemporaryUploadedFile|null */
    public $thirdPartyIdPhoto = null;

    /** @var TemporaryUploadedFile|null */
    public $recipientIdCopy = null;

    protected function thirdPartyRules(): array
    {
        if (! $this->byThirdParty) {
            return [];
        }

        return [
            'thirdPartyName' => ['required', 'string', 'max:150'],
            'thirdPartyIdPhoto' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'recipientIdCopy' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    protected function thirdPartyMessages(): array
    {
        return [
            'thirdPartyName.required' => 'Indica el nombre de quien retira.',
            'thirdPartyIdPhoto.required' => 'Toma una foto de la cédula de quien retira.',
            'recipientIdCopy.required' => 'Toma una foto de la copia de la cédula del destinatario.',
        ];
    }

    /**
     * @return array{0: ?string, 1: ?string} [foto de la cédula de quien retira, copia de la del destinatario]
     */
    protected function storeThirdPartyPhotos(): array
    {
        if (! $this->byThirdParty) {
            return [null, null];
        }

        return [
            $this->thirdPartyIdPhoto->store('third-party-ids', 'documents'),
            $this->recipientIdCopy->store('third-party-ids', 'documents'),
        ];
    }

    protected function deleteThirdPartyPhotos(array $paths): void
    {
        foreach (array_filter($paths) as $path) {
            Storage::disk('documents')->delete($path);
        }
    }

    protected function resetThirdParty(): void
    {
        $this->reset(['byThirdParty', 'thirdPartyName', 'thirdPartyIdPhoto', 'recipientIdCopy']);
    }
}
