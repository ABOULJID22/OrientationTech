<?php

namespace App\Filament\Resources\TestPersonnalises\Pages;

use App\Filament\Resources\TestPersonnalises\TestPersonnaliseResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Collection;

class ViewTestPersonnalise extends ViewRecord
{
    protected static string $resource = TestPersonnaliseResource::class;

    protected string $view = 'filament.resources.test-personnalises.pages.view-test-personnalise';

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Modifier')
                ->icon('heroicon-m-pencil-square')
                ->url(fn (): string => TestPersonnaliseResource::getUrl('edit', ['record' => $this->getRecord()])),
        ];
    }

    public function axisScoreSeries(): array
    {
        return $this->prepareScoreSeries($this->getRecord()->axis_scores ?? [], 8);
    }

    public function domainScoreSeries(): array
    {
        return $this->prepareScoreSeries($this->getRecord()->domain_scores ?? [], 8);
    }

    public function getResultSummary(): string
    {
        return $this->getRecord()->result_summary ?: 'Aucun résumé disponible.';
    }

    public function getMetadata(): array
    {
        return [
            'test_name' => $this->getRecord()->test_name ?: '—',
            'annee' => $this->getRecord()->version ?: '—',
            'target_level' => $this->getRecord()->target_level ?: '—',
            'status' => $this->getRecord()->status ?: '—',
            'primary_domain' => $this->getRecord()->primary_domain ?: '—',
            'secondary_domain' => $this->getRecord()->secondary_domain ?: '—',
            'submitted_at' => $this->getRecord()->submitted_at,
            'created_at' => $this->getRecord()->created_at,
            'updated_at' => $this->getRecord()->updated_at,
        ];
    }

    protected function prepareScoreSeries(array $scores, int $limit): array
    {
        return collect($scores)
            ->sortDesc()
            ->take($limit)
            ->map(fn ($score, $label): array => [
                'label' => (string) $label,
                'score' => (float) $score,
            ])
            ->values()
            ->all();
    }
}
