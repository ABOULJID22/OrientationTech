<?php

namespace App\Filament\Resources\ResourceContents\Pages;

use App\Filament\Resources\ResourceContents\ResourceContentResource;
use App\Services\Notifications\PlatformNotificationService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditResourceContent extends EditRecord
{
    protected static string $resource = ResourceContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        if (! $this->record->wasChanged('status')) {
            return;
        }

        app(PlatformNotificationService::class)->notifyResourceStatusChanged(
            $this->record->fresh(['teacher']),
            $this->record->getOriginal('status'),
        );
    }
}
