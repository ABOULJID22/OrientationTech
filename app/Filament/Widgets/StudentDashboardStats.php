<?php

namespace App\Filament\Widgets;

use App\Models\AcademicDiagnostic;
use App\Models\TestPersonnalise;
use App\Services\TestPersonnalises\TestPersonnaliseResultService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StudentDashboardStats extends BaseWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        $profile = $user->studentProfile;
        $diagnostic = AcademicDiagnostic::query()
            ->where('user_id', $user->id)
            ->latest('submitted_at')
            ->first();
        $personality = TestPersonnalise::query()
            ->where('user_id', $user->id)
            ->latest('submitted_at')
            ->first();

        $profileProgress = $this->profileProgress($profile);
        $testsDone = collect([
            (bool) $profile?->is_complete || $profileProgress >= 70,
            (bool) $diagnostic?->isCompleted(),
            $this->hasIkigai($diagnostic),
            (bool) $personality?->isCompleted(),
        ])->filter()->count();
        $ikigaiScore = $this->ikigaiScore($diagnostic);
        [$domainLabel, $domainScore] = $this->primaryDomain($diagnostic, $personality);

        return [
            Stat::make('Profil complete', $profileProgress . '%')
                ->description('Informations personnelles et scolaires')
                ->descriptionIcon('heroicon-m-user-circle')
                ->icon('heroicon-o-user-circle')
                ->color($profileProgress >= 80 ? 'success' : 'warning')
                ->chart([$profileProgress - 20, $profileProgress - 10, $profileProgress - 5, $profileProgress]),

            Stat::make('Tests termines', "{$testsDone}/4")
                ->description('Profil, diagnostic, Ikigai, personnalite')
                ->descriptionIcon('heroicon-m-check-circle')
                ->icon('heroicon-o-clipboard-document-check')
                ->color($testsDone >= 3 ? 'success' : 'info')
                ->chart([1, 2, $testsDone, 4]),

            Stat::make('Score Ikigai', $ikigaiScore . '%')
                ->description('Coherence passion, forces et metier')
                ->descriptionIcon('heroicon-m-sparkles')
                ->icon('heroicon-o-sparkles')
                ->color($ikigaiScore >= 70 ? 'success' : 'warning')
                ->chart([$ikigaiScore - 15, $ikigaiScore - 8, $ikigaiScore]),

            Stat::make('Domaine principal', $domainLabel)
                ->description('Compatibilite estimee: ' . $domainScore . '%')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->icon('heroicon-o-academic-cap')
                ->color('primary')
                ->chart([$domainScore - 18, $domainScore - 8, $domainScore]),
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()?->isStudent() ?? false;
    }

    private function profileProgress(mixed $profile): int
    {
        if (! $profile) {
            return 0;
        }

        $fields = [
            $profile->education_level,
            $profile->school_name,
            $profile->school_type,
            $profile->city,
            $profile->birth_date,
            $profile->gender,
        ];

        return (int) round((collect($fields)->filter()->count() / count($fields)) * 100);
    }

    private function hasIkigai(?AcademicDiagnostic $diagnostic): bool
    {
        $answers = $diagnostic?->diagnostic_answers ?? [];

        return collect(['ikigai_love', 'ikigai_good_at', 'ikigai_world_needs', 'ikigai_profession'])
            ->contains(fn (string $key): bool => filled($answers[$key] ?? null));
    }

    private function ikigaiScore(?AcademicDiagnostic $diagnostic): int
    {
        $answers = $diagnostic?->diagnostic_answers ?? [];
        $keys = ['ikigai_love', 'ikigai_good_at', 'ikigai_world_needs', 'ikigai_profession'];
        $filled = collect($keys)->filter(fn (string $key): bool => filled($answers[$key] ?? null))->count();

        return (int) round(($filled / count($keys)) * 100);
    }

    private function primaryDomain(?AcademicDiagnostic $diagnostic, ?TestPersonnalise $personality): array
    {
        $scores = $personality?->domain_scores ?? [];

        foreach ($this->diagnosticBoosts($diagnostic) as $domain => $boost) {
            $scores[$domain] = min(100, (float) ($scores[$domain] ?? 0) + $boost);
        }

        arsort($scores);

        $key = array_key_first($scores);
        $score = $key ? (int) round((float) $scores[$key]) : 0;

        return [
            $key ? TestPersonnaliseResultService::domainLabel((string) $key) : 'A determiner',
            $score,
        ];
    }

    private function diagnosticBoosts(?AcademicDiagnostic $diagnostic): array
    {
        $answers = $diagnostic?->diagnostic_answers ?? [];
        $boosts = [];

        $map = [
            'data_ai_interest' => ['data_ia', 'intelligence_artificielle'],
            'programming_interest' => ['development_web', 'mobile'],
            'cyber_network_interest' => ['cybersecurite', 'systemes_reseaux'],
            'design_product_interest' => ['ui_ux', 'creation_contenu'],
        ];

        foreach ($map as $answerKey => $domains) {
            $score = (int) ($answers[$answerKey] ?? 0);
            if ($score >= 4) {
                foreach ($domains as $domain) {
                    $boosts[$domain] = ($boosts[$domain] ?? 0) + ($score * 2);
                }
            }
        }

        return $boosts;
    }
}
