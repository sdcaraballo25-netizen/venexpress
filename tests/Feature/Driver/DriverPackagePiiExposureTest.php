<?php

namespace Tests\Feature\Driver;

use App\Models\Driver;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * DriverPackageController::lookup()/scan()/hubReception() resolvían
 * el paquete solo por tracking_number, sin verificar que le tocara al
 * repartidor autenticado (ni por ruta activa ni por ya ser suyo).
 * Como el número de guía es adivinable/enumerable, cualquier
 * repartidor podía leer nombre, cédula y teléfono del remitente y
 * destinatario, y el monto COD, de guías de otras agencias/rutas.
 */
class DriverPackagePiiExposureTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestPackages;

    private function createDriverUser(): array
    {
        $user = User::factory()->create([
            'role' => User::ROLE_REPARTIDOR,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
            'password' => bcrypt('password-seguro'),
        ]);

        $driver = Driver::factory()->create([
            'user_id' => $user->id,
            'status' => Driver::STATUS_ACTIVE,
        ]);

        return [$user, $driver];
    }

    private function authHeaders(User $user): array
    {
        $token = $this->postJson('/api/driver/login', [
            'email' => $user->email,
            'password' => 'password-seguro',
            'device_name' => 'telefono-de-prueba',
        ])->assertOk()->json('token');

        return ['Authorization' => "Bearer {$token}"];
    }

    public function test_lookup_does_not_disclose_a_package_outside_the_drivers_route(): void
    {
        [$user, $driver] = $this->createDriverUser();
        $headers = $this->authHeaders($user);

        // El repartidor tiene una ruta activa, pero para OTRA agencia
        // distinta a la del paquete que va a intentar consultar.
        $myAlly = $this->createAlly();
        $route = Route::create([
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
            'name' => 'Ruta de prueba',
            'driver_id' => $driver->id,
            'created_by' => $user->id,
            'status' => Route::STATUS_IN_PROGRESS,
            'route_type' => Route::TYPE_DELIVERY,
            'started_at' => now(),
        ]);
        RouteStop::create([
            'route_id' => $route->id,
            'ally_id' => $myAlly->id,
            'sequence' => 1,
            'status' => RouteStop::STATUS_PENDING,
        ]);

        $otherAlly = $this->createAlly();
        $foreignPackage = $this->createPackage($otherAlly, [
            'sender_name' => 'Remitente Secreto',
            'recipient_name' => 'Destinatario Secreto',
            'recipient_phone' => '0424-0000000',
            'is_cod' => true,
            'cod_amount_usd' => 250.00,
        ]);

        $response = $this->getJson(
            '/api/driver/packages/lookup?tracking_number=' . $foreignPackage->tracking_number,
            $headers
        );

        $response->assertNotFound();
        $response->assertJsonMissing(['package' => []]);
        $this->assertStringNotContainsString('Remitente Secreto', $response->getContent());
        $this->assertStringNotContainsString('Destinatario Secreto', $response->getContent());
        $this->assertStringNotContainsString('250', $response->getContent());
    }

    public function test_scan_error_response_does_not_disclose_a_package_outside_the_drivers_route(): void
    {
        [$user, $driver] = $this->createDriverUser();
        $headers = $this->authHeaders($user);

        // Sin ninguna ruta activa: el escaneo va a fallar, pero antes
        // no debía filtrar los datos del paquete en la respuesta 422.
        $otherAlly = $this->createAlly();
        $foreignPackage = $this->createPackage($otherAlly, [
            'sender_name' => 'Remitente Secreto',
            'recipient_name' => 'Destinatario Secreto',
            'is_cod' => true,
            'cod_amount_usd' => 250.00,
        ]);

        $response = $this->postJson('/api/driver/scan', [
            'tracking_number' => $foreignPackage->tracking_number,
        ], $headers);

        $response->assertStatus(422);
        $this->assertStringNotContainsString('Remitente Secreto', $response->getContent());
        $this->assertStringNotContainsString('Destinatario Secreto', $response->getContent());
        $this->assertStringNotContainsString('250', $response->getContent());
    }

    /*
    |--------------------------------------------------------------------------
    | Cédula/RIF: nunca llega al repartidor, ni de sus propios paquetes
    |--------------------------------------------------------------------------
    */

    private const SENDER_DOC = 'V-11222333';

    private const RECIPIENT_DOC = 'J-44555666-7';

    private function ownPackage(Driver $driver): \App\Models\Package
    {
        return $this->createPackage($this->createAlly(), [
            'driver_id' => $driver->id,
            'sender_id_doc' => self::SENDER_DOC,
            'recipient_id_doc' => self::RECIPIENT_DOC,
            'requires_delivery' => true,
            'delivery_address' => 'Av. Bolívar, casa 10',
            'delivery_reference' => 'Frente a la plaza',
            'current_status' => \App\Models\Package::STATUS_EN_TRANSITO_NACIONAL,
        ]);
    }

    public function test_api_detail_and_list_never_include_the_cedula_or_rif(): void
    {
        [$user, $driver] = $this->createDriverUser();
        $package = $this->ownPackage($driver);
        $headers = $this->authHeaders($user);

        $detail = $this->getJson("/api/driver/packages/{$package->id}", $headers)
            ->assertOk()
            // Lo que sí necesita para entregar.
            ->assertJsonPath('package.recipient.name', 'María Gómez')
            ->assertJsonPath('package.recipient.phone', '0424-7654321')
            ->assertJsonPath('package.delivery_address', 'Av. Bolívar, casa 10')
            ->assertJsonPath('package.delivery_reference', 'Frente a la plaza')
            ->assertJsonMissingPath('package.recipient.id_doc')
            ->assertJsonMissingPath('package.sender.id_doc');

        $this->assertStringNotContainsString(self::SENDER_DOC, $detail->getContent());
        $this->assertStringNotContainsString(self::RECIPIENT_DOC, $detail->getContent());

        $list = $this->getJson('/api/driver/packages', $headers)->assertOk();
        $this->assertStringNotContainsString(self::SENDER_DOC, $list->getContent());
        $this->assertStringNotContainsString(self::RECIPIENT_DOC, $list->getContent());

        $lookup = $this->getJson('/api/driver/packages/lookup?tracking_number='.$package->tracking_number, $headers)
            ->assertOk();
        $this->assertStringNotContainsString(self::RECIPIENT_DOC, $lookup->getContent());
    }

    public function test_web_package_detail_does_not_show_the_cedula_or_rif(): void
    {
        [$user, $driver] = $this->createDriverUser();
        $package = $this->ownPackage($driver);

        \Livewire\Livewire::actingAs($user)
            ->test(\App\Livewire\Driver\PackageDetail::class, ['packageId' => $package->id])
            ->assertSee('María Gómez')
            ->assertSee('0424-7654321')
            ->assertSee('Av. Bolívar, casa 10')
            ->assertDontSee(self::SENDER_DOC)
            ->assertDontSee(self::RECIPIENT_DOC);
    }

    public function test_label_pdf_hides_the_cedula_or_rif_for_the_driver_only(): void
    {
        [$user, $driver] = $this->createDriverUser();
        $package = $this->ownPackage($driver);

        \Barryvdh\DomPDF\Facade\Pdf::shouldReceive('loadView')
            ->once()
            ->withArgs(fn ($view, $data) => $view === 'pdf.package-label' && $data['hideIdDocs'] === true)
            ->andReturnSelf();
        \Barryvdh\DomPDF\Facade\Pdf::shouldReceive('setPaper')->andReturnSelf();
        \Barryvdh\DomPDF\Facade\Pdf::shouldReceive('stream')->andReturn(new \Illuminate\Http\Response('pdf'));

        $this->actingAs($user)
            ->get(route('packages.label', $package))
            ->assertOk();

        $data = ['package' => $package, 'barcodeSvg' => '', 'qrDataUri' => ''];

        $forDriver = view('pdf.package-label', $data + ['hideIdDocs' => true])->render();
        $this->assertStringNotContainsString(self::SENDER_DOC, $forDriver);
        $this->assertStringNotContainsString(self::RECIPIENT_DOC, $forDriver);
        $this->assertStringContainsString('0424-7654321', $forDriver);

        // Admin/Aliado (hideIdDocs falso o ausente) la siguen viendo.
        $forStaff = view('pdf.package-label', $data)->render();
        $this->assertStringContainsString(self::SENDER_DOC, $forStaff);
        $this->assertStringContainsString(self::RECIPIENT_DOC, $forStaff);
    }

    public function test_the_ally_still_sees_the_cedula_or_rif_of_its_packages(): void
    {
        $ally = $this->createAlly();
        $package = $this->createPackage($ally, [
            'sender_id_doc' => self::SENDER_DOC,
            'recipient_id_doc' => self::RECIPIENT_DOC,
        ]);

        \Livewire\Livewire::actingAs($ally->user)
            ->test(\App\Livewire\Ally\PackageDetail::class, ['packageId' => $package->id])
            ->assertSee(self::SENDER_DOC)
            ->assertSee(self::RECIPIENT_DOC);
    }
}
