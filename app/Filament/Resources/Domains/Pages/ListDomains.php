<?php

namespace App\Filament\Resources\Domains\Pages;

use App\Filament\Resources\Domains\DomainResource;
use App\Services\Domains\DomainCsvImportService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListDomains extends ListRecords
{
    protected static string $resource = DomainResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->visible(fn () => auth()->user()?->isSuperAdmin()),
            Action::make('import_csv')
                ->label('Importer CSV')
                ->icon('heroicon-m-arrow-up-tray')
                ->visible(fn () => auth()->user()?->isSuperAdmin())
                ->form([
                    FileUpload::make('csv')
                        ->label('Fichier CSV domaines')
                        ->disk('local')
                        ->directory('imports')
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $path = Storage::disk('local')->path($data['csv']);
                    $count = app(DomainCsvImportService::class)->import($path);

                    Notification::make()->title("{$count} domaines importes")->success()->send();
                }),
        ];
    }
}
