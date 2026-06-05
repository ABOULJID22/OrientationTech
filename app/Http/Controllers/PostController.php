<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\PostView;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController extends Controller
{
    /**
     * Afficher la liste des articles
     */
    public function index(Request $request): View
    {
        $locale = app()->getLocale();
        $fallback = config('app.fallback_locale');

        $query = Post::query()
            ->published()
            ->with([
                'author', 
                'category', 
                'translations' => function ($q) use ($locale, $fallback) {
                    $q->whereIn('locale', [$locale, $fallback]);
                }, 
                'category.translations' => function ($q) use ($locale, $fallback) {
                    $q->whereIn('locale', [$locale, $fallback]);
                }
            ]);

        // Filtre catégorie par slug (localisé)
        if ($request->filled('category')) {
            $slug = $request->query('category');
            $query->whereHas('category', function ($q) use ($slug, $locale, $fallback) {
                $q->where('slug', $slug)
                  ->orWhereHas('translations', function ($qq) use ($slug, $locale, $fallback) {
                      $qq->whereIn('locale', [$locale, $fallback])
                         ->where('slug', $slug);
                  });
            });
        }

        // Recherche plein texte (localisée)
        if ($request->filled('search')) {
            $s = $request->query('search');
            $query->where(function ($q) use ($s, $locale, $fallback) {
                // Recherche dans la table principale
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('content', 'like', "%{$s}%")
                  // Recherche dans les traductions
                  ->orWhereHas('translations', function ($qq) use ($s, $locale, $fallback) {
                      $qq->whereIn('locale', [$locale, $fallback])
                         ->where(function ($qqq) use ($s) {
                             $qqq->where('title', 'like', "%{$s}%")
                                 ->orWhere('content', 'like', "%{$s}%");
                         });
                  });
            });
        }

        // Tri
        $sort = $request->query('sort', 'recent');
        match ($sort) {
            default => $query->orderByDesc('published_at'),
        };

        // Pagination
        $posts = $query->paginate(9)->withQueryString();

        // Catégories pour la barre de filtre (localisées)
        $categories = Category::with(['translations' => function ($q) use ($locale, $fallback) {
            $q->whereIn('locale', [$locale, $fallback]);
        }])
        ->get(['id', 'name', 'slug'])
        ->sortBy(fn ($c) => mb_strtolower($c->name));

        return view('pages.blog.index', compact('posts', 'categories'));
    }

    /**
     * Afficher un seul article
     */
    public function show(Request $request, Post $post): View
    {
        $locale = app()->getLocale();
        $fallback = config('app.fallback_locale');
        
        // Vérifier que l'article est publié
        if ($post->status !== 'published' || !$post->published_at || $post->published_at->isFuture()) {
            abort(404);
        }

        $this->recordView($request, $post);

        // Charger les relations nécessaires
        $post->load([
            'author',
            'category', 
            'likes',
            'visibleComments.user',
            'visibleComments.replies.user',
            'visibleComments.replies.reports',
            'translations' => function ($q) use ($locale, $fallback) {
                $q->whereIn('locale', [$locale, $fallback]);
            }, 
            'category.translations' => function ($q) use ($locale, $fallback) {
                $q->whereIn('locale', [$locale, $fallback]);
            }
        ]);

        // Articles précédent et suivant
        $prev = Post::query()
            ->published()
            ->where('published_at', '<', $post->published_at)
            ->with(['translations' => function ($q) use ($locale, $fallback) {
                $q->whereIn('locale', [$locale, $fallback]);
            }])
            ->orderByDesc('published_at')
            ->first();

        $next = Post::query()
            ->published()
            ->where('published_at', '>', $post->published_at)
            ->with(['translations' => function ($q) use ($locale, $fallback) {
                $q->whereIn('locale', [$locale, $fallback]);
            }])
            ->orderBy('published_at')
            ->first();

        // Articles récents
        $recent = Post::query()
            ->published()
            ->whereKeyNot($post->getKey())
            ->with(['translations' => function ($q) use ($locale, $fallback) {
                $q->whereIn('locale', [$locale, $fallback]);
            }])
            ->orderByDesc('published_at')
            ->limit(4)
            ->get(['id','slug','title','cover_image','published_at','category_id']);

        $comments = $post->visibleComments
            ->whereNull('parent_id')
            ->sortBy('created_at')
            ->values();
        $likedByCurrentUser = $post->isLikedBy(auth()->user());
        $favoritedByCurrentUser = $post->isFavoritedBy(auth()->user());

        return view('pages.blog.show', compact('post', 'prev', 'next', 'recent', 'comments', 'likedByCurrentUser', 'favoritedByCurrentUser'));
    }

    private function recordView(Request $request, Post $post): void
    {
        $windowStart = now()->subMinutes(30);
        $user = $request->user();
        $sessionId = $request->session()->getId();
        $ipHash = hash('sha256', (string) $request->ip());
        $agentHash = hash('sha256', (string) $request->userAgent());

        $recentViewExists = PostView::query()
            ->where('post_id', $post->id)
            ->where('viewed_at', '>=', $windowStart)
            ->where(function ($query) use ($user, $sessionId, $ipHash): void {
                if ($user) {
                    $query->where('user_id', $user->id);
                    return;
                }

                $query->where('session_id', $sessionId)
                    ->orWhere('ip_hash', $ipHash);
            })
            ->exists();

        if ($recentViewExists) {
            return;
        }

        $post->views()->create([
            'user_id' => $user?->id,
            'session_id' => $sessionId,
            'ip_hash' => $ipHash,
            'user_agent_hash' => $agentHash,
            'viewed_at' => now(),
        ]);

        $post->increment('views_count');
    }
}
