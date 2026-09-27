<?php

namespace App\Livewire\Emprendedor;

use App\Models\Emprendedor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Pantalla de auto-servicio para que el emprendedor complete o
 * corrija su verificación de identidad. A propósito NO vive detrás de
 * 'account.approved' (ver routes/web.php) — mismo motivo que
 * Driver\Verificacion / Ally\Verificacion: emprendedor.perfil (logo,
 * portada) sí está detrás de ese middleware, pero esta pantalla no
 * puede estarlo, porque es la única forma de completar los datos que
 * hacen falta para que ese middleware alguna vez deje pasar.
 *
 * Nombre y teléfono NO se editan aquí: ya son editables desde
 * /profile. logo_path/cover_photo_path tampoco (esos son marketing
 * público del Marketplace, ver Emprendedor\Perfil — no son evidencia
 * de verificación).
 */
#[Layout('layouts.emprendedor')]
#[Title('Mi Verificación')]
class Verificacion extends Component
{
    use WithFileUploads;

    public string $cedula = '';

    public string $city = '';

    public string $state = '';

    public string $business_name = '';

    public string $descripcion = '';

    public string $document_id = '';

    public $cedula_front_photo = null;

    public $cedula_back_photo = null;

    public $rif_document = null;

    public $product_or_workspace_photo = null;

    public ?string $existingCedulaFrontPhotoPath = null;

    public ?string $existingCedulaBackPhotoPath = null;

    public ?string $existingRifDocumentPath = null;

    public ?string $existingProductOrWorkspacePhotoPath = null;

    protected function emprendedor(): Emprendedor
    {
        return Auth::user()->emprendedor;
    }

    public function mount(): void
    {
        $emprendedor = $this->emprendedor();

        $this->cedula = $emprendedor->cedula ?? '';
        $this->city = $emprendedor->city ?? '';
        $this->state = $emprendedor->state ?? '';
        $this->business_name = $emprendedor->business_name ?? '';
        $this->descripcion = $emprendedor->descripcion ?? '';
        $this->document_id = $emprendedor->document_id ?? '';

        $this->existingCedulaFrontPhotoPath = $emprendedor->cedula_front_photo_path;
        $this->existingCedulaBackPhotoPath = $emprendedor->cedula_back_photo_path;
        $this->existingRifDocumentPath = $emprendedor->rif_document_path;
        $this->existingProductOrWorkspacePhotoPath = $emprendedor->product_or_workspace_photo_path;
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
        $emprendedor = $this->emprendedor();

        if (in_array($emprendedor->verification_status, [Emprendedor::VERIFICATION_VERIFIED, Emprendedor::VERIFICATION_IN_REVIEW], true)) {
            return;
        }

        $this->validate([
            'cedula' => ['required', 'string', 'max:20'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:255'],
            'business_name' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'document_id' => ['required', 'string', 'max:20'],

            'cedula_front_photo' => $this->documentRules($this->cedula_front_photo, $this->existingCedulaFrontPhotoPath, ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096']),
            'cedula_back_photo' => $this->documentRules($this->cedula_back_photo, $this->existingCedulaBackPhotoPath, ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096']),
            'rif_document' => $this->documentRules($this->rif_document, $this->existingRifDocumentPath, ['file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:8192']),
            'product_or_workspace_photo' => $this->documentRules($this->product_or_workspace_photo, $this->existingProductOrWorkspacePhotoPath, ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096']),
        ]);

        $data = [
            'cedula' => $this->cedula,
            'city' => $this->city,
            'state' => $this->state,
            'business_name' => $this->business_name,
            'descripcion' => $this->descripcion !== '' ? $this->descripcion : null,
            'document_id' => $this->document_id,

            'verification_status' => Emprendedor::VERIFICATION_IN_REVIEW,
            'verification_rejection_reason' => null,
        ];

        if ($this->cedula_front_photo) {
            $data['cedula_front_photo_path'] = $this->cedula_front_photo->store('emprendedores', 'documents');
        }

        if ($this->cedula_back_photo) {
            $data['cedula_back_photo_path'] = $this->cedula_back_photo->store('emprendedores', 'documents');
        }

        if ($this->rif_document) {
            $data['rif_document_path'] = $this->rif_document->store('emprendedores', 'documents');
        }

        if ($this->product_or_workspace_photo) {
            $data['product_or_workspace_photo_path'] = $this->product_or_workspace_photo->store('emprendedores', 'documents');
        }

        $emprendedor->update($data);

        $this->redirect(route('account.pending'), navigate: false);
    }

    public function render()
    {
        return view('livewire.emprendedor.verificacion', [
            'emprendedor' => $this->emprendedor(),
        ]);
    }
}
