<?php

namespace App\Filament\Widgets;

use App\Models\AcademicDiagnostic;
use App\Models\Post;
use App\Models\ResourceContent;
use App\Models\TestPersonnalise;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class BlogStats extends BaseWidget
{
    protected static ?int $sort = 5;

    protected ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $user = auth()->user();
        $isTeacher = (bool) ($user?->isTeacher() && ! $user?->isSuperAdmin());

        $postsQuery = Post::query();
        $hasResourceContentsTable = Schema::hasTable('resource_contents');
        $resourcesQuery = $hasResourceContentsTable ? ResourceContent::query() : null;

        if ($isTeacher) {
            $postsQuery->where('author_id', $user->id);
            $resourcesQuery?->where('teacher_id', $user->id);
        }

        $totalPosts = (clone $postsQuery)->count();
        $recentPosts = (clone $postsQuery)->where('created_at', '>=', now()->subDays(30))->count();
        $publishedPosts = (clone $postsQuery)->where('status', Post::STATUS_PUBLISHED)->count();
        $pendingPosts = (clone $postsQuery)->where('status', Post::STATUS_PENDING)->count();
        $totalViews = (clone $postsQuery)->sum('views_count');
        $totalLikes = (clone $postsQuery)->sum('likes_count');
        $totalComments = (clone $postsQuery)->sum('comments_count');
        $publishedResources = $resourcesQuery ? (clone $resourcesQuery)->where('status', ResourceContent::STATUS_PUBLISHED)->count() : 0;
        $recentResources = $resourcesQuery ? (clone $resourcesQuery)->where('created_at', '>=', now()->subDays(30))->count() : 0;
        $completedDiagnostics = AcademicDiagnostic::query()->where('status', 'completed')->count();
        $completedPersonalities = TestPersonnalise::query()->where('status', 'completed')->count();
        $reportsReady = $this->reportsReady();

        return [
                Stat::make($isTeacher ? 'Mes articles' : 'Articles', number_format($totalPosts))
                ->description($publishedPosts . ' publies | ' . $pendingPosts . ' en attente')
                ->descriptionIcon('heroicon-m-document-text')
                ->icon('heroicon-o-document-text')
                ->color($pendingPosts > 0 ? 'warning' : 'primary')
                ->chart($this->monthlyPostsChart($isTeacher ? $user->id : null))
                ->url(route('filament.admin.resources.posts.index'))
                ->extraAttributes([
                    'class' => 'stat-card stat-card-primary',
                ]),

                Stat::make('Vues blog', number_format($totalViews))
                ->description($recentPosts . ' articles ajoutes ce mois')
                ->descriptionIcon('heroicon-m-eye')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->extraAttributes([
                    'class' => 'stat-card stat-card-primary',
                ]),

                Stat::make('Likes', number_format($totalLikes))
                ->description($totalComments . ' commentaires visibles')
                ->descriptionIcon('heroicon-m-heart')
                ->icon('heroicon-o-heart')
                ->color('danger')
                ->extraAttributes([
                    'class' => 'stat-card stat-card-warning',
                ]),

                Stat::make($isTeacher ? 'Mes ressources' : 'Ressources publiees', number_format($publishedResources))
                ->description($recentResources . ' ajoutees ce mois')
                ->descriptionIcon('heroicon-m-book-open')
                ->icon('heroicon-o-book-open')
                ->color('info')
                ->chart($this->monthlyResourcesChart($isTeacher ? $user->id : null))
                ->url(route('filament.admin.resources.resource-contents.index'))
                ->extraAttributes([
                    'class' => 'stat-card stat-card-primary',
                ]),

                Stat::make('Tests completes', number_format($completedDiagnostics + $completedPersonalities))
                ->description("Diagnostic {$completedDiagnostics} | Personnalite {$completedPersonalities}")
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('success')
                ->chart([$completedDiagnostics, $completedPersonalities, $completedDiagnostics + $completedPersonalities])
                ->extraAttributes([
                    'class' => 'stat-card stat-card-success',
                ]),

                Stat::make('Rapports prets', number_format($reportsReady))
                ->description('Eleves avec diagnostic et personnalite')
                ->descriptionIcon('heroicon-m-document-chart-bar')
                ->icon('heroicon-o-document-chart-bar')
                ->color('gray')
                ->extraAttributes([
                    'class' => 'stat-card stat-card-neutral',
                ]),
        ];
    }

    protected function getColumns(): array
    {
        return [
            'default' => 1,
            'sm' => 2,
            'lg' => 3,
            '2xl' => 4,
        ];
    }

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user && ($user->isSuperAdmin() || $user->isTeacher());
    }

    private function reportsReady(): int
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

    private function monthlyPostsChart(?string $authorId = null): array
    {
        return collect(range(5, 0))
            ->map(function (int $monthsAgo) use ($authorId): int {
                return Post::query()
                    ->when($authorId, fn (Builder $query): Builder => $query->where('author_id', $authorId))
                    ->whereBetween('created_at', [
                        now()->subMonths($monthsAgo)->startOfMonth(),
                        now()->subMonths($monthsAgo)->endOfMonth(),
                    ])
                    ->count();
            })
            ->values()
            ->all();
    }

    private function monthlyResourcesChart(?string $teacherId = null): array
    {
        if (! Schema::hasTable('resource_contents')) {
            return [0, 0, 0, 0, 0, 0];
        }

        return collect(range(5, 0))
            ->map(function (int $monthsAgo) use ($teacherId): int {
                return ResourceContent::query()
                    ->when($teacherId, fn (Builder $query): Builder => $query->where('teacher_id', $teacherId))
                    ->whereBetween('created_at', [
                        now()->subMonths($monthsAgo)->startOfMonth(),
                        now()->subMonths($monthsAgo)->endOfMonth(),
                    ])
                    ->count();
            })
            ->values()
            ->all();
    }
}
