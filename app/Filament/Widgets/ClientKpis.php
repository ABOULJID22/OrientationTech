<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class ClientKpis extends BaseWidget
{
    protected static ?int $sort = 2;
    protected ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        // Minimal placeholder widget to avoid missing-file autoload errors.
        return [];
    }
}