<?php

namespace App\Livewire\Admin;

use App\Models\Recommendation;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Recomendaciones')]
class RecommendationsManager extends Component
{
    use WithPagination;

    public function markRead(int $recommendationId): void
    {
        $recommendation = Recommendation::findOrFail($recommendationId);

        if ($recommendation->status === Recommendation::STATUS_NEW) {
            $recommendation->update(['status' => Recommendation::STATUS_READ]);
        }
    }

    public function archive(int $recommendationId): void
    {
        Recommendation::findOrFail($recommendationId)->update([
            'status' => Recommendation::STATUS_ARCHIVED,
        ]);

        session()->flash('success', 'Recomendación archivada.');
    }

    public function render()
    {
        return view('livewire.admin.recommendations-manager', [
            'recommendations' => Recommendation::query()
                ->where('status', '!=', Recommendation::STATUS_ARCHIVED)
                ->latest()
                ->paginate(15),
        ]);
    }
}
