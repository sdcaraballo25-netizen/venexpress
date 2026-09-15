<?php

namespace Tests\Feature\Driver;

use App\Models\Driver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * La descarga del APK de repartidor NUNCA debe ser pública: exponerla
 * permitiría a cualquiera bajar la app y explorar la superficie de
 * la API del driver sin ser un repartidor real.
 */
class AppDownloadAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('repartidor.app-download'))
            ->assertRedirect(route('login'));
    }

    public function test_other_roles_cannot_access_it(): void
    {
        $ally = User::factory()->create([
            'role' => User::ROLE_ALIADO,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($ally)
            ->get(route('repartidor.app-download'))
            ->assertForbidden();
    }

    public function test_an_approved_driver_can_access_it(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_REPARTIDOR,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        Driver::factory()->create([
            'user_id' => $user->id,
            'status' => Driver::STATUS_ACTIVE,
        ]);

        $this->actingAs($user)
            ->get(route('repartidor.app-download'))
            ->assertOk();
    }

    public function test_public_app_download_route_no_longer_exists(): void
    {
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('public.app-download'));
    }
}
