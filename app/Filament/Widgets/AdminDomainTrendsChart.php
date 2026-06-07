<?php

namespace App\Filament\Widgets;

use App\Models\TestPersonnalise;
use App\Services\TestPersonnalises\TestPersonnaliseResultService;
use Filament\Widgets\ChartWidget;

class AdminDomainTrendsChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Domaines les plus recommandes';

    protected ?string $description = 'Tendance globale basee sur le domaine principal du test personnalise.';

    protected int | string | array $columnSpan = ['lg' => 1];

    protected ?string $maxHeight = '280px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $counts = [];

        TestPersonnalise::query()
            ->where('status', 'completed')
            ->whereNotNull('primary_domain')
            ->pluck('primary_domain')
            ->each(function (string $domain) use (&$counts): void {
                $counts[$domain] = ($counts[$domain] ?? 0) + 1;
            });

        arsort($counts);
        $counts = array_slice($counts, 0, 8, true);

        return [
            'datasets' => [[
                'label' => 'Recommandations',
                'data' => array_values($counts),
                'backgroundColor' => '#2563eb',
                'hoverBackgroundColor' => '#2563eb',
                'borderRadius' => 8,
            ]],
            'labels' => collect(array_keys($counts))
                ->map(fn (string $domain): string => TestPersonnaliseResultService::domainLabel($domain))
                ->values()
                ->all(),
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
            'indexAxis' => 'y',
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
            'scales' => [
                'x' => [
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
