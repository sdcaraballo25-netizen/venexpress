<?php

namespace Tests\Feature\Admin;

use App\Exports\SimpleArrayExport;
use App\Livewire\Admin\AuditLogViewer;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

/**
 * Admin\AuditLogViewer no tenía ningún test. Cubre el listado
 * filtrado (que ya existía) y la exportación a Excel (nueva).
 */
class AuditLogViewerTest extends TestCase
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

    private function createLog(string $action, string $description): AuditLog
    {
        return AuditLog::create([
            'action' => $action,
            'description' => $description,
            'ip_address' => '127.0.0.1',
        ]);
    }

    public function test_the_screen_renders_and_lists_logs(): void
    {
        $log = $this->createLog('user.created', 'Se creó el usuario de prueba');

        Livewire::actingAs($this->createAdmin())
            ->test(AuditLogViewer::class)
            ->assertOk()
            ->assertSee('Se creó el usuario de prueba');
    }

    public function test_export_excel_respects_the_action_filter(): void
    {
        Excel::fake();

        $matching = $this->createLog('user.created', 'Log que sí debe salir en el Excel');
        $other = $this->createLog('user.deleted', 'Log que NO debe salir en el Excel');

        $filename = 'bitacora-'.now()->format('Y-m-d').'.xlsx';

        Livewire::actingAs($this->createAdmin())
            ->test(AuditLogViewer::class)
            ->set('actionFilter', 'user.created')
            ->call('exportExcel')
            ->assertFileDownloaded();

        Excel::assertDownloaded($filename, function (SimpleArrayExport $export) use ($matching, $other) {
            $descriptions = array_column(iterator_to_array($export->generator()), 4);

            self::assertContains($matching->description, $descriptions);
            self::assertNotContains($other->description, $descriptions);

            return true;
        });
    }

    public function test_export_excel_includes_the_ip_address(): void
    {
        Excel::fake();

        $this->createLog('user.created', 'Log con IP');

        $filename = 'bitacora-'.now()->format('Y-m-d').'.xlsx';

        Livewire::actingAs($this->createAdmin())
            ->test(AuditLogViewer::class)
            ->call('exportExcel');

        Excel::assertDownloaded($filename, function (SimpleArrayExport $export) {
            $ips = array_column(iterator_to_array($export->generator()), 5);

            self::assertContains('127.0.0.1', $ips);

            return true;
        });
    }

    /**
     * client.delivery_accepted/rejected no son acciones administrativas
     * (las dispara el propio cliente al aceptar/rechazar una entrega a
     * domicilio) y pasan una vez por cada paquete con entrega a
     * domicilio — con el tiempo ahogarían la bitácora real.
     */
    public function test_noisy_client_delivery_actions_are_hidden_from_the_list(): void
    {
        $this->createLog('client.delivery_accepted', 'El cliente confirmó la recepción a domicilio de la guía X.');
        $this->createLog('client.delivery_rejected', 'El cliente rechazó la entrega a domicilio de la guía Y.');
        $admin = $this->createLog('user.created', 'Acción administrativa real');

        $component = Livewire::actingAs($this->createAdmin())
            ->test(AuditLogViewer::class)
            ->assertDontSee('El cliente confirmó la recepción')
            ->assertDontSee('El cliente rechazó la entrega')
            ->assertSee('Acción administrativa real');

        $this->assertNotContains(
            'client.delivery_accepted',
            $component->viewData('actions')->all()
        );
    }

    public function test_noisy_client_delivery_actions_are_excluded_from_the_export_too(): void
    {
        Excel::fake();

        $this->createLog('client.delivery_accepted', 'No debe salir en el Excel');
        $admin = $this->createLog('user.created', 'Sí debe salir en el Excel');

        $filename = 'bitacora-'.now()->format('Y-m-d').'.xlsx';

        Livewire::actingAs($this->createAdmin())
            ->test(AuditLogViewer::class)
            ->call('exportExcel');

        Excel::assertDownloaded($filename, function (SimpleArrayExport $export) use ($admin) {
            $descriptions = array_column(iterator_to_array($export->generator()), 4);

            self::assertContains($admin->description, $descriptions);
            self::assertNotContains('No debe salir en el Excel', $descriptions);

            return true;
        });
    }

    /**
     * El código crudo (ej. "user.created") nunca debe mostrarse tal
     * cual: siempre pasa por AuditLog::labelFor()/actionLabel().
     */
    public function test_the_screen_shows_a_human_readable_action_label_not_the_raw_code(): void
    {
        $this->createLog('user.created', 'Se creó el usuario de prueba');

        // 'user.created' sigue apareciendo como valor interno del
        // <option> del filtro (necesario para que el filtrado siga
        // funcionando) — lo que no debe pasar es que la ETIQUETA
        // visible de la fila sea el código crudo en vez de la
        // traducción de AuditLog::actionLabel().
        Livewire::actingAs($this->createAdmin())
            ->test(AuditLogViewer::class)
            ->assertSee('Usuario creado')
            ->assertSee(AuditLog::labelFor('user.created'));

        $this->assertSame('Usuario creado', AuditLog::labelFor('user.created'));
    }

    public function test_actor_name_links_to_that_users_data_by_email(): void
    {
        $admin = $this->createAdmin();

        AuditLog::create([
            'actor_user_id' => $admin->id,
            'action' => 'user.created',
            'description' => 'Log con actor',
            'ip_address' => '127.0.0.1',
        ]);

        Livewire::actingAs($this->createAdmin())
            ->test(AuditLogViewer::class)
            ->assertSee(route('admin.users', ['search' => $admin->email]), false);
    }

    public function test_date_range_filters_out_older_logs(): void
    {
        // created_at no está en $fillable (Eloquent lo maneja solo),
        // así que hace falta forceFill() para poder simular fechas
        // pasadas en la prueba — un ->update() normal lo ignoraría en
        // silencio y la prueba no probaría nada real.
        $recent = $this->createLog('user.created', 'Log reciente');
        $recent->forceFill(['created_at' => now()->subDay()])->save();

        $old = $this->createLog('user.created', 'Log viejo');
        $old->forceFill(['created_at' => now()->subDays(60)])->save();

        Livewire::actingAs($this->createAdmin())
            ->test(AuditLogViewer::class)
            ->set('dateRange', '7d')
            ->assertSee('Log reciente')
            ->assertDontSee('Log viejo');
    }

    public function test_custom_date_range_filters_by_the_chosen_dates(): void
    {
        $inRange = $this->createLog('user.created', 'Dentro del rango');
        $inRange->forceFill(['created_at' => now()->subDays(10)])->save();

        $outOfRange = $this->createLog('user.created', 'Fuera del rango');
        $outOfRange->forceFill(['created_at' => now()->subDays(60)])->save();

        Livewire::actingAs($this->createAdmin())
            ->test(AuditLogViewer::class)
            ->set('dateRange', 'custom')
            ->set('customFrom', now()->subDays(15)->toDateString())
            ->set('customTo', now()->subDays(5)->toDateString())
            ->assertSee('Dentro del rango')
            ->assertDontSee('Fuera del rango');
    }
}
