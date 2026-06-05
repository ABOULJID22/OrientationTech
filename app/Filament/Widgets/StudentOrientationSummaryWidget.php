<?php

namespace App\Filament\Widgets;

use App\Models\AcademicDiagnostic;
use App\Models\TestPersonnalise;
use App\Services\TestPersonnalises\TestPersonnaliseResultService;
use Filament\Widgets\Widget;

class StudentOrientationSummaryWidget extends Widget
{
    protected static ?int $sort = 4;

    protected string $view = 'filament.widgets.student-orientation-summary-widget';

    protected int | string | array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $userId = auth()->id();
        $diagnostic = AcademicDiagnostic::query()
            ->where('user_id', $userId)
            ->latest('submitted_at')
            ->first();
        $personality = TestPersonnalise::query()
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->latest('submitted_at')
            ->first();

        $domains = $personality?->domain_scores ?? [];
        arsort($domains);
        $topDomains = array_slice($domains, 0, 4, true);

        return [
            'timeline' => $this->timeline($diagnostic, $personality),
            'careers' => $this->careers($topDomains),
            'skills' => $this->skillsToImprove($personality),
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()?->isStudent() ?? false;
    }

    private function timeline(?AcademicDiagnostic $diagnostic, ?TestPersonnalise $personality): array
    {
        $topDomain = $this->topDomainLabel($personality);

        return [
            ['period' => 'Maintenant', 'title' => 'Finaliser les tests', 'description' => $diagnostic?->isCompleted() && $personality?->isCompleted() ? 'Les tests principaux sont termines.' : 'Completer le diagnostic et le test personnalise.'],
            ['period' => '1 mois', 'title' => 'Clarifier le projet', 'description' => "Comparer {$topDomain} avec 2 autres domaines proches."],
            ['period' => '3 mois', 'title' => 'Construire un mini-projet', 'description' => 'Realiser un projet simple et le presenter a un enseignant ou parent.'],
            ['period' => '1 an', 'title' => 'Choisir une filiere', 'description' => 'Selectionner une voie scolaire compatible avec les resultats.'],
            ['period' => '3-5 ans', 'title' => 'Portfolio et formation', 'description' => 'Cumuler projets, stages, certifications et dossier de candidature.'],
        ];
    }

    private function careers(array $topDomains): array
    {
        $careers = [];

        foreach ($topDomains as $domain => $score) {
            foreach ($this->careerTitles((string) $domain) as $title) {
                $careers[] = [
                    'title' => $title,
                    'domain' => TestPersonnaliseResultService::domainLabel((string) $domain),
                    'score' => (int) round((float) $score),
                    'priority' => (float) $score >= 80 ? 'Tres recommande' : 'Recommande',
                ];
            }
        }

        return array_slice($careers, 0, 6);
    }

    private function skillsToImprove(?TestPersonnalise $personality): array
    {
        $scores = $personality?->axis_scores ?? [];
        asort($scores);

        return collect($scores)
            ->take(4)
            ->map(fn ($score, $axis): array => [
                'skill' => $axis,
                'score' => (int) round((float) $score),
                'advice' => $this->adviceForAxis((string) $axis),
            ])
            ->values()
            ->all();
    }

    private function topDomainLabel(?TestPersonnalise $personality): string
    {
        $scores = $personality?->domain_scores ?? [];
        arsort($scores);
        $domain = array_key_first($scores);

        return $domain ? TestPersonnaliseResultService::domainLabel((string) $domain) : 'le domaine principal';
    }

    private function careerTitles(string $domain): array
    {
        return match ($domain) {
            'data_ia', 'intelligence_artificielle' => ['Data Analyst', 'Ingenieur IA junior'],
            'cybersecurite' => ['Analyste cybersecurite', 'Consultant securite SI'],
            'ui_ux' => ['UX/UI Designer', 'Product Designer'],
            'cloud_infrastructure' => ['Ingenieur Cloud junior', 'DevOps junior'],
            default => ['Developpeur Full-Stack', 'Analyste fonctionnel'],
        };
    }

    private function adviceForAxis(string $axis): string
    {
        return match ($axis) {
            'communication' => 'Presenter un mini-projet devant un groupe.',
            'analytical' => 'Pratiquer logique, statistiques et resolution de problemes.',
            'creativity' => 'Explorer design, ideation et prototypes rapides.',
            'leadership' => 'Prendre un petit role de coordination dans un projet.',
            'autonomy' => 'Planifier une tache hebdomadaire sans assistance.',
            default => 'Travailler par petits exercices reguliers.',
        };
    }
}
