<?php

namespace App\Filament\Widgets;

use App\Models\AcademicDiagnostic;
use App\Models\StudentProfile;
use App\Models\TestPersonnalise;
use App\Models\User;
use App\Services\TestPersonnalises\TestPersonnaliseResultService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminDashboardStats extends BaseWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $students = User::whereHas('roles', fn ($query) => $query->where('name', User::ROLE_STUDENT))->count();
        $teachers = User::whereHas('roles', fn ($query) => $query->where('name', User::ROLE_TEACHER))->count();
        $schools = StudentProfile::query()->whereNotNull('school_name')->distinct('school_name')->count('school_name');
        $diagnosticsDone = AcademicDiagnostic::query()->where('status', 'completed')->count();
        $personalitiesDone = TestPersonnalise::query()->where('status', 'completed')->count();
        $reportsGenerated = $this->reportsGenerated();
        $completionRate = $students > 0 ? (int) round(($reportsGenerated / $students) * 100) : 0;
        [$dominantDomain, $dominantCount] = $this->dominantDomain();

        return [
            Stat::make('Eleves inscrits', number_format($students))
                ->description('Comptes avec role eleve')
                ->descriptionIcon('heroicon-m-user-group')
                ->icon('heroicon-o-user-group')
                ->color('primary')
                ->chart($this->monthlyUsersChart()),

            Stat::make('Enseignants', number_format($teachers))
                ->description('Utilisateurs pedagogiques')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->icon('heroicon-o-academic-cap')
                ->color('info'),

            Stat::make('Etablissements', number_format($schools))
                ->description('Ecoles renseignees')
                ->descriptionIcon('heroicon-m-building-library')
                ->icon('heroicon-o-building-library')
                ->color('gray'),

            Stat::make('Tests completes', number_format($diagnosticsDone + $personalitiesDone))
                ->description("Diagnostic {$diagnosticsDone} | Personnalite {$personalitiesDone}")
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('success'),

            Stat::make('Rapports generes', number_format($reportsGenerated))
                ->description('Eleves avec les deux tests termines')
                ->descriptionIcon('heroicon-m-document-chart-bar')
                ->icon('heroicon-o-document-chart-bar')
                ->color('warning'),

            Stat::make('Taux completion', $completionRate . '%')
                ->description('Rapport complet / eleves inscrits')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->icon('heroicon-o-arrow-trending-up')
                ->color($completionRate >= 70 ? 'success' : 'warning')
                ->chart([$completionRate - 20, $completionRate - 10, $completionRate]),

            Stat::make('Domaine dominant', $dominantDomain)
                ->description($dominantCount . ' recommandations')
                ->descriptionIcon('heroicon-m-sparkles')
                ->icon('heroicon-o-sparkles')
                ->color('primary'),
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    protected function getColumns(): int | array | null
    {
        return ['@xl' => 4, '!@lg' => 3];
    }

    private function reportsGenerated(): int
    {
        return TestPersonnalise::query()
            ->where('status', 'completed')
            ->whereExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('academic_diagnostics')
                    ->whereColumn('academic_diagnostics.user_id', 'test_personnalises.user_id')
                    ->where('academic_diagnostics.status', 'completed');
            })
            ->distinct('user_id')
            ->count('user_id');
    }

    private function dominantDomain(): array
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

        $key = array_key_first($counts);

        return [
            $key ? TestPersonnaliseResultService::domainLabel((string) $key) : 'A determiner',
            $key ? $counts[$key] : 0,
        ];
    }

    private function monthlyUsersChart(): array
    {
        return collect(range(5, 0))
            ->map(fn (int $monthsAgo): int => User::query()
                ->whereHas('roles', fn ($query) => $query->where('name', User::ROLE_STUDENT))
                ->whereBetween('created_at', [
                    now()->subMonths($monthsAgo)->startOfMonth(),
                    now()->subMonths($monthsAgo)->endOfMonth(),
                ])
                ->count())
            ->values()
            ->all();
    }
}
