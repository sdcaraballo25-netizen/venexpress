<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\UsersManager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * $search usa #[Url] para poder llegar aquí con un enlace directo
 * (ej. /admin/users?search=correo@ejemplo.com desde la Bitácora de
 * auditoría, para revisar los datos de un usuario puntual).
 */
class UsersManagerSearchUrlTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_ADMIN_PRINCIPAL,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);
    }

    public function test_search_query_string_pre_filters_the_user_list(): void
    {
        $target = User::factory()->create(['name' => 'Objetivo de la búsqueda']);
        $other = User::factory()->create(['name' => 'Otro usuario cualquiera']);

        Livewire::withQueryParams(['search' => $target->email])
            ->actingAs($this->createAdmin())
            ->test(UsersManager::class)
            ->assertSet('search', $target->email)
            ->assertSee('Objetivo de la búsqueda')
            ->assertDontSee('Otro usuario cualquiera');
    }
}
