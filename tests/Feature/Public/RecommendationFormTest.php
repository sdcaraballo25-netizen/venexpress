<?php

namespace Tests\Feature\Public;

use App\Livewire\Admin\RecommendationsManager;
use App\Livewire\Public\RecommendationForm;
use App\Models\Recommendation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RecommendationFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_visitor_can_submit_a_recommendation(): void
    {
        Livewire::test(RecommendationForm::class)
            ->set('name', 'Visitante')
            ->set('email', 'visitante@example.com')
            ->set('message', 'Sería genial tener pagos con Zelle.')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('submitted', true);

        $this->assertDatabaseHas('recommendations', [
            'name' => 'Visitante',
            'email' => 'visitante@example.com',
            'status' => Recommendation::STATUS_NEW,
        ]);
    }

    public function test_message_is_required(): void
    {
        Livewire::test(RecommendationForm::class)
            ->set('name', 'Visitante')
            ->set('message', '')
            ->call('submit')
            ->assertHasErrors(['message' => 'required']);
    }

    public function test_admin_can_archive_a_recommendation(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN_PRINCIPAL,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $recommendation = Recommendation::create([
            'name' => 'Visitante',
            'message' => 'Sugerencia de prueba.',
            'status' => Recommendation::STATUS_NEW,
        ]);

        Livewire::actingAs($admin)
            ->test(RecommendationsManager::class)
            ->call('archive', $recommendation->id);

        $this->assertSame(Recommendation::STATUS_ARCHIVED, $recommendation->fresh()->status);
    }
}
