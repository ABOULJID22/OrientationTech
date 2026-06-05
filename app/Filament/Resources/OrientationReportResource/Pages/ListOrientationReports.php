<?php

namespace App\Filament\Resources\OrientationReportResource\Pages;

use App\Filament\Resources\OrientationReportResource;
use Filament\Resources\Pages\ListRecords;

class ListOrientationReports extends ListRecords
{
    protected static string $resource = OrientationReportResource::class;

    public function getTitle(): string
    {
        return 'Rapports d\'orientation valides';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
