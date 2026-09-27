<?php

namespace App\Livewire\Ally;

use App\Models\Ally;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Pantalla de auto-servicio para que el aliado complete o corrija su
 * verificación de identidad. A propósito NO vive detrás de
 * 'account.approved' (ver routes/web.php) — mismo motivo que
 * Driver\Verificacion.
 *
 * Nombre y teléfono NO se editan aquí: ya son editables desde
 * /profile (Livewire\Profile\Show).
 *
 * Solo el Aliado Administrador (role 'aliado') tiene identidad propia
 * que verificar; el personal de Taquilla (aliado_taquilla) opera bajo
 * la verificación de su agencia, no tiene una propia.
 */
#[Layout('layouts.ally')]
#[Title('Mi Verificación')]
class Verificacion extends Component
{
    use WithFileUploads;

    public string $business_name = '';

    public string $rif = '';

    public string $address = '';

    public string $city = '';

    public string $state = '';

    public $owner_id_document = null;

    public $owner_id_back_document = null;

    public $rif_document = null;

    public $storefront_photo = null;

    public ?string $existingOwnerIdDocumentPath = null;

    public ?string $existingOwnerIdBackDocumentPath = null;

    public ?string $existingRifDocumentPath = null;

    public ?string $existingStorefrontPhotoPath = null;

    protected function ally(): Ally
    {
        return Auth::user()->ally;
    }

    public function mount(): void
    {
        $ally = $this->ally();

        $this->business_name = $ally->business_name ?? '';
        $this->rif = $ally->rif ?? '';
        $this->address = $ally->address ?? '';
        $this->city = $ally->city ?? '';
        $this->state = $ally->state ?? '';

        $this->existingOwnerIdDocumentPath = $ally->owner_id_document_path;
        $this->existingOwnerIdBackDocumentPath = $ally->owner_id_back_document_path;
        $this->existingRifDocumentPath = $ally->rif_document_path;
        $this->existingStorefrontPhotoPath = $ally->storefront_photo_path;
    }

    protected function requiredUnlessExists(?string $existingPath): \Closure
    {
        return function (string $attribute, $value, \Closure $fail) use ($existingPath) {
            if (! $value && ! $existingPath) {
                $fail('Debes subir este documento.');
            }
        };
    }

    /**
     * Sin 'nullable' a propósito: esa regla corta el resto de
     * validaciones (incluida requiredUnlessExists) apenas el valor es
     * null, que es exactamente el caso que necesitamos seguir
     * validando.
     */
    protected function documentRules($uploadedValue, ?string $existingPath, array $formatRules): array
    {
        return [
            Rule::when($uploadedValue !== null, $formatRules),
            $this->requiredUnlessExists($existingPath),
        ];
    }

    public function submit(): void
    {
        $ally = $this->ally();

        if (in_array($ally->verification_status, [Ally::VERIFICATION_VERIFIED, Ally::VERIFICATION_IN_REVIEW], true)) {
            return;
        }

        $this->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'rif' => [
                'required', 'string', 'max:20',
                Rule::unique('allies', 'rif')->ignore($ally->id),
            ],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:255'],

            'owner_id_document' => $this->documentRules($this->owner_id_document, $this->existingOwnerIdDocumentPath, ['file', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx', 'max:8192']),
            'owner_id_back_document' => $this->documentRules($this->owner_id_back_document, $this->existingOwnerIdBackDocumentPath, ['file', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx', 'max:8192']),
            'rif_document' => $this->documentRules($this->rif_document, $this->existingRifDocumentPath, ['file', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx', 'max:8192']),
            'storefront_photo' => $this->documentRules($this->storefront_photo, $this->existingStorefrontPhotoPath, ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096']),
        ]);

        $data = [
            'business_name' => $this->business_name,
            'rif' => $this->rif,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,

            'verification_status' => Ally::VERIFICATION_IN_REVIEW,
            'verification_rejection_reason' => null,
        ];

        if ($this->owner_id_document) {
            $data['owner_id_document_path'] = $this->owner_id_document->store('allies', 'documents');
        }

        if ($this->owner_id_back_document) {
            $data['owner_id_back_document_path'] = $this->owner_id_back_document->store('allies', 'documents');
        }

        if ($this->rif_document) {
            $data['rif_document_path'] = $this->rif_document->store('allies', 'documents');
        }

        if ($this->storefront_photo) {
            $data['storefront_photo_path'] = $this->storefront_photo->store('allies', 'documents');
        }

        $ally->update($data);

        $this->redirect(route('account.pending'), navigate: false);
    }

    public function render()
    {
        return view('livewire.ally.verificacion', [
            'ally' => $this->ally(),
        ]);
    }
}
