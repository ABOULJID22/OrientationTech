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

    protected ?string $maxHeight = '280px';

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
                'pointRadius' => 3,
                'pointHoverRadius' => 3,
                'pointHitRadius' => 0,
            ]],
            'labels' => $months->map(fn ($month): string => $month->translatedFormat('M Y'))->values()->all(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'resizeDelay' => 0,
            'events' => [],
            'animation' => false,
            'animations' => false,
            'hover' => [
                'mode' => null,
                'animationDuration' => 0,
            ],
            'transitions' => [
                'active' => [
                    'animation' => [
                        'duration' => 0,
                    ],
                ],
                'resize' => [
                    'animation' => [
                        'duration' => 0,
                    ],
                ],
            ],
            'elements' => [
                'point' => [
                    'radius' => 3,
                    'hoverRadius' => 3,
                    'hitRadius' => 0,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'grace' => '10%',
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }
}
