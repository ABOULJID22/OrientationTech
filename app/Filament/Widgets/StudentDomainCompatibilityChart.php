<?php

namespace App\Filament\Widgets;

use App\Models\TestPersonnalise;
use App\Services\TestPersonnalises\TestPersonnaliseResultService;
use Filament\Widgets\ChartWidget;

class StudentDomainCompatibilityChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Compatibilite avec les domaines';

    protected ?string $description = 'Top domaines recommandes selon les resultats actuels.';

    protected int | string | array $columnSpan = ['lg' => 1];

    protected ?string $maxHeight = '360px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $personality = TestPersonnalise::query()
            ->where('user_id', auth()->id())
            ->where('status', 'completed')
            ->latest('submitted_at')
            ->first();
        $scores = $personality?->domain_scores ?? [];

        arsort($scores);
        $scores = array_slice($scores, 0, 6, true);

        return [
            'datasets' => [[
                'label' => 'Compatibilite',
                'data' => array_values($scores),
                'backgroundColor' => '#2563eb',
                'borderRadius' => 8,
            ]],
            'labels' => collect(array_keys($scores))
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
            'plugins' => [
                'tooltip' => [
                    'enabled' => false,
                ],
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'x' => [
                    'beginAtZero' => true,
                    'max' => 100,
                ],
            ],
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()?->isStudent() ?? false;
    }
}
