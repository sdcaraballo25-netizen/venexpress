<?php

namespace Tests\Unit;

use App\Models\Ally;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Antes, las URLs de documentos de Ally/Driver ("/aliados/{ally}/...",
 * "/repartidores/{driver}/...") exponían el id autoincremental de la
 * base de datos: un admin podía enumerar agencias/repartidores solo
 * incrementando el número en la URL. HasPublicId hace que el
 * route-model binding use un UUID en vez de ese id.
 */
class HasPublicIdTest extends TestCase
{
    use CreatesTestPackages;
    use RefreshDatabase;

    public function test_new_users_get_a_unique_public_id(): void
    {
        $userOne = User::factory()->create();
        $userTwo = User::factory()->create();

        $this->assertNotNull($userOne->public_id);
        $this->assertNotNull($userTwo->public_id);
        $this->assertNotSame($userOne->public_id, $userTwo->public_id);
    }

    public function test_new_allies_get_a_unique_public_id(): void
    {
        $ally = $this->createAlly();

        $this->assertNotNull($ally->public_id);
    }

    public function test_new_drivers_get_a_unique_public_id(): void
    {
        $driver = Driver::factory()->create();

        $this->assertNotNull($driver->public_id);
    }

    public function test_ally_route_key_is_the_public_id_not_the_numeric_id(): void
    {
        $ally = $this->createAlly();

        $this->assertSame('public_id', $ally->getRouteKeyName());
        $this->assertSame($ally->public_id, $ally->getRouteKey());

        $url = route('allies.documents.rif', $ally);

        $this->assertStringContainsString($ally->public_id, $url);
        $this->assertStringNotContainsString('/aliados/'.$ally->id.'/', $url);
    }

    public function test_driver_route_key_is_the_public_id_not_the_numeric_id(): void
    {
        $driver = Driver::factory()->create();

        $this->assertSame('public_id', $driver->getRouteKeyName());

        $url = route('drivers.documents.license', $driver);

        $this->assertStringContainsString($driver->public_id, $url);
        $this->assertStringNotContainsString('/repartidores/'.$driver->id.'/', $url);
    }
}
