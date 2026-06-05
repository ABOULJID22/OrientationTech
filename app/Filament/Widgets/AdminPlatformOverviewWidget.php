<?php

namespace App\Filament\Widgets;

use App\Models\AcademicDiagnostic;
use App\Models\StudentProfile;
use App\Models\TestPersonnalise;
use App\Models\User;
use Filament\Widgets\Widget;

class AdminPlatformOverviewWidget extends Widget
{
    protected static ?int $sort = 4;

    protected string $view = 'filament.widgets.admin-platform-overview-widget';

    protected int | string | array $columnSpan = 'full';

    protected function getViewData(): array
    {
        return [
            'funnel' => $this->funnel(),
            'schools' => $this->schools(),
            'recentUsers' => $this->recentUsers(),
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    private function funnel(): array
    {
        $students = User::whereHas('roles', fn ($query) => $query->where('name', User::ROLE_STUDENT))->count();
        $profiles = StudentProfile::query()->where('is_complete', true)->count();
        $diagnostics = AcademicDiagnostic::query()->where('status', 'completed')->distinct('user_id')->count('user_id');
        $personalities = TestPersonnalise::query()->where('status', 'completed')->distinct('user_id')->count('user_id');
        $reports = TestPersonnalise::query()
            ->where('status', 'completed')
            ->whereExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('academic_diagnostics')
                    ->whereColumn('academic_diagnostics.user_id', 'test_personnalises.user_id')
                    ->where('academic_diagnostics.status', 'completed');
            })
            ->distinct('user_id')
            ->count('user_id');

        return [
            ['label' => 'Inscription', 'value' => $students],
            ['label' => 'Profil complete', 'value' => $profiles],
            ['label' => 'Diagnostic', 'value' => $diagnostics],
            ['label' => 'Personnalite', 'value' => $personalities],
            ['label' => 'Rapport', 'value' => $reports],
        ];
    }

    private function schools(): array
    {
        return StudentProfile::query()
            ->selectRaw('school_name, city, count(*) as students')
            ->whereNotNull('school_name')
            ->groupBy('school_name', 'city')
            ->orderByDesc('students')
            ->limit(6)
            ->get()
            ->map(function ($school): array {
                $diagnostics = AcademicDiagnostic::query()
                    ->whereHas('user.studentProfile', fn ($query) => $query->where('school_name', $school->school_name))
                    ->where('status', 'completed')
                    ->count();

                $completion = $school->students > 0 ? (int) round(($diagnostics / $school->students) * 100) : 0;

                return [
                    'name' => $school->school_name,
                    'city' => $school->city ?: '-',
                    'students' => $school->students,
                    'completion' => min(100, $completion),
                ];
            })
            ->all();
    }

    private function recentUsers(): array
    {
        return User::query()
            ->latest()
            ->limit(6)
            ->get()
            ->map(fn (User $user): array => [
                'name' => $user->name,
                'role' => $user->roles->pluck('name')->first() ?: '-',
                'city' => $user->studentProfile?->city ?? $user->city ?? '-',
                'created_at' => $user->created_at?->format('d/m/Y'),
            ])
            ->all();
    }
}
