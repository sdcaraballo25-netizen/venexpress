<?php

namespace Tests\Feature;

use App\Livewire\Admin\AlliesManager;
use App\Livewire\Admin\DriversApprovalManager;
use App\Livewire\Admin\EmprendedoresApprovalManager;
use App\Livewire\Ally\Verificacion as AllyVerificacion;
use App\Livewire\Driver\Verificacion as DriverVerificacion;
use App\Livewire\Emprendedor\Verificacion as EmprendedorVerificacion;
use App\Models\Ally;
use App\Models\Driver;
use App\Models\Emprendedor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Fase 5: prueba de extremo a extremo del ciclo completo
 *
 *   registro (PENDIENTE) -> completar verificación (EN_REVISION)
 *   -> Admin rechaza con motivo (RECHAZADO) -> account-pending muestra
 *   el motivo -> el usuario corrige y reenvía desde la MISMA pantalla
 *   "Mi Verificación" (EN_REVISION, motivo limpio) -> Admin aprueba
 *   (VERIFICADO + ACTIVO) -> el usuario ya puede operar (acceder a su
 *   dashboard real, antes bloqueado por EnsureAccountIsApproved).
 *
 * No se agregó ningún componente nuevo para esto: la pantalla de
 * Fase 4 ya es la misma que usa el flujo de corrección/resubmisión
 * (ver Driver/Ally/Emprendedor\Verificacion — editable en PENDIENTE
 * y en RECHAZADO). Este test demuestra que el ciclo completo
 * funciona de punta a punta a través de los componentes reales de
 * Admin y de usuario, no solo con datos sembrados a mano.
 */
class VerificationLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_ADMIN_PRINCIPAL,
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    public function test_driver_full_reject_correct_resubmit_approve_cycle(): void
    {
        Storage::fake('documents');
        Notification::fake();

        $admin = $this->createAdmin();

        $driverUser = User::factory()->create([
            'role' => User::ROLE_REPARTIDOR,
            'status' => User::STATUS_ACTIVE,
        ]);

        $driver = Driver::factory()->create([
            'user_id' => $driverUser->id,
            'status' => Driver::STATUS_PENDING,
            'verification_status' => Driver::VERIFICATION_PENDING,
            'vehicle_plate' => 'OLD-000',
        ]);

        // 1. Todavía no puede operar: el middleware lo manda a account-pending.
        $this->actingAs($driverUser)
            ->get(route('repartidor.dashboard'))
            ->assertRedirect(route('account.pending'));

        // 2. Completa su verificación por primera vez (Fase 4).
        Livewire::actingAs($driverUser)
            ->test(DriverVerificacion::class)
            ->set('cedula', 'V-11111111')
            ->set('city', 'Caracas')
            ->set('state', 'Distrito Capital')
            ->set('vehicle_plate', 'ABC-123')
            ->set('vehicle_type', 'Moto')
            ->set('id_photo', UploadedFile::fake()->image('cedula-frente.jpg'))
            ->set('cedula_back_photo', UploadedFile::fake()->image('cedula-reverso.jpg'))
            ->set('selfie_photo', UploadedFile::fake()->image('selfie.jpg'))
            ->set('license_photo', UploadedFile::fake()->image('licencia.jpg'))
            ->set('vehicle_photo', UploadedFile::fake()->image('vehiculo.jpg'))
            ->set('plate_photo', UploadedFile::fake()->image('placa.jpg'))
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertSame(Driver::VERIFICATION_IN_REVIEW, $driver->fresh()->verification_status);

        // 3. Admin (componente real) rechaza con motivo obligatorio.
        Livewire::actingAs($admin)
            ->test(DriversApprovalManager::class)
            ->call('openReject', $driver->id)
            ->set('rejectionReason', 'La foto de la placa no es legible.')
            ->call('reject')
            ->assertHasNoErrors();

        $driver->refresh();
        $this->assertSame(Driver::VERIFICATION_REJECTED, $driver->verification_status);
        $this->assertSame('La foto de la placa no es legible.', $driver->verification_rejection_reason);
        $this->assertSame(Driver::STATUS_PENDING, $driver->status);

        // 4. account-pending muestra el motivo y el CTA a "Mi Verificación".
        //    ->refresh() es necesario: actingAs() fija esta MISMA
        //    instancia de $driverUser para todas las requests
        //    siguientes, y Eloquent ya había cacheado su relación
        //    driver() desde el paso 1 — sin refrescar, seguiría viendo
        //    el estado de ANTES del reject.
        $this->actingAs($driverUser->refresh())
            ->get(route('account.pending'))
            ->assertOk()
            ->assertSee('Tu solicitud fue rechazada')
            ->assertSee('La foto de la placa no es legible.')
            ->assertSee(route('repartidor.verificacion'));

        // 5. El usuario corrige (reemplaza la foto de la placa) y reenvía
        //    desde la MISMA pantalla de Fase 4 — sin volver a subir el
        //    resto de documentos, que ya existían.
        Livewire::actingAs($driverUser->refresh())
            ->test(DriverVerificacion::class)
            ->set('plate_photo', UploadedFile::fake()->image('placa-nueva.jpg'))
            ->call('submit')
            ->assertHasNoErrors();

        $driver->refresh();
        $this->assertSame(Driver::VERIFICATION_IN_REVIEW, $driver->verification_status);
        $this->assertNull($driver->verification_rejection_reason);

        // 6. Admin aprueba.
        Livewire::actingAs($admin)
            ->test(DriversApprovalManager::class)
            ->call('approve', $driver->id);

        $driver->refresh();
        $this->assertSame(Driver::VERIFICATION_VERIFIED, $driver->verification_status);
        $this->assertSame(Driver::STATUS_ACTIVE, $driver->status);
        $this->assertTrue($driver->canOperate());

        // 7. Ya puede operar: accede a su dashboard real.
        $this->actingAs($driverUser->refresh())
            ->get(route('repartidor.dashboard'))
            ->assertOk();
    }

    public function test_ally_full_reject_correct_resubmit_approve_cycle(): void
    {
        Storage::fake('documents');
        Notification::fake();

        $admin = $this->createAdmin();

        $allyUser = User::factory()->create([
            'role' => User::ROLE_ALIADO,
            'status' => User::STATUS_ACTIVE,
        ]);

        $ally = Ally::create([
            'user_id' => $allyUser->id,
            'business_name' => 'Agencia Original',
            'rif' => 'J-11111111-1',
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
            'address' => 'Av. Principal',
            'commission_percentage' => 10,
            'status' => Ally::STATUS_PENDING,
            'verification_status' => Ally::VERIFICATION_PENDING,
        ]);

        $this->actingAs($allyUser)
            ->get(route('ally.dashboard'))
            ->assertRedirect(route('account.pending'));

        Livewire::actingAs($allyUser)
            ->test(AllyVerificacion::class)
            ->set('business_name', 'Agencia Verificada')
            ->set('rif', 'J-11111111-1')
            ->set('address', 'Av. Bolívar')
            ->set('city', 'Valencia')
            ->set('state', 'Carabobo')
            ->set('owner_id_document', UploadedFile::fake()->image('cedula-frente.jpg'))
            ->set('owner_id_back_document', UploadedFile::fake()->image('cedula-reverso.jpg'))
            ->set('rif_document', UploadedFile::fake()->image('rif.jpg'))
            ->set('storefront_photo', UploadedFile::fake()->image('fachada.jpg'))
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertSame(Ally::VERIFICATION_IN_REVIEW, $ally->fresh()->verification_status);

        Livewire::actingAs($admin)
            ->test(AlliesManager::class)
            ->call('openReject', $ally->id)
            ->set('rejectionReason', 'La foto de la fachada está borrosa.')
            ->call('reject')
            ->assertHasNoErrors();

        $ally->refresh();
        $this->assertSame(Ally::VERIFICATION_REJECTED, $ally->verification_status);
        $this->assertSame('La foto de la fachada está borrosa.', $ally->verification_rejection_reason);

        // ->refresh() en cada paso siguiente: actingAs() fija esta
        // MISMA instancia de $allyUser para todas las requests
        // siguientes, y su relación ally() ya quedó cacheada desde el
        // paso 1 — sin refrescar, seguiría viendo el estado anterior.
        $this->actingAs($allyUser->refresh())
            ->get(route('account.pending'))
            ->assertOk()
            ->assertSee('Tu solicitud fue rechazada')
            ->assertSee('La foto de la fachada está borrosa.')
            ->assertSee(route('ally.verificacion'));

        Livewire::actingAs($allyUser->refresh())
            ->test(AllyVerificacion::class)
            ->set('storefront_photo', UploadedFile::fake()->image('fachada-nueva.jpg'))
            ->call('submit')
            ->assertHasNoErrors();

        $ally->refresh();
        $this->assertSame(Ally::VERIFICATION_IN_REVIEW, $ally->verification_status);
        $this->assertNull($ally->verification_rejection_reason);

        Livewire::actingAs($admin)
            ->test(AlliesManager::class)
            ->call('approve', $ally->id);

        $ally->refresh();
        $this->assertTrue($ally->canOperate());

        $this->actingAs($allyUser->refresh())
            ->get(route('ally.dashboard'))
            ->assertOk();
    }

    public function test_emprendedor_full_reject_correct_resubmit_approve_cycle(): void
    {
        Storage::fake('documents');
        Notification::fake();

        $admin = $this->createAdmin();

        $pickupAllyUser = User::factory()->create(['role' => User::ROLE_ALIADO]);
        $pickupAlly = Ally::create([
            'user_id' => $pickupAllyUser->id,
            'business_name' => 'Agencia de Retiro',
            'rif' => 'J-22222222-2',
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
            'address' => 'Av. Principal',
            'commission_percentage' => 10,
            'status' => Ally::STATUS_ACTIVE,
            'verification_status' => Ally::VERIFICATION_VERIFIED,
        ]);

        $emprendedorUser = User::factory()->create([
            'role' => User::ROLE_EMPRENDEDOR,
            'status' => User::STATUS_ACTIVE,
        ]);

        $emprendedor = Emprendedor::create([
            'user_id' => $emprendedorUser->id,
            'pickup_ally_id' => $pickupAlly->id,
            'business_name' => 'Tienda Original',
            'document_id' => 'V-33333333',
            'status' => Emprendedor::STATUS_PENDING,
            'verification_status' => Emprendedor::VERIFICATION_PENDING,
        ]);

        $this->actingAs($emprendedorUser)
            ->get(route('emprendedor.dashboard'))
            ->assertRedirect(route('account.pending'));

        Livewire::actingAs($emprendedorUser)
            ->test(EmprendedorVerificacion::class)
            ->set('cedula', 'V-44444444')
            ->set('city', 'Caracas')
            ->set('state', 'Distrito Capital')
            ->set('business_name', 'Tienda Verificada')
            ->set('document_id', 'V-33333333')
            ->set('cedula_front_photo', UploadedFile::fake()->image('cedula-frente.jpg'))
            ->set('cedula_back_photo', UploadedFile::fake()->image('cedula-reverso.jpg'))
            ->set('rif_document', UploadedFile::fake()->image('rif.jpg'))
            ->set('product_or_workspace_photo', UploadedFile::fake()->image('productos.jpg'))
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertSame(Emprendedor::VERIFICATION_IN_REVIEW, $emprendedor->fresh()->verification_status);

        Livewire::actingAs($admin)
            ->test(EmprendedoresApprovalManager::class)
            ->call('openReject', $emprendedor->id)
            ->set('rejectionReason', 'La foto del RIF no es legible.')
            ->call('reject')
            ->assertHasNoErrors();

        $emprendedor->refresh();
        $this->assertSame(Emprendedor::VERIFICATION_REJECTED, $emprendedor->verification_status);
        $this->assertSame('La foto del RIF no es legible.', $emprendedor->verification_rejection_reason);

        // ->refresh() en cada paso siguiente: actingAs() fija esta
        // MISMA instancia de $emprendedorUser para todas las requests
        // siguientes, y su relación emprendedor() ya quedó cacheada
        // desde el paso 1 — sin refrescar, seguiría viendo el estado
        // anterior.
        $this->actingAs($emprendedorUser->refresh())
            ->get(route('account.pending'))
            ->assertOk()
            ->assertSee('Tu solicitud fue rechazada')
            ->assertSee('La foto del RIF no es legible.')
            ->assertSee(route('emprendedor.verificacion'));

        Livewire::actingAs($emprendedorUser->refresh())
            ->test(EmprendedorVerificacion::class)
            ->set('rif_document', UploadedFile::fake()->image('rif-nuevo.jpg'))
            ->call('submit')
            ->assertHasNoErrors();

        $emprendedor->refresh();
        $this->assertSame(Emprendedor::VERIFICATION_IN_REVIEW, $emprendedor->verification_status);
        $this->assertNull($emprendedor->verification_rejection_reason);

        Livewire::actingAs($admin)
            ->test(EmprendedoresApprovalManager::class)
            ->call('approve', $emprendedor->id);

        $emprendedor->refresh();
        $this->assertTrue($emprendedor->canOperate());

        $this->actingAs($emprendedorUser->refresh())
            ->get(route('emprendedor.dashboard'))
            ->assertOk();
    }
}
