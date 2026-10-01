<?php

namespace Tests\Feature\Driver;

use App\Livewire\Admin\UsersManager;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Un token de la app del repartidor emitido mientras la cuenta podía
 * operar no debe seguir sirviendo cuando deja de poder hacerlo, y los
 * tokens deben vencer.
 */
class DriverApiAccountStatusTest extends TestCase
{
    use RefreshDatabase;

    private function createDriverUser(): array
    {
        $user = User::factory()->create([
            'role' => User::ROLE_REPARTIDOR,
            'status' => User::STATUS_ACTIVE,
            'password' => bcrypt('password-seguro'),
        ]);

        $driver = Driver::factory()->create([
            'user_id' => $user->id,
            'status' => Driver::STATUS_ACTIVE,
        ]);

        return [$user, $driver];
    }

    private function loginToken(User $user): string
    {
        return $this->postJson('/api/driver/login', [
            'email' => $user->email,
            'password' => 'password-seguro',
            'device_name' => 'telefono-de-prueba',
        ])->assertOk()->json('token');
    }

    public function test_an_existing_token_stops_working_once_the_driver_can_no_longer_operate(): void
    {
        [$user, $driver] = $this->createDriverUser();

        $headers = ['Authorization' => 'Bearer '.$this->loginToken($user)];

        $this->getJson('/api/driver/dashboard', $headers)->assertOk();

        // Suspendido directamente en la tabla (sin pasar por el panel
        // que además revoca tokens): el middleware debe bloquearlo igual.
        $driver->update(['status' => Driver::STATUS_SUSPENDED]);
        Auth::forgetGuards();

        $this->getJson('/api/driver/dashboard', $headers)->assertForbidden();
    }

    public function test_deactivating_the_user_revokes_their_tokens(): void
    {
        [$user] = $this->createDriverUser();
        $this->loginToken($user);

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN_PRINCIPAL, 'status' => User::STATUS_ACTIVE]);

        Livewire::actingAs($admin)
            ->test(UsersManager::class)
            ->call('toggleStatus', $user->id);

        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_tokens_expire(): void
    {
        $this->assertNotNull(config('sanctum.expiration'));

        [$user] = $this->createDriverUser();

        $headers = ['Authorization' => 'Bearer '.$this->loginToken($user)];

        $this->travel((int) config('sanctum.expiration') + 1)->minutes();
        Auth::forgetGuards();

        $this->getJson('/api/driver/me', $headers)->assertUnauthorized();
    }
}
