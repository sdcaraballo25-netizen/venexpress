<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Ally;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Gestión de Usuarios')]
class UsersManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $roleFilter = '';
    public string $statusFilter = '';

    public bool $showCreateModal = false;
    public bool $showConfirmModal = false;
    public bool $showDeleteModal = false;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $role = User::ROLE_ALIADO;

    public string $business_name = '';
    public string $rif = '';
    public string $city = '';
    public string $address = '';

    public string $vehicle_plate = '';
    public string $vehicle_type = '';
    public string $phone = '';

    public string $adminPassword = '';
    public ?int $pendingUserId = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRoleFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        abort_unless(auth()->user()?->canManageUsers(), 403);
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->showConfirmModal = false;
        $this->adminPassword = '';
        $this->resetValidation();
    }

    public function requestCreate(): void
    {
        abort_unless(auth()->user()?->canManageUsers(), 403);

        $this->validate($this->creationRules());

        if (! auth()->user()->canCreateRole($this->role)) {
            $this->addError('role', 'No tienes permiso para crear este tipo de usuario.');
            return;
        }

        $this->showConfirmModal = true;
        $this->adminPassword = '';
    }

    public function createUser(): void
    {
        $actor = auth()->user();
        abort_unless($actor?->canManageUsers(), 403);

        $this->validate($this->creationRules());

        if (! $actor->canCreateRole($this->role)) {
            $this->addError('role', 'No tienes permiso para crear este tipo de usuario.');
            $this->showConfirmModal = false;
            return;
        }

        $this->validate([
            'adminPassword' => ['required', 'string'],
        ], [
            'adminPassword.required' => 'Debes introducir tu contraseña de administrador.',
        ]);

        if (! Hash::check($this->adminPassword, $actor->password)) {
            $this->addError('adminPassword', 'La contraseña de administrador no es correcta.');
            return;
        }

        $validated = $this->validate($this->creationRules());

        DB::transaction(function () use ($actor, $validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => strtolower($validated['email']),
                'password' => $validated['password'],
                'role' => $validated['role'],
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ]);

            if ($user->isAliado()) {
                Ally::create([
                    'user_id' => $user->id,
                    'business_name' => $validated['business_name'],
                    'rif' => $validated['rif'],
                    'city' => $validated['city'],
                    'address' => $validated['address'],
                    'commission_percentage' => 10.00,
                    'status' => Ally::STATUS_ACTIVE,
                ]);
            }

            if ($user->isRepartidor()) {
                Driver::create([
                    'user_id' => $user->id,
                    'vehicle_plate' => $validated['vehicle_plate'],
                    'vehicle_type' => $validated['vehicle_type'],
                    'phone' => $validated['phone'],
                    'status' => Driver::STATUS_ACTIVE,
                ]);
            }

            AuditLog::create([
                'actor_user_id' => $actor->id,
                'action' => 'user.created',
                'target_type' => User::class,
                'target_id' => $user->id,
                'description' => "Creó al usuario {$user->name} con rol {$user->role}.",
                'metadata' => ['role' => $user->role, 'email' => $user->email],
                'ip_address' => request()->ip(),
            ]);
        });

        $this->closeCreateModal();
        session()->flash('success', 'Usuario creado correctamente.');
        $this->resetPage();
    }

    public function toggleStatus(int $userId): void
    {
        $actor = auth()->user();
        $target = User::findOrFail($userId);

        abort_unless($actor?->canDeactivateUser($target), 403);

        $newStatus = $target->isActive()
            ? User::STATUS_INACTIVE
            : User::STATUS_ACTIVE;

        $target->update(['status' => $newStatus]);

        AuditLog::create([
            'actor_user_id' => $actor->id,
            'action' => 'user.status_changed',
            'target_type' => User::class,
            'target_id' => $target->id,
            'description' => "Cambió el estado de {$target->name} a {$newStatus}.",
            'metadata' => ['status' => $newStatus],
            'ip_address' => request()->ip(),
        ]);

        session()->flash('success', $newStatus === User::STATUS_ACTIVE
            ? 'Usuario activado correctamente.'
            : 'Usuario desactivado correctamente.');
    }

    public function requestDelete(int $userId): void
    {
        $actor = auth()->user();
        $target = User::findOrFail($userId);

        abort_unless($actor?->canDeleteUser($target), 403);

        $this->pendingUserId = $target->id;
        $this->adminPassword = '';
        $this->showDeleteModal = true;
    }

    public function deleteUser(): void
    {
        $actor = auth()->user();
        $target = $this->pendingUserId ? User::find($this->pendingUserId) : null;

        abort_unless($target && $actor?->canDeleteUser($target), 403);

        $this->validate([
            'adminPassword' => ['required', 'string'],
        ], [
            'adminPassword.required' => 'Debes introducir tu contraseña de administrador.',
        ]);

        if (! Hash::check($this->adminPassword, $actor->password)) {
            $this->addError('adminPassword', 'La contraseña de administrador no es correcta.');
            return;
        }

        DB::transaction(function () use ($actor, $target) {
            $name = $target->name;
            $role = $target->role;

            $target->delete();

            AuditLog::create([
                'actor_user_id' => $actor->id,
                'action' => 'user.deleted',
                'target_type' => User::class,
                'target_id' => $target->id,
                'description' => "Eliminó al usuario {$name} con rol {$role}.",
                'metadata' => ['role' => $role],
                'ip_address' => request()->ip(),
            ]);
        });

        $this->showDeleteModal = false;
        $this->pendingUserId = null;
        $this->adminPassword = '';
        session()->flash('success', 'Usuario eliminado correctamente.');
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search !== '', function ($query) {
                $term = '%' . $this->search . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term);
                });
            })
            ->when($this->roleFilter !== '', fn ($query) => $query->where('role', $this->roleFilter))
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
            ->latest()
            ->paginate(12);

        return view('livewire.admin.users-manager', [
            'users' => $users,
            'roleLabels' => User::roleLabels(),
        ]);
    }

    protected function creationRules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'in:' . implode(',', array_keys(User::roleLabels()))],
        ];

        if ($this->role === User::ROLE_ALIADO) {
            $rules += [
                'business_name' => ['required', 'string', 'max:255'],
                'rif' => ['required', 'string', 'max:20', 'unique:allies,rif'],
                'city' => ['required', 'string', 'max:255'],
                'address' => ['required', 'string', 'max:255'],
            ];
        }

        if ($this->role === User::ROLE_REPARTIDOR) {
            $rules += [
                'vehicle_plate' => ['required', 'string', 'max:20', 'unique:drivers,vehicle_plate'],
                'vehicle_type' => ['required', 'string', 'max:255'],
                'phone' => ['required', 'string', 'max:30'],
            ];
        }

        return $rules;
    }

    protected function resetForm(): void
    {
        $this->reset([
            'name', 'email', 'password', 'password_confirmation',
            'business_name', 'rif', 'city', 'address',
            'vehicle_plate', 'vehicle_type', 'phone',
            'adminPassword',
        ]);
        $this->role = User::ROLE_ALIADO;
        $this->resetValidation();
    }
}
