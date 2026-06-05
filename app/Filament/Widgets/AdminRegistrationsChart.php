<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;

class AdminRegistrationsChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Inscriptions eleves par mois';

    protected ?string $description = 'Evolution des nouveaux comptes eleves sur les 6 derniers mois.';

    protected int | string | array $columnSpan = ['lg' => 1];

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $months = collect(range(5, 0))->map(fn (int $monthsAgo) => now()->subMonths($monthsAgo));

        return [
            'datasets' => [[
                'label' => 'Eleves inscrits',
                'data' => $months->map(fn ($month): int => User::query()
                    ->whereHas('roles', fn ($query) => $query->where('name', User::ROLE_STUDENT))
                    ->whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                    ->count())->values()->all(),
                'borderColor' => '#2563eb',
                'backgroundColor' => 'rgba(79, 107, 163, 0.18)',
                'fill' => true,
                'tension' => 0.35,
            ]],
            'labels' => $months->map(fn ($month): string => $month->translatedFormat('M Y'))->values()->all(),
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }
}
