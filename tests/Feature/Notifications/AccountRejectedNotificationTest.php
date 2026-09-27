<?php

namespace Tests\Feature\Notifications;

use App\Models\User;
use App\Notifications\AccountRejected;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fase 6: AccountRejected ahora puede incluir el motivo del rechazo
 * en el correo. Debe aparecer SOLO cuando se pasa un motivo — sin
 * romper el uso existente de la notificación (roleLabel solo).
 */
class AccountRejectedNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(): User
    {
        return User::factory()->create(['name' => 'Juan Pérez']);
    }

    public function test_the_email_includes_the_reason_when_one_is_given(): void
    {
        $user = $this->createUser();

        $mail = (new AccountRejected('Repartidor', 'La foto de la licencia está vencida.'))
            ->toMail($user);

        $lines = [...$mail->introLines, ...$mail->outroLines];

        $this->assertTrue(
            collect($lines)->contains(fn ($line) => str_contains($line, 'La foto de la licencia está vencida.')),
            'El correo no incluyó el motivo del rechazo.'
        );
    }

    public function test_the_email_does_not_mention_a_reason_when_none_is_given(): void
    {
        $user = $this->createUser();

        // Mismo uso que antes de Fase 6 (solo roleLabel) — no debe
        // romperse ni mostrar un "Motivo:" vacío.
        $mail = (new AccountRejected('Repartidor'))->toMail($user);

        $lines = [...$mail->introLines, ...$mail->outroLines];

        $this->assertFalse(
            collect($lines)->contains(fn ($line) => str_starts_with($line, 'Motivo:')),
            'El correo mostró una línea de motivo aunque no se dio ninguno.'
        );
    }

    public function test_the_email_still_mentions_the_role_and_greets_the_user_by_name(): void
    {
        $user = $this->createUser();

        $mail = (new AccountRejected('Aliado', 'RIF ilegible.'))->toMail($user);

        $this->assertSame('Tu solicitud de VenExpress no fue aprobada', $mail->subject);
        $this->assertStringContainsString('Juan Pérez', $mail->greeting);

        $lines = [...$mail->introLines, ...$mail->outroLines];
        $this->assertTrue(collect($lines)->contains(fn ($line) => str_contains($line, 'Aliado')));
    }
}
