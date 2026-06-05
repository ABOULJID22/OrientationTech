<?php

namespace App\Filament\Resources\TestPersonnalises\Pages;

use App\Filament\Pages\MesResultatsDePersonnalites;
use App\Filament\Resources\TestPersonnalises\TestPersonnaliseResource;
use App\Models\TestPersonnalise;
use App\Services\Notifications\PlatformNotificationService;
use App\Services\TestPersonnalises\TestPersonnaliseResultService;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateTestPersonnalise extends CreateRecord
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
        }

        parent::mount();
    }

    protected function getSubmitFormAction(): Action
    {
        return parent::getSubmitFormAction()
            ->label('Créer');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSubmitFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $data['status'] = 'completed';
        $data['submitted_at'] = now();

        return array_merge(
            $data,
            app(TestPersonnaliseResultService::class)->calculate($data['answers'] ?? []),
        );
    }

    protected function handleRecordCreation(array $data): Model
    {
        $data['user_id'] = auth()->id();

        return parent::handleRecordCreation($data);
    }

    protected function getRedirectUrl(): string
    {
        if (auth()->user()?->isStudent()) {
            return MesResultatsDePersonnalites::getUrl();
        }

        return parent::getRedirectUrl();
    }

    protected function afterCreate(): void
    {
        app(PlatformNotificationService::class)->notifyPersonalityCompleted($this->record);
    }
}
