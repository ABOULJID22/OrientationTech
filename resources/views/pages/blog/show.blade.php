<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $post->translation()?->title ?? $post->title }} · OrientationTech</title>
  @include('layouts.theme-init')
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  @include('layouts.favicon')

</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
 @include('layouts.navbar')

  <main class="min-h-screen py-10 sm:py-14">
    <div class="max-w-5xl mx-auto px-6 lg:px-8">

      <!-- Fil d'Ariane -->
      <nav aria-label="{{ __('site.aria.breadcrumb') }}" class="mb-8 text-sm text-gray-500 dark:text-gray-400">
        <ol class="flex items-center gap-2">
          <li>
            <a href="{{ route('pages.blog.index') }}" class="link-underline-soft">
              {{ __('site.nav.blog') }}
            </a>
          </li>
          <li class="opacity-60">/</li>
          <li class="line-clamp-1">{{ $post->title }}</li>
        </ol>
      </nav>

      @php
        $shareHeading = __('site.blog.share.facebook');
        $shareHeading = $shareHeading === 'site.blog.share.facebook' ? 'Partager' : $shareHeading;
      @endphp
      <!-- Image principale -->
      @php
        $img = $post->cover_image ? Storage::url($post->cover_image) : asset('images/img1.jpg');
      @endphp
      <figure class="relative overflow-hidden rounded-[2.5rem] shadow-soft ring-1 ring-gray-200/70 dark:ring-gray-800/60 mb-12 group">
  <img src="{{ $img }}" alt="{{ $post->translation()?->title ?? $post->title }}" loading="lazy" decoding="async"
             class="w-full aspect-[21/9] object-cover transition-transform duration-500 group-hover:scale-[1.02]">
        <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/30 via-black/0 to-transparent"></div>
        @if($post->category)
          <figcaption class="absolute bottom-3 left-3 text-white/90 text-xs md:backdrop-blur-sm px-2 py-1 rounded">
            {{ $post->category->name }}
          </figcaption>
        @endif
      </figure>

      <section class="mb-10 space-y-4">
        <div class="flex flex-wrap items-center gap-3">
          @if($post->category)
            <a href="{{ route('pages.blog.index', ['category' => $post->category->slug]) }}" class="badge-pill">
              {{ $post->category->name }}
            </a>
          @endif

          @if(!empty($post->reading_time))
            <span class="stat-pill">
              {{ trans_choice('site.blog.reading_minutes', (int) $post->reading_time, ['count' => (int) $post->reading_time]) }}
            </span>
          @endif
        </div>

        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-gray-900 dark:text-white">
          {{ $post->translation()?->title ?? $post->title }}
        </h1>

        <div class="flex flex-wrap items-center gap-3 text-sm text-gray-600 dark:text-gray-300">
          <span class="inline-flex items-center gap-2">
            <svg class="h-4 w-4 text-primary-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <polyline points="6 9 12 15 18 9" />
              <path d="M19.5 12a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z" />
            </svg>
            <span>{{ __('site.blog.Publishedon') }}</span>
            <span class="font-semibold text-gray-900 dark:text-white">{{ $post->published_at->format('F j, Y') }}</span>
          </span>
          <span class="stat-pill">{{ number_format((int) $post->views_count) }} vues</span>
          <span class="stat-pill">{{ number_format((int) $post->likes_count) }} likes</span>
          <span class="stat-pill">{{ number_format((int) $post->comments_count) }} commentaires</span>
          <span class="inline-flex items-center gap-2">
            @auth
              <form method="POST" action="{{ route('pages.blog.like', $post) }}" class="inline-flex">
                @csrf
                <button type="submit" aria-label="{{ $likedByCurrentUser ? 'Retirer mon like' : 'Aimer cet article' }}" title="{{ $likedByCurrentUser ? 'Retirer mon like' : 'Aimer cet article' }}" class="flex h-10 w-10 items-center justify-center rounded-full border p-0 leading-none shadow-sm transition hover:-translate-y-0.5 hover:shadow-md {{ $likedByCurrentUser ? 'border-red-500 bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-300' : 'border-[#bfdbfe] bg-white text-gray-700 hover:border-red-300 hover:text-red-600 dark:border-white/10 dark:bg-gray-800 dark:text-gray-200 dark:hover:text-red-300' }}">
                  <svg class="block h-5 w-5 shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M12 21s-7.2-4.55-9.45-9.15C.78 8.23 2.4 4.5 6.25 4.5c2.08 0 3.55 1.1 4.42 2.27.28.38.78.38 1.06 0C12.6 5.6 14.07 4.5 16.15 4.5c3.85 0 5.47 3.73 3.7 7.35C17.6 16.45 12 21 12 21Z" fill="{{ $likedByCurrentUser ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                </button>
              </form>
              <form method="POST" action="{{ route('pages.blog.favorite', $post) }}" class="inline-flex">
                @csrf
                <button type="submit" aria-label="{{ $favoritedByCurrentUser ? 'Retirer des favoris' : 'Sauvegarder cet article' }}" title="{{ $favoritedByCurrentUser ? 'Retirer des favoris' : 'Sauvegarder cet article' }}" class="flex h-10 w-10 items-center justify-center rounded-full border p-0 leading-none shadow-sm transition hover:-translate-y-0.5 hover:shadow-md {{ $favoritedByCurrentUser ? 'border-[#2563eb] bg-[#eff6ff] text-[#2563eb] dark:bg-blue-500/10 dark:text-blue-300' : 'border-[#bfdbfe] bg-white text-gray-700 hover:border-[#93c5fd] hover:text-[#2563eb] dark:border-white/10 dark:bg-gray-800 dark:text-gray-200 dark:hover:text-blue-300' }}">
                  <svg class="block h-5 w-5 shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M6.5 4.75A2.25 2.25 0 0 1 8.75 2.5h6.5a2.25 2.25 0 0 1 2.25 2.25v16.1a.65.65 0 0 1-1.02.53L12 18.25l-4.48 3.13a.65.65 0 0 1-1.02-.53V4.75Z" fill="{{ $favoritedByCurrentUser ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
                  </svg>
                </button>
              </form>
            @else
              <a href="{{ route('login') }}" aria-label="Se connecter pour aimer" title="Se connecter pour aimer" class="flex h-10 w-10 items-center justify-center rounded-full border border-[#bfdbfe] bg-white p-0 leading-none text-gray-700 shadow-sm transition hover:-translate-y-0.5 hover:border-red-300 hover:text-red-600 hover:shadow-md dark:border-white/10 dark:bg-gray-800 dark:text-gray-200 dark:hover:text-red-300">
                <svg class="block h-5 w-5 shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                  <path d="M12 21s-7.2-4.55-9.45-9.15C.78 8.23 2.4 4.5 6.25 4.5c2.08 0 3.55 1.1 4.42 2.27.28.38.78.38 1.06 0C12.6 5.6 14.07 4.5 16.15 4.5c3.85 0 5.47 3.73 3.7 7.35C17.6 16.45 12 21 12 21Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </a>
              <a href="{{ route('login') }}" aria-label="Se connecter pour sauvegarder" title="Se connecter pour sauvegarder" class="flex h-10 w-10 items-center justify-center rounded-full border border-[#bfdbfe] bg-white p-0 leading-none text-gray-700 shadow-sm transition hover:-translate-y-0.5 hover:border-[#93c5fd] hover:text-[#2563eb] hover:shadow-md dark:border-white/10 dark:bg-gray-800 dark:text-gray-200 dark:hover:text-blue-300">
                <svg class="block h-5 w-5 shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                  <path d="M6.5 4.75A2.25 2.25 0 0 1 8.75 2.5h6.5a2.25 2.25 0 0 1 2.25 2.25v16.1a.65.65 0 0 1-1.02.53L12 18.25l-4.48 3.13a.65.65 0 0 1-1.02-.53V4.75Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
                </svg>
              </a>
            @endauth
          </span>
        </div>

        <div class="hidden">
          @auth
            <form method="POST" action="{{ route('pages.blog.like', $post) }}">
              @csrf
              <button
                type="submit"
                aria-label="{{ $likedByCurrentUser ? 'Retirer mon like' : 'Aimer cet article' }}"
                title="{{ $likedByCurrentUser ? 'Retirer mon like' : 'Aimer cet article' }}"
                class="group inline-flex items-center gap-3 rounded-full border px-4 py-2 text-sm font-bold shadow-sm transition hover:-translate-y-0.5 hover:shadow-md {{ $likedByCurrentUser ? 'border-red-500 bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-300' : 'border-[#bfdbfe] bg-white text-gray-700 hover:border-red-300 hover:text-red-600 dark:border-white/10 dark:bg-gray-800 dark:text-gray-200 dark:hover:text-red-300' }}"
              >
                <span class="grid h-10 w-10 place-items-center rounded-full {{ $likedByCurrentUser ? 'bg-red-500 text-white' : 'bg-[#eff6ff] text-gray-500 group-hover:bg-red-50 group-hover:text-red-500 dark:bg-white/10' }} transition">
                  <svg class="h-6 w-6 transition duration-200 group-hover:scale-110 {{ $likedByCurrentUser ? 'scale-110' : '' }}" viewBox="0 0 24 24" aria-hidden="true">
                    <path
                      d="M12 21s-7.2-4.55-9.45-9.15C.78 8.23 2.4 4.5 6.25 4.5c2.08 0 3.55 1.1 4.42 2.27.28.38.78.38 1.06 0C12.6 5.6 14.07 4.5 16.15 4.5c3.85 0 5.47 3.73 3.7 7.35C17.6 16.45 12 21 12 21Z"
                      fill="{{ $likedByCurrentUser ? 'currentColor' : 'none' }}"
                      stroke="currentColor"
                      stroke-width="1.8"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    />
                  </svg>
                </span>
                <span class="leading-tight">
                  {{ $likedByCurrentUser ? 'Aimé' : 'Aimer' }}
                  <span class="block text-xs font-semibold opacity-70">{{ number_format((int) $post->likes_count) }} likes</span>
                </span>
              </button>
            </form>
            <form method="POST" action="{{ route('pages.blog.favorite', $post) }}" class="mt-3">
              @csrf
              <button
                type="submit"
                aria-label="{{ $favoritedByCurrentUser ? 'Retirer des favoris' : 'Sauvegarder cet article' }}"
                title="{{ $favoritedByCurrentUser ? 'Retirer des favoris' : 'Sauvegarder cet article' }}"
                class="flex h-12 w-12 items-center justify-center rounded-full border p-0 leading-none shadow-sm transition hover:-translate-y-0.5 hover:shadow-md {{ $favoritedByCurrentUser ? 'border-[#2563eb] bg-[#eff6ff] text-[#2563eb] dark:bg-blue-500/10 dark:text-blue-300' : 'border-[#bfdbfe] bg-white text-gray-700 hover:border-[#93c5fd] hover:text-[#2563eb] dark:border-white/10 dark:bg-gray-800 dark:text-gray-200 dark:hover:text-blue-300' }}"
              >
                <svg class="block h-6 w-6 shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                  <path d="M6.5 4.75A2.25 2.25 0 0 1 8.75 2.5h6.5a2.25 2.25 0 0 1 2.25 2.25v16.1a.65.65 0 0 1-1.02.53L12 18.25l-4.48 3.13a.65.65 0 0 1-1.02-.53V4.75Z" fill="{{ $favoritedByCurrentUser ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
                </svg>
              </button>
            </form>
          @else
            <a href="{{ route('login') }}" class="inline-flex items-center rounded-full bg-[#2563eb] px-5 py-2 text-sm font-bold text-white shadow-sm hover:bg-[#1d4ed8]">
              Connecte-toi pour aimer et commenter
            </a>
          @endauth
        </div>
      </section>

      <!-- Contenu -->
      @php
        $content = $post->translation()?->content ?? $post->content;
      @endphp
      @if($content)
        @php
          $renderedContent = \Filament\Forms\Components\RichEditor\RichContentRenderer::make($content)->toHtml();
        @endphp
        <article class="prose prose-slate dark:prose-invert fi-prose max-w-none prose-headings:scroll-mt-24">
          {!! $renderedContent !!}
        </article>
      @else
        <div class="bg-gray-100 dark:bg-gray-800 rounded-2xl p-8 text-center">
          <p class="text-gray-500 dark:text-white italic">
            {{ __('site.blog.no_content') }}
          </p>
        </div>
      @endif

      <!-- Tags (activer si relation tags existe) -->
      @isset($post->tags)
        @if($post->tags->count())
          <div class="mt-10 flex flex-wrap gap-2">
            @foreach ($post->tags as $tag)
              <a href="{{ route('pages.blog.index', ['search' => $tag->name]) }}"
                 class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-gray-100 text-gray-700 hover:bg-primary-500 hover:text-white transition-colors dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-primary-400">
                #{{ $tag->name }}
              </a>
            @endforeach
          </div>
        @endif
      @endisset

      <section class="mt-12 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-white/10">
        <div class="mb-6 flex items-center justify-between gap-4">
          <div>
            <h2 class="text-2xl font-bold text-gray-950 dark:text-white">Commentaires</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pose une question, partage ton avis ou reponds a un autre eleve.</p>
          </div>
          <span class="rounded-full bg-[#eff6ff] px-3 py-1 text-sm font-bold text-[#2563eb]">{{ $comments->count() }}</span>
        </div>

        @if(session('success'))
          <div class="mb-5 rounded-xl bg-emerald-50 p-4 text-sm font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">
            {{ session('success') }}
          </div>
        @endif

        @auth
          <form method="POST" action="{{ route('pages.blog.comments.store', $post) }}" class="mb-8 space-y-3">
            @csrf
            <textarea name="content" rows="4" required maxlength="2000" class="w-full rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-900 focus:border-[#2563eb] focus:outline-none focus:ring-2 focus:ring-[#2563eb]/20 dark:border-white/10 dark:bg-gray-900 dark:text-white" placeholder="Ecris ton commentaire...">{{ old('content') }}</textarea>
            @error('content')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            <button type="submit" class="rounded-lg bg-[#2563eb] px-5 py-2 text-sm font-bold text-white hover:bg-[#1d4ed8]">
              Publier le commentaire
            </button>
          </form>
        @else
          <div class="mb-8 rounded-xl bg-[#eff6ff] p-4 text-sm text-[#1e40af]">
            Connecte-toi pour ajouter un commentaire ou repondre.
          </div>
        @endauth

        <div class="space-y-5">
          @forelse($comments as $comment)
            <div class="rounded-xl border border-gray-200 p-4 dark:border-white/10">
              <div class="flex items-start justify-between gap-4">
                <div>
                  <p class="font-bold text-gray-950 dark:text-white">{{ $comment->user?->name ?? 'Utilisateur' }}</p>
                  <p class="mt-1 text-xs text-gray-500">{{ $comment->created_at?->format('d/m/Y H:i') }}</p>
                </div>
              </div>

              <p class="mt-3 whitespace-pre-line text-sm leading-6 text-gray-700 dark:text-gray-200">{{ $comment->content }}</p>

              @auth
                <div class="mt-4 flex flex-wrap gap-2">
                  <details class="rounded-lg bg-gray-50 px-3 py-2 text-sm dark:bg-gray-900">
                    <summary class="cursor-pointer font-semibold text-[#2563eb]">Repondre</summary>
                    <form method="POST" action="{{ route('pages.blog.comments.store', $post) }}" class="mt-3 space-y-2">
                      @csrf
                      <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                      <textarea name="content" rows="3" required maxlength="2000" class="w-full rounded-lg border border-gray-200 bg-white p-3 text-sm dark:border-white/10 dark:bg-gray-800"></textarea>
                      <button class="rounded-lg bg-[#2563eb] px-4 py-2 text-xs font-bold text-white">Envoyer</button>
                    </form>
                  </details>

                  @if(auth()->id() === $comment->user_id || auth()->user()?->isSuperAdmin())
                    <details class="rounded-lg bg-gray-50 px-3 py-2 text-sm dark:bg-gray-900">
                      <summary class="cursor-pointer font-semibold text-gray-700 dark:text-gray-200">Modifier</summary>
                      <form method="POST" action="{{ route('pages.blog.comments.update', $comment) }}" class="mt-3 space-y-2">
                        @csrf
                        @method('PATCH')
                        <textarea name="content" rows="3" required maxlength="2000" class="w-full rounded-lg border border-gray-200 bg-white p-3 text-sm dark:border-white/10 dark:bg-gray-800">{{ $comment->content }}</textarea>
                        <button class="rounded-lg bg-[#2563eb] px-4 py-2 text-xs font-bold text-white">Enregistrer</button>
                      </form>
                    </details>
                  @endif

                  @if(auth()->id() !== $comment->user_id)
                    <details class="rounded-lg bg-gray-50 px-3 py-2 text-sm dark:bg-gray-900">
                      <summary class="cursor-pointer font-semibold text-amber-600">Signaler</summary>
                      <form method="POST" action="{{ route('pages.blog.comments.report', $comment) }}" class="mt-3 space-y-2">
                        @csrf
                        <input name="reason" required maxlength="120" class="w-full rounded-lg border border-gray-200 bg-white p-3 text-sm dark:border-white/10 dark:bg-gray-800" placeholder="Motif du signalement">
                        <textarea name="details" rows="2" maxlength="1000" class="w-full rounded-lg border border-gray-200 bg-white p-3 text-sm dark:border-white/10 dark:bg-gray-800" placeholder="Details optionnels"></textarea>
                        <button class="rounded-lg bg-amber-500 px-4 py-2 text-xs font-bold text-white">Signaler</button>
                      </form>
                    </details>
                  @endif

                  @if(auth()->id() === $comment->user_id || auth()->user()?->isSuperAdmin() || (auth()->user()?->isTeacher() && $post->author_id === auth()->id()))
                    <form method="POST" action="{{ route('pages.blog.comments.delete', $comment) }}">
                      @csrf
                      @method('DELETE')
                      <button class="rounded-lg bg-red-50 px-3 py-2 text-sm font-semibold text-red-600 hover:bg-red-100 dark:bg-red-500/10 dark:text-red-300">Supprimer</button>
                    </form>
                  @endif
                </div>
              @endauth

              @if($comment->replies->count())
                <div class="mt-4 space-y-3 border-l-2 border-[#bfdbfe] pl-4">
                  @foreach($comment->replies as $reply)
                    <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-900">
                      <p class="text-sm font-bold text-gray-950 dark:text-white">{{ $reply->user?->name ?? 'Utilisateur' }}</p>
                      <p class="mt-1 whitespace-pre-line text-sm text-gray-700 dark:text-gray-200">{{ $reply->content }}</p>

                      @auth
                        <div class="mt-3 flex flex-wrap gap-2">
                          @if(auth()->id() === $reply->user_id || auth()->user()?->isSuperAdmin())
                            <details class="rounded-lg bg-white px-3 py-2 text-xs dark:bg-gray-800">
                              <summary class="cursor-pointer font-semibold text-gray-700 dark:text-gray-200">Modifier</summary>
                              <form method="POST" action="{{ route('pages.blog.comments.update', $reply) }}" class="mt-2 space-y-2">
                                @csrf
                                @method('PATCH')
                                <textarea name="content" rows="2" required maxlength="2000" class="w-full rounded-lg border border-gray-200 bg-white p-3 text-sm dark:border-white/10 dark:bg-gray-800">{{ $reply->content }}</textarea>
                                <button class="rounded-lg bg-[#2563eb] px-3 py-2 text-xs font-bold text-white">Enregistrer</button>
                              </form>
                            </details>
                          @endif

                          @if(auth()->id() !== $reply->user_id)
                            <details class="rounded-lg bg-white px-3 py-2 text-xs dark:bg-gray-800">
                              <summary class="cursor-pointer font-semibold text-amber-600">Signaler</summary>
                              <form method="POST" action="{{ route('pages.blog.comments.report', $reply) }}" class="mt-2 space-y-2">
                                @csrf
                                <input name="reason" required maxlength="120" class="w-full rounded-lg border border-gray-200 bg-white p-3 text-sm dark:border-white/10 dark:bg-gray-800" placeholder="Motif">
                                <button class="rounded-lg bg-amber-500 px-3 py-2 text-xs font-bold text-white">Signaler</button>
                              </form>
                            </details>
                          @endif

                          @if(auth()->id() === $reply->user_id || auth()->user()?->isSuperAdmin() || (auth()->user()?->isTeacher() && $post->author_id === auth()->id()))
                            <form method="POST" action="{{ route('pages.blog.comments.delete', $reply) }}">
                              @csrf
                              @method('DELETE')
                              <button class="rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-100 dark:bg-red-500/10 dark:text-red-300">Supprimer</button>
                            </form>
                          @endif
                        </div>
                      @endauth
                    </div>
                  @endforeach
                </div>
              @endif
            </div>
          @empty
            <p class="rounded-xl bg-gray-50 p-5 text-center text-sm text-gray-500 dark:bg-gray-900 dark:text-gray-400">Aucun commentaire pour le moment.</p>
          @endforelse
        </div>
      </section>

      <!-- Partage -->
      @php $url = route('pages.blog.show', $post); @endphp
      <div class="mt-12 border-t border-gray-200/60 dark:border-gray-800/70 pt-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
          <a href="{{ route('pages.blog.index') }}" class="link-underline-soft text-sm text-gray-600 dark:text-gray-300">
            ← {{ __('site.nav.blog') }}
          </a>

          <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:gap-5">
            <span class="uppercase tracking-[0.2em] text-xs text-gray-500 dark:text-gray-400">{{ $shareHeading }}</span>
            <div class="flex flex-wrap gap-2 sm:gap-3">
             <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($url) }}&text={{ urlencode($post->title) }}"
                 target="_blank" rel="noopener"
                 class="share-button bg-[#1da1f2] text-white w-full sm:w-auto">
                LinkedIn
              </a>
              <a href="https://twitter.com/intent/tweet?url={{ urlencode($url) }}&text={{ urlencode($post->title) }}"
                 target="_blank" rel="noopener"
                 class="share-button bg-[#1da1f2] text-white w-full sm:w-auto">
                X 
              </a>
              <a href="mailto:?subject={{ rawurlencode($post->title) }}&body={{ rawurlencode($url) }}"
                 class="share-button bg-primary-500/10 text-black dark:text-white dark:hover:text-white w-full sm:w-auto">
                {{ __('site.blog.share.email') }}
              </a>
              <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($url) }}"
                 target="_blank" rel="noopener"
                 class="share-button bg-[#3b5998] text-white w-full sm:w-auto">
                Facebook
              </a>
              <button type="button"
                x-data="{ copied: false }"
                x-on:click="navigator.clipboard.writeText('{{ $url }}').then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                class="share-button bg-gray-100 text-gray-800 hover:bg-primary-500 hover:text-[#4c51bf] dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-primary-500 w-full sm:w-auto">
                {{ __('site.blog.share.copy_link') }}
                <span x-show="copied" x-transition class="ml-2 inline-block text-sm text-green-600 dark:text-green-400" aria-live="polite">✔︎</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Navigation précédent / suivant -->
      <nav class="mt-14 grid gap-4 sm:grid-cols-2">
        @if($prev)
          <a href="{{ route('pages.blog.show', $prev) }}"
             class="group rounded-2xl border border-gray-200/70 dark:border-gray-800/60 p-5 hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('site.blog.previous') }}</div>
            <div class="font-semibold text-gray-900 dark:text-white line-clamp-2 group-hover:text-primary-500 dark:group-hover:text-primary-300">
              {{ $prev->title }}
            </div>
          </a>
        @endif

        @if($next)
          <a href="{{ route('pages.blog.show', $next) }}"
             class="group rounded-2xl border border-gray-200/70 dark:border-gray-800/60 p-5 hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors text-right">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('site.blog.next') }}</div>
            <div class="font-semibold text-gray-900 dark:text-white line-clamp-2 group-hover:text-primary-500 dark:group-hover:text-primary-300">
              {{ $next->title }}
            </div>
          </a>
        @endif
      </nav>

      <!-- Articles récents -->
      @if($recent->count())
        <section class="mt-16">
          <h2 class="text-2xl sm:text-3xl font-bold mb-6 text-gray-900 dark:text-white">{{ __('site.blog.recent') }}</h2>
          <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach ($recent as $r)
              @php $rimg = $r->cover_image ? Storage::url($r->cover_image) : asset('images/img1.jpg'); @endphp
              <a href="{{ route('pages.blog.show', $r) }}"
                 class="group bg-white dark:bg-gray-800 rounded-2xl shadow ring-1 ring-gray-200/70 dark:ring-gray-800/60 hover:shadow-lg transition-all overflow-hidden">
                <img src="{{ $rimg }}" loading="lazy" decoding="async" class="w-full object-cover transition-transform duration-300 group-hover:scale-[1.03]" alt="{{ $r->title }}">
                <div class="p-4">
                  <h3 class="text-base font-semibold text-gray-900 dark:text-white line-clamp-2 group-hover:text-primary-500 dark:group-hover:text-primary-300">
                    {{ $r->title }}
                  </h3>
                </div>
              </a>
            @endforeach
          </div>
        </section>
      @endif

    </div>
  </main>

  @include('layouts.footer')
</body>
</html>
