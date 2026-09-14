<?php

namespace App\Livewire\Ally;

use App\Models\Ally;
use App\Models\Package;
use App\Models\User;
use App\Services\AllyStaffService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Gestión de Taquillas (RF-ALI-02): el Aliado Administrador crea y
 * administra los usuarios de Taquilla de su propia agencia, y ve un
 * resumen de las ventas de hoy de cada uno. Reutiliza AllyStaffService
 * y Ally::staffUsers() tal cual — ya existían, solo no tenían pantalla.
 */
#[Layout('layouts.ally')]
class StaffManager extends Component
{
    use WithPagination;

    public ?int $editingId = null;

    public string $name = '';
    public string $username = '';
    public string $password = '';
    public string $password_confirmation = '';

    public bool $showForm = false;

    public ?string $successMessage = null;

    protected function ally(): Ally
    {
        $user = Auth::user();
        $ally = $user?->resolveAlly();

        abort_unless($ally && $user->isAliado(), 403, 'Solo el Aliado Administrador puede gestionar Taquillas.');

        return $ally;
    }

    protected function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:150'],
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-z0-9._-]+$/',
                $this->editingId
                    ? 'unique:users,username,'.$this->editingId
                    : 'unique:users,username',
            ],
        ];

        if ($this->editingId === null) {
            $rules['password'] = ['required', 'string', 'confirmed', Rules\Password::defaults()];
        } elseif ($this->password !== '') {
            $rules['password'] = ['string', 'confirmed', Rules\Password::defaults()];
        }

        return $rules;
    }

    protected function messages(): array
    {
        return [
            'username.regex' => 'Solo minúsculas, números, puntos, guiones y guiones bajos (sin espacios ni @).',
        ];
    }

    public function startCreate(): void
    {
        $this->reset(['editingId', 'name', 'username', 'password', 'password_confirmation']);
        $this->resetErrorBag();
        $this->showForm = true;
    }

    public function edit(int $userId): void
    {
        $staff = $this->ally()->staffUsers()->findOrFail($userId);

        $this->editingId = $staff->id;
        $this->name = $staff->name;
        $this->username = (string) $staff->username;
        $this->password = '';
        $this->password_confirmation = '';
        $this->resetErrorBag();
        $this->showForm = true;
    }

    public function cancel(): void
    {
        $this->reset(['editingId', 'name', 'username', 'password', 'password_confirmation']);
        $this->resetErrorBag();
        $this->showForm = false;
    }

    public function save(AllyStaffService $staffService): void
    {
        $ally = $this->ally();
        $data = $this->validate();

        if ($this->editingId) {
            $staff = $ally->staffUsers()->findOrFail($this->editingId);

            abort_unless(Auth::user()->canEditUser($staff), 403);

            $staffService->update($staff, $data);
            $this->successMessage = "Se actualizó a {$staff->name}.";
        } else {
            $staff = $staffService->create($ally, $data);
            $this->successMessage = "Se creó el usuario de taquilla \"{$staff->name}\".";
        }

        $this->cancel();
    }

    public function toggleActive(int $userId, AllyStaffService $staffService): void
    {
        $staff = $this->ally()->staffUsers()->findOrFail($userId);

        abort_unless(Auth::user()->canDeactivateUser($staff), 403);

        if ($staff->isActive()) {
            $staffService->deactivate($staff);
            $this->successMessage = "Se desactivó a {$staff->name}. Ya no podrá iniciar sesión.";
        } else {
            $staffService->activate($staff);
            $this->successMessage = "Se reactivó a {$staff->name}.";
        }
    }

    /**
     * Total facturado y guías registradas hoy, agrupado por quién las
     * registró (cualquier usuario de la agencia, incluyendo al
     * propio Aliado Administrador si registra guías él mismo).
     *
     * @return \Illuminate\Support\Collection<int, object{registered_by_user_id:int,guides:int,total_usd:float}>
     */
    protected function todaySalesByUser(Ally $ally)
    {
        return Package::query()
            ->where('ally_id', $ally->id)
            ->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
            ->whereNotNull('registered_by_user_id')
            ->selectRaw('registered_by_user_id, COUNT(*) as guides, SUM(total_price_usd) as total_usd')
            ->groupBy('registered_by_user_id')
            ->get()
            ->keyBy('registered_by_user_id');
    }

    public function render()
    {
        $ally = $this->ally();

        return view('livewire.ally.staff-manager', [
            'ally' => $ally,
            'staff' => $ally->staffUsers()->orderBy('name')->paginate(15),
            'todaySalesByUser' => $this->todaySalesByUser($ally),
        ]);
    }
}
