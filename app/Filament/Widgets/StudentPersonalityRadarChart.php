<?php

namespace App\Filament\Widgets;

use App\Models\TestPersonnalise;
use App\Services\TestPersonnalises\TestPersonnaliseQuestionnaire;
use Filament\Widgets\ChartWidget;

class StudentPersonalityRadarChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Profil de personnalite';

    protected ?string $description = 'Radar des axes psychometriques du test personnalise.';

    protected int | string | array $columnSpan = ['lg' => 1];

    protected ?string $maxHeight = '360px';

    protected function getType(): string
    {
        return 'radar';
    }

    protected function getData(): array
    {
        $personality = TestPersonnalise::query()
            ->where('user_id', auth()->id())
            ->where('status', 'completed')
            ->latest('submitted_at')
            ->first();
        $scores = $personality?->axis_scores ?? [];

        return [
            'datasets' => [[
                'label' => 'Score',
                'data' => array_values($scores),
                'backgroundColor' => 'rgba(79, 107, 163, 0.20)',
                'borderColor' => '#2563eb',
                'pointBackgroundColor' => '#2563eb',
            ]],
            'labels' => collect(array_keys($scores))
                ->map(fn (string $axis): string => TestPersonnaliseQuestionnaire::axisLabel($axis))
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
                'line' => [
                    'tension' => 0,
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
                'r' => [
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
