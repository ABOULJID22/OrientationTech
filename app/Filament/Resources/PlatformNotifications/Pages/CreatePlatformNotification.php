<?php

namespace App\Filament\Resources\PlatformNotifications\Pages;

use App\Filament\Resources\PlatformNotifications\PlatformNotificationResource;
use App\Models\PlatformNotification;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreatePlatformNotification extends CreateRecord
{
    protected static string $resource = PlatformNotificationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->record->status !== PlatformNotification::STATUS_SENT) {
            return;
        }

        $count = $this->record->sendToRecipients();

        $notification = Notification::make()
            ->title($count > 0 ? 'Notification envoyee' : 'Notification creee sans destinataire')
            ->body($count > 0 ? "{$count} utilisateur(s) notifie(s)." : 'Aucun utilisateur actif ne correspond aux roles selectionnes.');

        $count > 0
            ? $notification->success()
            : $notification->warning();

        $notification->send();
    }
}
