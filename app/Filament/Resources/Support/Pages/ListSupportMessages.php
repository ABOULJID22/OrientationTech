<?php

namespace App\Filament\Resources\Support\Pages;

use App\Filament\Resources\Support\SupportMessageResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;

class ListSupportMessages extends ListRecords
{
    protected static string $resource = SupportMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
                Action::make('conversations')
                ->label(__('support.conversations'))
                ->icon('heroicon-m-chat-bubble-oval-left-ellipsis')
                // Use a fixed URL because the named route may not be defined in every setup
                ->url('/admin/support-conversations')
                ->openUrlInNewTab(false),
        ];
    }
}
