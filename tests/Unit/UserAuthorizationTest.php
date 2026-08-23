<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

class UserAuthorizationTest extends TestCase
{
    public function test_principal_can_create_every_supported_role(): void
    {
        $user = User::factory()->make(['role' => User::ROLE_ADMIN_PRINCIPAL, 'status' => User::STATUS_ACTIVE]);

        $this->assertTrue($user->canCreateRole(User::ROLE_ADMIN_PRINCIPAL));
        $this->assertTrue($user->canCreateRole(User::ROLE_ADMIN_OPERATIVO));
        $this->assertTrue($user->canCreateRole(User::ROLE_ALIADO));
        $this->assertTrue($user->canCreateRole(User::ROLE_REPARTIDOR));
        $this->assertTrue($user->canCreateRole(User::ROLE_CLIENTE));
    }

    public function test_operativo_cannot_create_principal_or_manage_admins(): void
    {
        $operativo = User::factory()->make(['role' => User::ROLE_ADMIN_OPERATIVO, 'status' => User::STATUS_ACTIVE]);
        $principal = User::factory()->make(['role' => User::ROLE_ADMIN_PRINCIPAL, 'status' => User::STATUS_ACTIVE]);
        $otherOperativo = User::factory()->make(['role' => User::ROLE_ADMIN_OPERATIVO, 'status' => User::STATUS_ACTIVE]);

        $this->assertFalse($operativo->canCreateRole(User::ROLE_ADMIN_PRINCIPAL));
        $this->assertFalse($operativo->canEditUser($principal));
        $this->assertFalse($operativo->canDeactivateUser($principal));
        $this->assertFalse($operativo->canEditUser($otherOperativo));
        $this->assertFalse($operativo->canDeactivateUser($otherOperativo));
        $this->assertFalse($operativo->canDeleteUser($otherOperativo));
    }

    public function test_admin_cannot_deactivate_or_delete_itself(): void
    {
        $principal = User::factory()->make(['role' => User::ROLE_ADMIN_PRINCIPAL, 'status' => User::STATUS_ACTIVE]);

        $this->assertFalse($principal->canDeactivateUser($principal));
        $this->assertFalse($principal->canDeleteUser($principal));
    }
}
