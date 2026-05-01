<?php

namespace App\Filament\Widgets;

use App\Models\Contact;
use App\Models\Post;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BlogStats extends BaseWidget
{
    protected static ?int $sort = 1;

    // The widget will refresh every 10 seconds, which also updates chart data.
    protected ?string $pollingInterval = '10s';

    protected function getStats(): array
    {
        $totalUsers = User::count();
        $totalPosts = Post::count();
        $totalContacts = Contact::count();
        $clientUsers = User::whereHas('roles', fn ($q) => $q->where('name', 'client'))->count();
        $supportRequests = Contact::where('user_type', 'client')->count();

        return [
                Stat::make(__('widgets.blog.users'), number_format($totalUsers))
                ->description(__('widgets.blog.users_description'))
                ->descriptionIcon('heroicon-m-user-group')
                ->icon('heroicon-o-user-group')
                ->color('info')
                ->chart([7, 2, 10, 3, 15, 4, 17, 10, 12, 18, 5, 20])
                ->url(route('filament.admin.resources.users.index'))
                ->extraAttributes([
                    'class' => 'stat-card stat-card-info',
                ]),

                Stat::make(__('widgets.blog.client_users'), number_format($clientUsers))
                ->description(__('widgets.blog.client_users_description'))
                ->descriptionIcon('heroicon-m-building-storefront')
                ->icon('heroicon-o-building-storefront')
                ->color('success')
                ->chart([2, 5, 3, 7, 6, 9, 8, 11, 10, 13, 12, 15])
                ->url(route('filament.admin.resources.users.index'))
                ->extraAttributes([
                    'class' => 'stat-card stat-card-success',
                ]),

                Stat::make(__('widgets.blog.posts'), number_format($totalPosts))
                ->description(__('widgets.blog.posts_description'))
                ->descriptionIcon('heroicon-m-document-text')
                ->icon('heroicon-o-document-text')
                ->color('primary')
                ->chart([1, 2, 4, 3, 5, 6, 8, 7, 9, 10, 11, 12])
                ->url(route('filament.admin.resources.posts.index'))
                ->extraAttributes([
                    'class' => 'stat-card stat-card-primary',
                ]),

                Stat::make(__('widgets.blog.support_requests'), number_format($supportRequests))
                ->description(__('widgets.blog.support_requests_description'))
                ->descriptionIcon('heroicon-m-lifebuoy')
                ->icon('heroicon-o-lifebuoy')
                ->color('gray')
                ->chart([4, 6, 8, 5, 10, 7, 8, 9, 11, 13, 12, 14])
                ->extraAttributes([
                    'class' => 'stat-card stat-card-neutral',
                ]),

                Stat::make(__('widgets.blog.contacts'), number_format($totalContacts))
                ->description(__('widgets.blog.contacts_description'))
                ->descriptionIcon('heroicon-m-inbox')
                ->icon('heroicon-o-inbox')
                ->color('primary')
                ->chart([3, 5, 4, 6, 7, 8, 10, 9, 12, 11, 14, 13])
                ->url(route('filament.admin.resources.contacts.index'))
                ->extraAttributes([
                    'class' => 'stat-card stat-card-primary',
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
        return $user && ($user->isSuperAdmin() || $user->isAssistant());
    }
}

