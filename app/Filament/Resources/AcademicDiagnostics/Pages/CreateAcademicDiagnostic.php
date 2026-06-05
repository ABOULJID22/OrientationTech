<?php

namespace App\Filament\Resources\AcademicDiagnostics\Pages;

use App\Filament\Resources\AcademicDiagnostics\AcademicDiagnosticResource;
use App\Models\AcademicDiagnostic;
use App\Services\Diagnostics\AcademicDiagnosticResultService;
use App\Services\Notifications\PlatformNotificationService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateAcademicDiagnostic extends CreateRecord
{
    protected static string $resource = AcademicDiagnosticResource::class;

    public function mount(): void
    {
        if (auth()->user()?->isStudent()) {
            $existingRecord = AcademicDiagnostic::query()
                ->where('user_id', auth()->id())
                ->latest('submitted_at')
                ->first();

            if ($existingRecord) {
                $this->redirect(
                    $this->getResourceUrl('edit', ['record' => $existingRecord]),
                    navigate: true,
                );

                return;
            }
        }

        parent::mount();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $data['status'] = 'completed';
        $data['submitted_at'] = now();

        return array_merge(
            $data,
            app(AcademicDiagnosticResultService::class)->calculate($data),
        );
    }

    protected function handleRecordCreation(array $data): Model
    {
        $data['user_id'] = auth()->id();

        return parent::handleRecordCreation($data);
    }

    protected function afterCreate(): void
    {
        app(PlatformNotificationService::class)->notifyDiagnosticCompleted($this->record);
    }
}
