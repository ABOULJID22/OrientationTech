<?php

namespace App\Http\Controllers;

use App\Models\ResourceContent;
use App\Services\Notifications\PlatformNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ResourceContentController extends Controller
{
    public function show(ResourceContent $resourceContent): View
    {
        abort_unless(
            $resourceContent->status === ResourceContent::STATUS_PUBLISHED
            && (! $resourceContent->published_at || $resourceContent->published_at->lte(now())),
            404
        );

        $resourceContent->increment('views_count');

        $recent = ResourceContent::query()
            ->published()
            ->whereKeyNot($resourceContent->getKey())
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('pages.resources.show', [
            'resourceContent' => $resourceContent,
            'recentResources' => $recent,
            'liked' => $resourceContent->isLikedBy(auth()->user()),
            'favorited' => $resourceContent->isFavoritedBy(auth()->user()),
        ]);
    }

    public function like(ResourceContent $resourceContent): RedirectResponse
    {
        $this->ensurePublished($resourceContent);

        $liked = false;

        DB::transaction(function () use ($resourceContent, &$liked): void {
            $like = $resourceContent->likes()
                ->where('user_id', auth()->id())
                ->first();

            if ($like) {
                $like->delete();
                $resourceContent->decrement('likes_count');
                return;
            }

            $resourceContent->likes()->create(['user_id' => auth()->id()]);
            $resourceContent->increment('likes_count');
            $liked = true;
        });

        if ($liked && auth()->user()) {
            app(PlatformNotificationService::class)->notifyResourceInteraction($resourceContent, auth()->user(), 'like');
        }

        return back();
    }

    public function favorite(ResourceContent $resourceContent): RedirectResponse
    {
        $this->ensurePublished($resourceContent);

        $favorited = false;

        DB::transaction(function () use ($resourceContent, &$favorited): void {
            $favorite = $resourceContent->favorites()
                ->where('user_id', auth()->id())
                ->first();

            if ($favorite) {
                $favorite->delete();
                $resourceContent->decrement('favorites_count');
                return;
            }

            $resourceContent->favorites()->create(['user_id' => auth()->id()]);
            $resourceContent->increment('favorites_count');
            $favorited = true;
        });

        if ($favorited && auth()->user()) {
            app(PlatformNotificationService::class)->notifyResourceInteraction($resourceContent, auth()->user(), 'favorite');
        }

        return back();
    }

    private function ensurePublished(ResourceContent $resourceContent): void
    {
        abort_unless(
            $resourceContent->status === ResourceContent::STATUS_PUBLISHED
            && (! $resourceContent->published_at || $resourceContent->published_at->lte(now())),
            404
        );
    }
}
