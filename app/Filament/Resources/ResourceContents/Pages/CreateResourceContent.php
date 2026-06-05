<?php

namespace App\Filament\Resources\ResourceContents\Pages;

use App\Filament\Resources\ResourceContents\ResourceContentResource;
use App\Services\Notifications\PlatformNotificationService;
use Filament\Resources\Pages\CreateRecord;

class CreateResourceContent extends CreateRecord
{
    protected static string $resource = ResourceContentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['teacher_id'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        app(PlatformNotificationService::class)->notifyResourceCreated($this->record->fresh(['teacher']));
    }
}
