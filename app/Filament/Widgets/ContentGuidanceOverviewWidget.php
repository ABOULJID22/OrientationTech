<?php

namespace App\Filament\Widgets;

use App\Models\Contact;
use App\Models\Post;
use App\Models\ResourceContent;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class ContentGuidanceOverviewWidget extends Widget
{
    protected static ?int $sort = 6;

    protected string $view = 'filament.widgets.content-guidance-overview-widget';

    protected int | string | array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $user = auth()->user();
        $isTeacher = (bool) ($user?->isTeacher() && ! $user?->isSuperAdmin());

        return [
            'recentPosts' => Post::query()
                ->when($isTeacher, fn (Builder $query): Builder => $query->where('author_id', $user->id))
                ->latest()
                ->limit(5)
                ->get(['id', 'title', 'created_at']),
            'recentResources' => Schema::hasTable('resource_contents')
                ? ResourceContent::query()
                    ->when($isTeacher, fn (Builder $query): Builder => $query->where('teacher_id', $user->id))
                    ->latest()
                    ->limit(5)
                    ->get(['id', 'type', 'title', 'created_at'])
                : collect(),
            'pendingContacts' => Contact::query()
                ->whereNull('replied_at')
                ->latest()
                ->limit(5)
                ->get(['id', 'name', 'email', 'user_type', 'created_at']),
        ];
    }

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user && ($user->isSuperAdmin() || $user->isTeacher());
    }
}
