<?php

namespace App\Livewire\Driver;

use App\Models\Driver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Pantalla de auto-servicio para que el repartidor complete o corrija
 * su verificación de identidad. A propósito NO vive detrás de
 * 'account.approved' (ver routes/web.php): si lo estuviera, un
 * repartidor todavía no verificado nunca podría llegar aquí para
 * completarla — quedaría atrapado en account-pending.blade.php sin
 * forma de avanzar.
 *
 * Nombre y teléfono NO se editan aquí: ya son editables desde
 * /profile (Livewire\Profile\Show), mismo criterio que
 * Emprendedor\Perfil ya documenta.
 */
#[Layout('layouts.driver')]
#[Title('Mi Verificación')]
class Verificacion extends Component
{
    use WithFileUploads;

    public string $cedula = '';

    public string $city = '';

    public string $state = '';

    public string $vehicle_plate = '';

    public string $vehicle_type = '';

    public $id_photo = null;

    public $cedula_back_photo = null;

    public $selfie_photo = null;

    public $license_photo = null;

    public $vehicle_photo = null;

    public $plate_photo = null;

    public ?string $existingIdPhotoPath = null;

    public ?string $existingCedulaBackPhotoPath = null;

    public ?string $existingSelfiePhotoPath = null;

    public ?string $existingLicensePhotoPath = null;

    public ?string $existingVehiclePhotoPath = null;

    public ?string $existingPlatePhotoPath = null;

    public ?string $successMessage = null;

    protected function driver(): Driver
    {
        return Auth::user()->driver;
    }

    public function mount(): void
    {
        $driver = $this->driver();

        $this->cedula = $driver->cedula ?? '';
        $this->city = $driver->city ?? '';
        $this->state = $driver->state ?? '';
        $this->vehicle_plate = $driver->vehicle_plate ?? '';
        $this->vehicle_type = $driver->vehicle_type ?? '';

        $this->existingIdPhotoPath = $driver->id_photo_path;
        $this->existingCedulaBackPhotoPath = $driver->cedula_back_photo_path;
        $this->existingSelfiePhotoPath = $driver->selfie_photo_path;
        $this->existingLicensePhotoPath = $driver->license_photo_path;
        $this->existingVehiclePhotoPath = $driver->vehicle_photo_path;
        $this->existingPlatePhotoPath = $driver->plate_photo_path;
    }

    /**
     * Un documento es obligatorio solo si nunca se subió uno antes:
     * si ya existe una ruta guardada, el repartidor puede dejarlo tal
     * cual sin tener que volver a subirlo en cada corrección.
     */
    protected function requiredUnlessExists(?string $existingPath): \Closure
    {
        return function (string $attribute, $value, \Closure $fail) use ($existingPath) {
            if (! $value && ! $existingPath) {
                $fail('Debes subir este documento.');
            }
        };
    }

    /**
     * Reglas para un documento que es obligatorio solo si nunca se
     * subió uno antes. Sin 'nullable' a propósito: esa regla corta el
     * resto de validaciones (incluida requiredUnlessExists) apenas el
     * valor es null, que es exactamente el caso que necesitamos
     * seguir validando.
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
        $driver = $this->driver();

        // Ya verificado, o ya enviado y esperando revisión: no tiene
        // sentido volver a poner/dejar la cuenta en EN_REVISION desde
        // aquí. La vista tampoco muestra el formulario en esos casos,
        // pero se guarda igual por seguridad ante una llamada directa
        // a este método.
        if (in_array($driver->verification_status, [Driver::VERIFICATION_VERIFIED, Driver::VERIFICATION_IN_REVIEW], true)) {
            return;
        }

        $this->validate([
            'cedula' => ['required', 'string', 'max:20'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:255'],
            'vehicle_plate' => [
                'required', 'string', 'max:20',
                Rule::unique('drivers', 'vehicle_plate')->ignore($driver->id),
            ],
            'vehicle_type' => ['required', 'string', 'max:255'],

            'id_photo' => $this->documentRules($this->id_photo, $this->existingIdPhotoPath, ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096']),
            'cedula_back_photo' => $this->documentRules($this->cedula_back_photo, $this->existingCedulaBackPhotoPath, ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096']),
            'selfie_photo' => $this->documentRules($this->selfie_photo, $this->existingSelfiePhotoPath, ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096']),
            'license_photo' => $this->documentRules($this->license_photo, $this->existingLicensePhotoPath, ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096']),
            'vehicle_photo' => $this->documentRules($this->vehicle_photo, $this->existingVehiclePhotoPath, ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096']),
            'plate_photo' => $this->documentRules($this->plate_photo, $this->existingPlatePhotoPath, ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096']),
        ]);

        $data = [
            'cedula' => $this->cedula,
            'city' => $this->city,
            'state' => $this->state,
            'vehicle_plate' => $this->vehicle_plate,
            'vehicle_type' => $this->vehicle_type,

            // Enviar a revisión: el motivo de un rechazo anterior ya
            // no aplica a esta nueva versión de los datos.
            'verification_status' => Driver::VERIFICATION_IN_REVIEW,
            'verification_rejection_reason' => null,
        ];

        if ($this->id_photo) {
            $data['id_photo_path'] = $this->id_photo->store('drivers', 'documents');
        }

        if ($this->cedula_back_photo) {
            $data['cedula_back_photo_path'] = $this->cedula_back_photo->store('drivers', 'documents');
        }

        if ($this->selfie_photo) {
            $data['selfie_photo_path'] = $this->selfie_photo->store('drivers', 'documents');
        }

        if ($this->license_photo) {
            $data['license_photo_path'] = $this->license_photo->store('drivers', 'documents');
        }

        if ($this->vehicle_photo) {
            $data['vehicle_photo_path'] = $this->vehicle_photo->store('drivers', 'documents');
        }

        if ($this->plate_photo) {
            $data['plate_photo_path'] = $this->plate_photo->store('drivers', 'documents');
        }

        $driver->update($data);

        $this->redirect(route('account.pending'), navigate: false);
    }

    public function render()
    {
        return view('livewire.driver.verificacion', [
            'driver' => $this->driver(),
        ]);
    }
}
