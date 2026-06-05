<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\DomainComment;
use App\Models\DomainCommentReport;
use App\Models\DomainView;
use App\Services\Notifications\PlatformNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DomainExplorerController extends Controller
{
    public function index(Request $request): View
    {
        $query = Domain::query()->active();
        $search = trim((string) $request->query('search'));
        $locales = array_keys(config('orientationtech.supported_locales', ['fr' => 'Francais', 'en' => 'English']));

        if ($search !== '') {
            $query->where(function ($query) use ($search, $locales): void {
                foreach (['name', 'short_description', 'full_description', 'keywords'] as $column) {
                    foreach ($locales as $locale) {
                        $query->orWhere("{$column}->{$locale}", 'like', "%{$search}%");
                    }
                }

                foreach (['category'] as $column) {
                    $query->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        foreach (['category', 'difficulty_level', 'future_potential', 'ai_impact'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->query($filter));
            }
        }

        if ($request->boolean('freelance')) {
            $query->where('freelance_opportunity', '>=', 4);
        }

        if ($request->boolean('remote')) {
            $query->where('remote_opportunity', '>=', 4);
        }

        match ($request->query('sort', 'recent')) {
            'likes' => $query->orderByDesc('likes_count'),
            'views' => $query->orderByDesc('views_count'),
            'comments' => $query->orderByDesc('comments_count'),
            'rating' => $query->orderByDesc('rating_average'),
            'junior_salary' => $query->orderByDesc('junior_salary_max'),
            'senior_salary' => $query->orderByDesc('senior_salary_max'),
            'easy' => $query->orderByRaw("FIELD(difficulty_level, 'Facile', 'Moyen', 'Avancé', 'Expert')"),
            default => $query->orderBy('display_order')->latest(),
        };

        $domains = $query->paginate(12)->withQueryString();

        return view('pages.domains.index', [
            'domains' => $domains,
            'categories' => Domain::query()->active()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category'),
            'difficultyLevels' => Domain::query()->active()->whereNotNull('difficulty_level')->distinct()->pluck('difficulty_level'),
            'futurePotentials' => Domain::query()->active()->whereNotNull('future_potential')->distinct()->pluck('future_potential'),
            'aiImpacts' => Domain::query()->active()->whereNotNull('ai_impact')->distinct()->pluck('ai_impact'),
            'mostLiked' => Domain::query()->active()->orderByDesc('likes_count')->limit(4)->get(),
            'mostViewed' => Domain::query()->active()->orderByDesc('views_count')->limit(4)->get(),
            'bestRated' => Domain::query()->active()->where('ratings_count', '>', 0)->orderByDesc('rating_average')->limit(4)->get(),
            'futureDomains' => Domain::query()->active()->whereIn('future_potential', ['Très élevé', 'Excellent', 'Très fort'])->limit(4)->get(),
        ]);
    }

    public function show(Request $request, Domain $domain): View
    {
        Gate::authorize('view', $domain);
        $this->recordView($request, $domain);

        $domain->load(['comments.user', 'comments.replies.user']);

        return view('pages.domains.show', [
            'domain' => $domain,
            'comments' => $domain->comments()->visible()->whereNull('parent_id')->with(['user', 'replies.user'])->oldest()->get(),
            'liked' => $domain->isLikedBy(auth()->user()),
            'favorited' => $domain->isFavoritedBy(auth()->user()),
            'myRating' => $domain->ratingBy(auth()->user()),
            'relatedDomains' => Domain::query()->active()->where('category', $domain->category)->whereKeyNot($domain->id)->limit(3)->get(),
        ]);
    }

    public function compare(Request $request): View
    {
        $input = $request->query('domains', []);
        $ids = collect(is_array($input) ? $input : explode(',', (string) $input))
            ->filter()
            ->take(3)
            ->all();

        $domains = Domain::query()->active()->whereIn('id', $ids)->get();

        return view('pages.domains.compare', [
            'domains' => $domains,
            'allDomains' => Domain::query()->active()->get(['id', 'name'])->sortBy('name'),
        ]);
    }

    public function like(Domain $domain): RedirectResponse
    {
        Gate::authorize('interact', $domain);

        DB::transaction(function () use ($domain): void {
            $like = $domain->likes()->where('user_id', auth()->id())->first();
            if ($like) {
                $like->delete();
                $domain->decrement('likes_count');
                return;
            }

            $domain->likes()->create(['user_id' => auth()->id()]);
            $domain->increment('likes_count');
        });

        return back();
    }

    public function favorite(Domain $domain): RedirectResponse
    {
        Gate::authorize('interact', $domain);

        $favorite = $domain->favorites()->where('user_id', auth()->id())->first();
        $favorite ? $favorite->delete() : $domain->favorites()->create(['user_id' => auth()->id()]);

        return back();
    }

    public function rate(Request $request, Domain $domain): RedirectResponse
    {
        Gate::authorize('interact', $domain);
        $validated = $request->validate(['rating' => ['required', 'integer', 'min:1', 'max:5']]);

        $domain->ratings()->updateOrCreate(['user_id' => auth()->id()], ['rating' => $validated['rating']]);
        $domain->forceFill([
            'ratings_count' => $domain->ratings()->count(),
            'rating_average' => round((float) $domain->ratings()->avg('rating'), 2),
        ])->saveQuietly();

        return back();
    }

    public function storeComment(Request $request, Domain $domain): RedirectResponse
    {
        Gate::authorize('interact', $domain);
        $validated = $request->validate([
            'content' => ['required', 'string', 'min:3', 'max:2000'],
            'parent_id' => ['nullable', 'integer', 'exists:domain_comments,id'],
        ]);

        $comment = $domain->comments()->create([
            'user_id' => auth()->id(),
            'parent_id' => $validated['parent_id'] ?? null,
            'content' => $validated['content'],
            'status' => DomainComment::STATUS_VISIBLE,
        ]);

        app(PlatformNotificationService::class)->notifyDomainCommentCreated($comment);

        return back()->with('success', 'Commentaire ajoute.');
    }

    public function updateComment(Request $request, DomainComment $comment): RedirectResponse
    {
        Gate::authorize('update', $comment);
        $validated = $request->validate(['content' => ['required', 'string', 'min:3', 'max:2000']]);
        $comment->update(['content' => $validated['content']]);

        return back()->with('success', 'Commentaire modifie.');
    }

    public function deleteComment(DomainComment $comment): RedirectResponse
    {
        Gate::authorize('delete', $comment);
        $comment->delete();

        return back()->with('success', 'Commentaire supprime.');
    }

    public function reportComment(Request $request, DomainComment $comment): RedirectResponse
    {
        Gate::authorize('report', $comment);
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:120'],
            'details' => ['nullable', 'string', 'max:1000'],
        ]);

        $report = DomainCommentReport::updateOrCreate(
            ['comment_id' => $comment->id, 'user_id' => auth()->id()],
            ['reason' => $validated['reason'], 'details' => $validated['details'] ?? null, 'status' => 'pending']
        );
        $comment->update(['status' => DomainComment::STATUS_REPORTED]);

        app(PlatformNotificationService::class)->notifyDomainCommentReported($report);

        return back()->with('success', 'Signalement envoye.');
    }

    private function recordView(Request $request, Domain $domain): void
    {
        $recent = DomainView::query()
            ->where('domain_id', $domain->id)
            ->where('viewed_at', '>=', now()->subMinutes(30))
            ->where(function ($query) use ($request): void {
                if ($request->user()) {
                    $query->where('user_id', $request->user()->id);
                    return;
                }
                $query->where('session_id', $request->session()->getId())
                    ->orWhere('ip_hash', hash('sha256', (string) $request->ip()));
            })
            ->exists();

        if ($recent) {
            return;
        }

        $domain->views()->create([
            'user_id' => $request->user()?->id,
            'session_id' => $request->session()->getId(),
            'ip_hash' => hash('sha256', (string) $request->ip()),
            'viewed_at' => now(),
        ]);
        $domain->increment('views_count');
    }
}
