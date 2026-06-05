<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostCommentReport;
use App\Services\Notifications\PlatformNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class PostInteractionController extends Controller
{
    public function toggleLike(Post $post): RedirectResponse
    {
        Gate::authorize('interact', $post);

        $user = auth()->user();

        $liked = false;

        DB::transaction(function () use ($post, $user, &$liked): void {
            $existing = $post->likes()
                ->where('user_id', $user->id)
                ->first();

            if ($existing) {
                $existing->delete();
                $post->decrement('likes_count');
                return;
            }

            $post->likes()->create(['user_id' => $user->id]);
            $post->increment('likes_count');
            $liked = true;
        });

        if ($liked) {
            app(PlatformNotificationService::class)->notifyPostInteraction($post, $user, 'like');
        }

        return back();
    }

    public function toggleFavorite(Post $post): RedirectResponse
    {
        Gate::authorize('interact', $post);

        $user = auth()->user();

        $favorited = false;

        DB::transaction(function () use ($post, $user, &$favorited): void {
            $existing = $post->favorites()
                ->where('user_id', $user->id)
                ->first();

            if ($existing) {
                $existing->delete();
                $post->decrement('favorites_count');
                return;
            }

            $post->favorites()->create(['user_id' => $user->id]);
            $post->increment('favorites_count');
            $favorited = true;
        });

        if ($favorited) {
            app(PlatformNotificationService::class)->notifyPostInteraction($post, $user, 'favorite');
        }

        return back();
    }

    public function storeComment(Request $request, Post $post): RedirectResponse
    {
        Gate::authorize('interact', $post);

        $validated = $request->validate([
            'content' => ['required', 'string', 'min:3', 'max:2000'],
            'parent_id' => ['nullable', 'integer', 'exists:post_comments,id'],
        ]);

        $parentId = $validated['parent_id'] ?? null;

        if ($parentId) {
            $parent = PostComment::query()
                ->where('post_id', $post->id)
                ->whereNull('parent_id')
                ->visible()
                ->findOrFail($parentId);

            $parentId = $parent->id;
        }

        $comment = $post->comments()->create([
            'user_id' => auth()->id(),
            'parent_id' => $parentId,
            'content' => $validated['content'],
            'status' => PostComment::STATUS_VISIBLE,
        ]);

        app(PlatformNotificationService::class)->notifyPostInteraction(
            $post,
            auth()->user(),
            $parentId ? 'reply' : 'comment',
            $comment->loadMissing('parent.user'),
        );

        return back()->with('success', 'Commentaire ajoute avec succes.');
    }

    public function updateComment(Request $request, PostComment $comment): RedirectResponse
    {
        Gate::authorize('update', $comment);

        $validated = $request->validate([
            'content' => ['required', 'string', 'min:3', 'max:2000'],
        ]);

        $comment->update([
            'content' => $validated['content'],
        ]);

        return back()->with('success', 'Commentaire modifie.');
    }

    public function deleteComment(PostComment $comment): RedirectResponse
    {
        Gate::authorize('delete', $comment);

        if (auth()->user()?->isTeacher() && $comment->user_id !== auth()->id()) {
            $comment->update([
                'status' => PostComment::STATUS_HIDDEN,
                'hidden_by' => auth()->id(),
                'hidden_at' => now(),
            ]);

            return back()->with('success', 'Commentaire masque.');
        }

        $comment->delete();

        return back()->with('success', 'Commentaire supprime.');
    }

    public function reportComment(Request $request, PostComment $comment): RedirectResponse
    {
        Gate::authorize('report', $comment);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:120'],
            'details' => ['nullable', 'string', 'max:1000'],
        ]);

        $report = PostCommentReport::query()->updateOrCreate(
            [
                'post_comment_id' => $comment->id,
                'reporter_id' => auth()->id(),
            ],
            [
                'reason' => $validated['reason'],
                'details' => $validated['details'] ?? null,
                'status' => PostCommentReport::STATUS_PENDING,
            ]
        );

        app(PlatformNotificationService::class)->notifyPostCommentReported($report);

        return back()->with('success', 'Signalement envoye.');
    }
}
