<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use App\Services\Notifications\PlatformNotificationService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (auth()->user()?->isTeacher() && ! auth()->user()?->isSuperAdmin() && ($data['status'] ?? null) === Post::STATUS_PUBLISHED) {
            $data['status'] = Post::STATUS_PENDING;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $record = $this->record;
        $oldStatus = $record->wasChanged('status') ? $record->getOriginal('status') : null;

       
        $primaryLocale = config('app.fallback_locale') ?: 'fr';

       
        $editedLocale = app()->getLocale();

        if ($editedLocale === $primaryLocale) {
            $t = $record->translation($primaryLocale) ?: $record->translations()->first();
            if ($t) {
                $record->forceFill([
                    'title' => $t->title,
                    'slug' => $t->slug,
                    'content' => $t->content,
                ])->saveQuietly();
            }
        }

        if ($oldStatus !== null) {
            app(PlatformNotificationService::class)->notifyPostStatusChanged($record->fresh(['author']), $oldStatus);
        }
    }
}
