<?php

namespace App\Livewire\Emprendedor;

use App\Models\Emprendedor;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Perfil público de la tienda: lo que un comprador ve en la página
 * del emprendedor (public.marketplace.store) antes de decidir si le
 * paga por fuera de la plataforma. Sin esto, esa página solo mostraba
 * el nombre del negocio — nada que distinga a un emprendedor real de
 * una cuenta recién creada para estafar.
 *
 * Teléfono y correo no se editan aquí: ya son editables por todos los
 * roles desde /profile (Livewire\Profile\Show), y esta pantalla los
 * reutiliza tal cual (Auth::user()->phone / ->email) en vez de
 * duplicarlos.
 */
#[Layout('layouts.emprendedor')]
#[Title('Mi Tienda')]
class Perfil extends Component
{
    use WithFileUploads;

    public string $descripcion = '';

    public string $address = '';

    public $logo = null;

    public $cover = null;

    public ?string $existingLogoPath = null;

    public ?string $existingCoverPath = null;

    public ?string $successMessage = null;

    protected function emprendedor(): Emprendedor
    {
        return Auth::user()->emprendedor;
    }

    public function mount(): void
    {
        $emprendedor = $this->emprendedor();

        $this->descripcion = $emprendedor->descripcion ?? '';
        $this->address = $emprendedor->address ?? '';
        $this->existingLogoPath = $emprendedor->logo_path;
        $this->existingCoverPath = $emprendedor->cover_photo_path;
    }

    public function save(): void
    {
        $this->validate([
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'address' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $data = [
            'descripcion' => $this->descripcion !== '' ? $this->descripcion : null,
            'address' => $this->address !== '' ? $this->address : null,
        ];

        if ($this->logo) {
            $data['logo_path'] = $this->logo->store('emprendedores/logos', 'public');
        }

        if ($this->cover) {
            $data['cover_photo_path'] = $this->cover->store('emprendedores/portadas', 'public');
        }

        $this->emprendedor()->update($data);

        $this->reset(['logo', 'cover']);
        $this->existingLogoPath = $this->emprendedor()->logo_path;
        $this->existingCoverPath = $this->emprendedor()->cover_photo_path;

        $this->successMessage = 'Perfil de tienda actualizado correctamente.';
    }

    public function render()
    {
        return view('livewire.emprendedor.perfil', [
            'emprendedor' => $this->emprendedor(),
        ]);
    }
}
