<?php

namespace App\Filament\Resources\ResourceContents\Pages;

use App\Filament\Resources\ResourceContents\ResourceContentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListResourceContents extends ListRecords
{
    protected static string $resource = ResourceContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Nouvelle ressource'),
        ];
    }
}
