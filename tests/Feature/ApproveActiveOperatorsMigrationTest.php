<?php

namespace Tests\Feature;

use App\Models\Ally;
use App\Models\Driver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Migración 2026_10_06_000002: repartidores y agencias que ya operaban
 * (ACTIVO) pero quedaron con la verificación en PENDIENTE se aprueban
 * todos de una vez; el resto sigue esperando la revisión normal.
 */
class ApproveActiveOperatorsMigrationTest extends TestCase
{
    use CreatesTestPackages;
    use RefreshDatabase;

    public function test_active_operators_pending_verification_are_approved(): void
    {
        $activePending = Driver::factory()->create([
            'status' => Driver::STATUS_ACTIVE,
            'verification_status' => Driver::VERIFICATION_PENDING,
        ]);
        $inReview = Driver::factory()->create([
            'status' => Driver::STATUS_ACTIVE,
            'verification_status' => Driver::VERIFICATION_IN_REVIEW,
        ]);
        $notActive = Driver::factory()->create([
            'status' => Driver::STATUS_PENDING,
            'verification_status' => Driver::VERIFICATION_PENDING,
        ]);
        $ally = $this->createAlly(['verification_status' => Ally::VERIFICATION_PENDING]);
        $pendingAlly = $this->createAlly([
            'status' => Ally::STATUS_PENDING,
            'verification_status' => Ally::VERIFICATION_PENDING,
        ]);

        (require database_path('migrations/2026_10_06_000002_approve_active_operators_pending_verification.php'))->up();

        $this->assertSame(Driver::VERIFICATION_VERIFIED, $activePending->fresh()->verification_status);
        $this->assertNotNull($activePending->fresh()->verification_reviewed_at);
        $this->assertSame(Driver::VERIFICATION_IN_REVIEW, $inReview->fresh()->verification_status);
        $this->assertSame(Driver::VERIFICATION_PENDING, $notActive->fresh()->verification_status);
        $this->assertSame(Ally::VERIFICATION_VERIFIED, $ally->fresh()->verification_status);
        $this->assertSame(Ally::VERIFICATION_PENDING, $pendingAlly->fresh()->verification_status);
    }
}
