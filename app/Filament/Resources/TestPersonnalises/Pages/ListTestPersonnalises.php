<?php

namespace App\Filament\Resources\TestPersonnalises\Pages;

use App\Filament\Resources\TestPersonnalises\TestPersonnaliseResource;
use App\Models\TestPersonnalise;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTestPersonnalises extends ListRecords
{
    protected static string $resource = TestPersonnaliseResource::class;

    public function mount(): void
    {
        if (auth()->user()?->isStudent()) {
            $existing = TestPersonnalise::query()
                ->where('user_id', auth()->id())
                ->latest('submitted_at')
                ->first();

            if ($existing) {
                $this->redirect($this->getResourceUrl('view', ['record' => $existing]), navigate: true);

                return;
            }

            $this->redirect($this->getResourceUrl('create'), navigate: true);

            return;
        }

        parent::mount();
    }

    protected function getHeaderActions(): array
    {
        if (! static::getResource()::canCreate()) {
            return [];
        }

        return [CreateAction::make()];
    }
}
