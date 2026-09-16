<?php

namespace App\Livewire\Admin;

use App\Models\Ally;
use App\Models\AuditLog;
use App\Models\Driver;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\VenezuelaLocationService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Gestión de Usuarios')]
class UsersManager extends Component
{
    use WithPagination;

    /**
     * #[Url]: permite llegar aquí con un enlace directo tipo
     * /admin/users?search=correo@ejemplo.com (ej. desde la Bitácora de
     * auditoría, para revisar los datos de un usuario puntual) sin
     * cambiar el comportamiento normal de la búsqueda en pantalla.
     */
    #[Url]
    public string $search = '';

    public string $roleFilter = '';

    public string $statusFilter = '';

    public bool $showCreateModal = false;

    public bool $showConfirmModal = false;

    public bool $showDeleteModal = false;

    public bool $showEditModal = false;

    public ?int $editingUserId = null;

    public string $edit_name = '';

    public string $edit_email = '';

    public string $edit_password = '';

    public string $edit_password_confirmation = '';

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $role = User::ROLE_ALIADO;

    public string $business_name = '';

    public string $rif = '';

    public string $state = '';

    public string $city = '';

    public string $address = '';

    public array $states = [];

    public array $cities = [];

    public string $vehicle_plate = '';

    public string $vehicle_type = '';

    public string $phone = '';

    public string $driver_type = Driver::TYPE_DELIVERY;

    public ?int $warehouse_id = null;

    public bool $editIsDriver = false;

    public string $edit_driver_type = '';

    public string $edit_vehicle_plate = '';

    public string $edit_vehicle_type = '';

    public string $edit_phone = '';

    public string $adminPassword = '';

    public ?int $pendingUserId = null;

    public function mount(VenezuelaLocationService $locationService): void
    {
        $this->states = $locationService->states();
    }

    public function updatedState(
        VenezuelaLocationService $locationService
    ): void {
        $this->city = '';

        $this->cities = $this->state !== ''
            ? $locationService->citiesByState($this->state)
            : [];
    }

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
        /** @var User|null $actor */
        $actor = Auth::user();

        abort_unless($actor?->canManageUsers(), 403);

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
        /** @var User|null $actor */
        $actor = Auth::user();

        abort_unless($actor?->canManageUsers(), 403);

        $this->validate($this->creationRules());

        if (! $actor?->canCreateRole($this->role)) {
            $this->addError(
                'role',
                'No tienes permiso para crear este tipo de usuario.'
            );

            return;
        }

        $this->showConfirmModal = true;
        $this->adminPassword = '';
    }

    public function createUser(): void
    {
        /** @var User|null $actor */
        $actor = Auth::user();

        abort_unless($actor?->canManageUsers(), 403);

        $this->validate($this->creationRules());

        if (! $actor?->canCreateRole($this->role)) {
            $this->addError(
                'role',
                'No tienes permiso para crear este tipo de usuario.'
            );

            $this->showConfirmModal = false;

            return;
        }

        $this->validate([
            'adminPassword' => ['required', 'string'],
        ], [
            'adminPassword.required' => 'Debes introducir tu contraseña de administrador.',
        ]);

        if (! Hash::check($this->adminPassword, $actor->password)) {
            $this->addError(
                'adminPassword',
                'La contraseña de administrador no es correcta.'
            );

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
                    'state' => $validated['state'],
                    'city' => $validated['city'],
                    'address' => $validated['address'],
                    'commission_percentage' => 10.00,
                    'status' => Ally::STATUS_ACTIVE,
                ]);
            }

            if ($user->isRepartidor()) {
                $isHub = $validated['driver_type'] === Driver::TYPE_HUB;

                Driver::create([
                    'user_id' => $user->id,
                    'vehicle_plate' => $validated['vehicle_plate'],
                    'vehicle_type' => $isHub
                        ? Driver::HUB_VEHICLE_TYPE
                        : $validated['vehicle_type'],
                    'phone' => $validated['phone'],
                    'status' => Driver::STATUS_ACTIVE,
                    'driver_type' => $validated['driver_type'],
                ]);
            }

            if ($user->isAlmacen()) {
                $user->update([
                    'warehouse_id' => $validated['warehouse_id'],
                ]);
            }

            AuditLog::create([
                'actor_user_id' => $actor->id,
                'action' => 'user.created',
                'target_type' => User::class,
                'target_id' => $user->id,
                'description' => "Creó al usuario {$user->name} con rol {$user->role}.",
                'metadata' => [
                    'role' => $user->role,
                    'email' => $user->email,
                ],
                'ip_address' => request()->ip(),
            ]);
        });

        $this->closeCreateModal();

        session()->flash(
            'success',
            'Usuario creado correctamente.'
        );

        $this->resetPage();
    }

    /**
     * Abre el modal de edición con los datos actuales del usuario.
     */
    public function openEditModal(int $userId): void
    {
        $target = User::findOrFail($userId);

        /** @var User|null $actor */
        $actor = Auth::user();

        abort_unless(
            $actor?->canEditUser($target),
            403
        );

        $this->editingUserId = $target->id;
        $this->edit_name = $target->name;
        $this->edit_email = $target->email;
        $this->edit_password = '';
        $this->edit_password_confirmation = '';

        $this->editIsDriver = $target->isRepartidor() && $target->driver !== null;

        if ($this->editIsDriver) {
            $this->edit_driver_type = $target->driver->driver_type;
            $this->edit_vehicle_plate = $target->driver->vehicle_plate;
            $this->edit_vehicle_type = $target->driver->vehicle_type;
            $this->edit_phone = $target->driver->phone;
        }

        $this->resetValidation();

        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editingUserId = null;
        $this->editIsDriver = false;

        $this->reset([
            'edit_name',
            'edit_email',
            'edit_password',
            'edit_password_confirmation',
            'edit_driver_type',
            'edit_vehicle_plate',
            'edit_vehicle_type',
            'edit_phone',
        ]);

        $this->resetValidation();
    }

    /**
     * Actualiza nombre, correo y opcionalmente la contraseña.
     */
    public function updateUser(): void
    {
        /** @var User|null $actor */
        $actor = Auth::user();

        $target = $this->editingUserId
            ? User::find($this->editingUserId)
            : null;

        abort_unless(
            $target && $actor?->canEditUser($target),
            403
        );

        // Regla explícita: solo se tocan datos de Driver si el usuario
        // objetivo es repartidor y ya tiene un registro Driver asociado.
        $isTargetDriver = $target->isRepartidor() && $target->driver !== null;

        $rules = [
            'edit_name' => [
                'required',
                'string',
                'max:255',
            ],
            'edit_email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email,'.$target->id,
            ],
        ];

        if ($this->edit_password !== '') {
            $rules['edit_password'] = [
                'string',
                'confirmed',
                Rules\Password::defaults(),
            ];
        }

        if ($isTargetDriver) {
            $rules += [
                'edit_driver_type' => [
                    'required',
                    'string',
                    'in:'.implode(',', [Driver::TYPE_HUB, Driver::TYPE_DELIVERY]),
                ],

                'edit_vehicle_plate' => [
                    'required',
                    'string',
                    'max:20',
                    'unique:drivers,vehicle_plate,'.$target->driver->id,
                ],

                'edit_phone' => [
                    'required',
                    'string',
                    'max:30',
                ],
            ];

            // Un driver HUB no maneja un vehículo particular: el campo
            // no aplica y se sobreescribe siempre con
            // Driver::HUB_VEHICLE_TYPE al guardar.
            if ($this->edit_driver_type !== Driver::TYPE_HUB) {
                $rules['edit_vehicle_type'] = [
                    'required',
                    'string',
                    'max:255',
                ];
            }
        }

        $validated = $this->validate(
            $rules,
            [],
            [
                'edit_name' => 'nombre',
                'edit_email' => 'correo electrónico',
                'edit_password' => 'contraseña',
                'edit_driver_type' => 'tipo de repartidor',
                'edit_vehicle_plate' => 'placa',
                'edit_vehicle_type' => 'tipo de vehículo',
                'edit_phone' => 'teléfono',
            ]
        );

        $data = [
            'name' => $validated['edit_name'],
            'email' => strtolower($validated['edit_email']),
        ];

        if ($this->edit_password !== '') {
            $data['password'] = $validated['edit_password'];
        }

        $driverData = null;
        $previousDriverType = null;

        if ($isTargetDriver) {
            $previousDriverType = $target->driver->driver_type;
            $isHub = $validated['edit_driver_type'] === Driver::TYPE_HUB;

            $driverData = [
                'driver_type' => $validated['edit_driver_type'],
                'vehicle_plate' => $validated['edit_vehicle_plate'],
                'vehicle_type' => $isHub
                    ? Driver::HUB_VEHICLE_TYPE
                    : $validated['edit_vehicle_type'],
                'phone' => $validated['edit_phone'],
            ];
        }

        DB::transaction(function () use ($actor, $target, $data, $driverData, $previousDriverType) {
            $target->update($data);

            $metadata = [
                'fields' => array_keys($data),
            ];

            if ($driverData !== null) {
                $target->driver->update($driverData);

                if ($previousDriverType !== $driverData['driver_type']) {
                    $metadata['driver_type'] = [
                        'from' => $previousDriverType,
                        'to' => $driverData['driver_type'],
                    ];
                }
            }

            AuditLog::create([
                'actor_user_id' => $actor->id,
                'action' => 'user.updated',
                'target_type' => User::class,
                'target_id' => $target->id,
                'description' => "Editó los datos del usuario {$target->name}."
                    .(isset($data['password'])
                        ? ' Se restableció su contraseña.'
                        : ''),
                'metadata' => $metadata,
                'ip_address' => request()->ip(),
            ]);
        });

        $this->closeEditModal();

        session()->flash(
            'success',
            'Usuario actualizado correctamente.'
        );
    }

    public function toggleStatus(int $userId): void
    {
        /** @var User|null $actor */
        $actor = Auth::user();

        $target = User::findOrFail($userId);

        abort_unless(
            $actor?->canDeactivateUser($target),
            403
        );

        $newStatus = $target->isActive()
            ? User::STATUS_INACTIVE
            : User::STATUS_ACTIVE;

        DB::transaction(function () use ($target, $newStatus) {
            $target->update([
                'status' => $newStatus,
            ]);

            if ($target->isRepartidor()) {
                $target->driver?->update([
                    'status' => $newStatus === User::STATUS_ACTIVE
                            ? Driver::STATUS_ACTIVE
                            : Driver::STATUS_SUSPENDED,
                ]);
            }
        });

        AuditLog::create([
            'actor_user_id' => $actor->id,
            'action' => 'user.status_changed',
            'target_type' => User::class,
            'target_id' => $target->id,
            'description' => "Cambió el estado de {$target->name} a {$newStatus}.",
            'metadata' => [
                'status' => $newStatus,
            ],
            'ip_address' => request()->ip(),
        ]);

        session()->flash(
            'success',
            $newStatus === User::STATUS_ACTIVE
                ? 'Usuario activado correctamente.'
                : 'Usuario desactivado correctamente.'
        );
    }

    public function requestDelete(int $userId): void
    {
        /** @var User|null $actor */
        $actor = Auth::user();

        $target = User::findOrFail($userId);

        abort_unless(
            $actor?->canDeleteUser($target),
            403
        );

        if ($target->hasOperationalHistory()) {
            session()->flash(
                'error',
                "No puedes eliminar a {$target->name}: tiene guías "
                .'o pagos registrados en el sistema. '
                .'Desactiva la cuenta en su lugar para conservar el historial.'
            );

            return;
        }

        $this->pendingUserId = $target->id;
        $this->adminPassword = '';
        $this->showDeleteModal = true;
    }

    public function deleteUser(): void
    {
        /** @var User|null $actor */
        $actor = Auth::user();

        $target = $this->pendingUserId
            ? User::find($this->pendingUserId)
            : null;

        abort_unless(
            $target && $actor?->canDeleteUser($target),
            403
        );

        $this->validate([
            'adminPassword' => [
                'required',
                'string',
            ],
        ], [
            'adminPassword.required' => 'Debes introducir tu contraseña de administrador.',
        ]);

        if (! Hash::check($this->adminPassword, $actor->password)) {
            $this->addError(
                'adminPassword',
                'La contraseña de administrador no es correcta.'
            );

            return;
        }

        if ($target->hasOperationalHistory()) {
            $this->showDeleteModal = false;
            $this->pendingUserId = null;
            $this->adminPassword = '';

            session()->flash(
                'error',
                "No puedes eliminar a {$target->name}: tiene guías "
                .'o pagos registrados en el sistema. '
                .'Desactiva la cuenta en su lugar para conservar el historial.'
            );

            return;
        }

        try {
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
                    'metadata' => [
                        'role' => $role,
                    ],
                    'ip_address' => request()->ip(),
                ]);
            });
        } catch (QueryException $e) {
            $this->showDeleteModal = false;
            $this->pendingUserId = null;
            $this->adminPassword = '';

            session()->flash(
                'error',
                "No se pudo eliminar a {$target->name} porque todavía "
                .'tiene registros asociados en el sistema.'
            );

            return;
        }

        $this->showDeleteModal = false;
        $this->pendingUserId = null;
        $this->adminPassword = '';

        session()->flash(
            'success',
            'Usuario eliminado correctamente.'
        );
    }

    public function render()
    {
        $users = User::query()
            ->when(
                $this->search !== '',
                function ($query) {
                    $term = '%'.$this->search.'%';

                    $query->where(function ($q) use ($term) {
                        $q->where(
                            'name',
                            'like',
                            $term
                        )->orWhere(
                            'email',
                            'like',
                            $term
                        );
                    });
                }
            )
            ->when(
                $this->roleFilter !== '',
                fn ($query) => $query->where(
                    'role',
                    $this->roleFilter
                )
            )
            ->when(
                $this->statusFilter !== '',
                fn ($query) => $query->where(
                    'status',
                    $this->statusFilter
                )
            )
            ->latest()
            ->paginate(12);

        return view(
            'livewire.admin.users-manager',
            [
                'users' => $users,
                'roleLabels' => User::roleLabels(),
                'warehouses' => Warehouse::query()->where('is_active', true)->orderBy('name')->get(),
            ]
        );
    }

    protected function creationRules(): array
    {
        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'string',
                'confirmed',
                Rules\Password::defaults(),
            ],

            'role' => [
                'required',
                'string',
                'in:'.implode(
                    ',',
                    array_keys(User::roleLabels())
                ),
            ],
        ];

        if ($this->role === User::ROLE_ALIADO) {
            $rules += [
                'business_name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'rif' => [
                    'required',
                    'string',
                    'max:20',
                    'unique:allies,rif',
                ],

                'state' => [
                    'required',
                    'string',
                ],

                'city' => [
                    'required',
                    'string',
                ],

                'address' => [
                    'required',
                    'string',
                    'max:255',
                ],
            ];
        }

        if ($this->role === User::ROLE_REPARTIDOR) {
            $rules += [
                'driver_type' => [
                    'required',
                    'string',
                    'in:'.implode(',', [Driver::TYPE_HUB, Driver::TYPE_DELIVERY]),
                ],

                'vehicle_plate' => [
                    'required',
                    'string',
                    'max:20',
                    'unique:drivers,vehicle_plate',
                ],

                'phone' => [
                    'required',
                    'string',
                    'max:30',
                ],
            ];

            // Un driver HUB no maneja un vehículo particular: el campo
            // no aplica y se sobreescribe siempre con
            // Driver::HUB_VEHICLE_TYPE al guardar.
            if ($this->driver_type !== Driver::TYPE_HUB) {
                $rules['vehicle_type'] = [
                    'required',
                    'string',
                    'max:255',
                ];
            }
        }

        if ($this->role === User::ROLE_ALMACEN) {
            $rules += [
                'warehouse_id' => [
                    'required',
                    'integer',
                    'exists:warehouses,id',
                ],
            ];
        }

        return $rules;
    }

    protected function resetForm(): void
    {
        $this->reset([
            'name',
            'email',
            'password',
            'password_confirmation',
            'business_name',
            'rif',
            'state',
            'city',
            'address',
            'vehicle_plate',
            'vehicle_type',
            'phone',
            'driver_type',
            'warehouse_id',
            'adminPassword',
        ]);

        $this->cities = [];

        $this->role = User::ROLE_ALIADO;

        $this->resetValidation();
    }
}
