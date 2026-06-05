<?php

namespace App\Filament\Resources\PlatformNotifications\Pages;

use App\Filament\Resources\PlatformNotifications\PlatformNotificationResource;
use App\Models\PlatformNotification;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPlatformNotification extends EditRecord
{
    protected static string $resource = PlatformNotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Supprimer'),
        ];
    }

    protected function afterSave(): void
    {
        if ($this->record->status !== PlatformNotification::STATUS_SENT || $this->record->sent_at) {
            return;
        }

        $count = $this->record->sendToRecipients();

        $notification = Notification::make()
            ->title($count > 0 ? 'Notification envoyee' : 'Notification sans destinataire')
            ->body($count > 0 ? "{$count} utilisateur(s) notifie(s)." : 'Aucun utilisateur actif ne correspond aux roles selectionnes.');

        $count > 0
            ? $notification->success()
            : $notification->warning();

        $notification->send();
    }
}
