<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Explorer les domaines du numerique - OrientationTech</title>
    @include('layouts.favicon')
    @include('layouts.theme-init')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 dark:bg-gray-900 dark:text-gray-100">
@include('layouts.navbar')

<main class="min-h-screen py-10 sm:py-14">
    <section class="bg-gradient-to-br from-[#172554] via-[#2563eb] to-[#3b82f6] py-14 text-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6">
            <p class="text-sm font-bold uppercase tracking-wide text-blue-100">OrientationTech Explorer</p>
            <h1 class="mt-3 max-w-4xl text-4xl font-extrabold sm:text-5xl">Explorer les domaines du numerique</h1>
            <p class="mt-4 max-w-3xl text-lg text-blue-50">Lis, compare, aime et sauvegarde les specialites informatiques pour construire ton parcours d orientation.</p>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
        <form method="GET" action="{{ route('domains.index') }}" class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-white/10">
            <div class="grid gap-4 lg:grid-cols-5">
                <input name="search" value="{{ request('search') }}" placeholder="Rechercher : Python, Cybersecurite, Data, freelance..." class="rounded-xl border border-gray-200 px-4 py-3 text-sm lg:col-span-2 dark:border-white/10 dark:bg-gray-900">
                <select name="category" class="rounded-xl border border-gray-200 px-4 py-3 text-sm dark:border-white/10 dark:bg-gray-900">
                    <option value="">Toutes les categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
                    @endforeach
                </select>
                <select name="difficulty_level" class="rounded-xl border border-gray-200 px-4 py-3 text-sm dark:border-white/10 dark:bg-gray-900">
                    <option value="">Toutes difficultes</option>
                    @foreach($difficultyLevels as $level)
                        <option value="{{ $level }}" @selected(request('difficulty_level') === $level)>{{ $level }}</option>
                    @endforeach
                </select>
                <select name="sort" class="rounded-xl border border-gray-200 px-4 py-3 text-sm dark:border-white/10 dark:bg-gray-900">
                    <option value="recent" @selected(request('sort') === 'recent')>Les plus recents</option>
                    <option value="likes" @selected(request('sort') === 'likes')>Les plus aimes</option>
                    <option value="views" @selected(request('sort') === 'views')>Les plus consultes</option>
                    <option value="comments" @selected(request('sort') === 'comments')>Les plus commentes</option>
                    <option value="rating" @selected(request('sort') === 'rating')>Les mieux notes</option>
                    <option value="junior_salary" @selected(request('sort') === 'junior_salary')>Salaire junior eleve</option>
                    <option value="senior_salary" @selected(request('sort') === 'senior_salary')>Salaire senior eleve</option>
                    <option value="easy" @selected(request('sort') === 'easy')>Difficulte la plus facile</option>
                </select>
            </div>
            <div class="mt-4 flex flex-wrap items-center gap-3">
                <select name="future_potential" class="rounded-xl border border-gray-200 px-4 py-2 text-sm dark:border-white/10 dark:bg-gray-900">
                    <option value="">Potentiel futur</option>
                    @foreach($futurePotentials as $value)<option value="{{ $value }}" @selected(request('future_potential') === $value)>{{ $value }}</option>@endforeach
                </select>
                <select name="ai_impact" class="rounded-xl border border-gray-200 px-4 py-2 text-sm dark:border-white/10 dark:bg-gray-900">
                    <option value="">Impact IA</option>
                    @foreach($aiImpacts as $value)<option value="{{ $value }}" @selected(request('ai_impact') === $value)>{{ $value }}</option>@endforeach
                </select>
                <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="freelance" value="1" @checked(request('freelance'))> Freelance possible</label>
                <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="remote" value="1" @checked(request('remote'))> Remote possible</label>
                <button class="rounded-xl bg-[#2563eb] px-5 py-2 text-sm font-bold text-white hover:bg-[#1d4ed8]">Appliquer</button>
                <a href="{{ route('domains.compare') }}" class="rounded-xl border border-[#2563eb] px-5 py-2 text-sm font-bold text-[#2563eb]">Comparer</a>
            </div>
        </form>
    </section>

    <section class="mx-auto max-w-7xl px-4 pb-12 sm:px-6">
        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            @forelse($domains as $domain)
                <article class="relative flex h-full flex-col overflow-hidden rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 transition hover:-translate-y-1 hover:shadow-lg dark:bg-gray-800 dark:ring-white/10">
                    <div class="min-w-0 pr-24 sm:pr-28">
                        <span class="inline-flex max-w-full rounded-full bg-[#eff6ff] px-3 py-1 text-xs font-bold text-[#2563eb]">
                            <span class="truncate">{{ $domain->category }}</span>
                        </span>
                        <h2 class="mt-4 text-xl font-extrabold text-gray-950 dark:text-white">{{ $domain->name }}</h2>
                        @php
                            $liked = $domain->isLikedBy(auth()->user());
                            $favorited = $domain->isFavoritedBy(auth()->user());
                        @endphp
                        <div class="absolute right-4 top-4 flex items-center gap-2 sm:right-5 sm:top-5">
                            @auth
                                <form method="POST" action="{{ route('domains.like', $domain) }}">
                                    @csrf
                                    <button
                                        type="submit"
                                        aria-label="{{ $liked ? 'Retirer le like' : 'Aimer ce domaine' }}"
                                        title="{{ $liked ? 'Retirer le like' : 'Aimer ce domaine' }}"
                                        class="flex h-10 w-10 items-center justify-center rounded-full border p-0 leading-none shadow-sm transition hover:-translate-y-0.5 hover:shadow-md sm:h-11 sm:w-11 {{ $liked ? 'border-red-500 bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-300' : 'border-gray-200 bg-white text-gray-500 hover:border-red-300 hover:text-red-500 dark:border-white/10 dark:bg-gray-900 dark:text-gray-300' }}"
                                    >
                                        <svg class="block h-5 w-5 shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M12 21s-7.2-4.55-9.45-9.15C.78 8.23 2.4 4.5 6.25 4.5c2.08 0 3.55 1.1 4.42 2.27.28.38.78.38 1.06 0C12.6 5.6 14.07 4.5 16.15 4.5c3.85 0 5.47 3.73 3.7 7.35C17.6 16.45 12 21 12 21Z" fill="{{ $liked ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('domains.favorite', $domain) }}">
                                    @csrf
                                    <button
                                        type="submit"
                                        aria-label="{{ $favorited ? 'Retirer des favoris' : 'Sauvegarder ce domaine' }}"
                                        title="{{ $favorited ? 'Retirer des favoris' : 'Sauvegarder ce domaine' }}"
                                        class="flex h-10 w-10 items-center justify-center rounded-full border p-0 leading-none shadow-sm transition hover:-translate-y-0.5 hover:shadow-md sm:h-11 sm:w-11 {{ $favorited ? 'border-[#2563eb] bg-[#eff6ff] text-[#2563eb] dark:bg-blue-500/10 dark:text-blue-300' : 'border-gray-200 bg-white text-gray-500 hover:border-[#93c5fd] hover:text-[#2563eb] dark:border-white/10 dark:bg-gray-900 dark:text-gray-300' }}"
                                    >
                                        <svg class="block h-5 w-5 shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M6.5 4.75A2.25 2.25 0 0 1 8.75 2.5h6.5a2.25 2.25 0 0 1 2.25 2.25v16.1a.65.65 0 0 1-1.02.53L12 18.25l-4.48 3.13a.65.65 0 0 1-1.02-.53V4.75Z" fill="{{ $favorited ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
                                        </svg>
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('login') }}" aria-label="Connecte-toi pour aimer" title="Connecte-toi pour aimer" class="flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 bg-white p-0 leading-none text-gray-500 shadow-sm hover:border-red-300 hover:text-red-500 hover:shadow-md dark:border-white/10 dark:bg-gray-900 sm:h-11 sm:w-11">
                                    <svg class="block h-5 w-5 shrink-0" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s-7.2-4.55-9.45-9.15C.78 8.23 2.4 4.5 6.25 4.5c2.08 0 3.55 1.1 4.42 2.27.28.38.78.38 1.06 0C12.6 5.6 14.07 4.5 16.15 4.5c3.85 0 5.47 3.73 3.7 7.35C17.6 16.45 12 21 12 21Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                </a>
                                <a href="{{ route('login') }}" aria-label="Connecte-toi pour sauvegarder" title="Connecte-toi pour sauvegarder" class="flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 bg-white p-0 leading-none text-gray-500 shadow-sm hover:border-[#93c5fd] hover:text-[#2563eb] hover:shadow-md dark:border-white/10 dark:bg-gray-900 sm:h-11 sm:w-11">
                                    <svg class="block h-5 w-5 shrink-0" viewBox="0 0 24 24" aria-hidden="true"><path d="M6.5 4.75A2.25 2.25 0 0 1 8.75 2.5h6.5a2.25 2.25 0 0 1 2.25 2.25v16.1a.65.65 0 0 1-1.02.53L12 18.25l-4.48 3.13a.65.65 0 0 1-1.02-.53V4.75Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" /></svg>
                                </a>
                            @endauth
                        </div>
                    </div>
                    <p class="mt-3 line-clamp-3 text-sm leading-6 text-gray-600 dark:text-gray-300">{{ $domain->short_description }}</p>
                    <div class="mt-4 grid grid-cols-3 gap-2 text-xs">
                        <span class="rounded-lg bg-gray-50 p-2 dark:bg-gray-900">Difficulte<br><b>{{ $domain->difficulty_level }}</b></span>
                        <span class="rounded-lg bg-gray-50 p-2 dark:bg-gray-900">Potentiel<br><b>{{ $domain->future_potential }}</b></span>
                        <span class="rounded-lg bg-gray-50 p-2 dark:bg-gray-900">IA<br><b>{{ $domain->ai_impact }}</b></span>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-3 text-xs font-semibold text-gray-500">
                        <span>{{ $domain->views_count }} vues</span>
                        <span>{{ $domain->likes_count }} likes</span>
                        <span>{{ $domain->comments_count }} commentaires</span>
                        <span>{{ number_format((float) $domain->rating_average, 1) }}/5</span>
                    </div>
                    <div class="mt-auto pt-5">
                        <a href="{{ route('domains.show', $domain) }}" class="inline-flex w-full justify-center rounded-xl bg-[#2563eb] px-4 py-3 text-sm font-bold text-white hover:bg-[#1d4ed8]">Voir le domaine</a>
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-2xl bg-white p-8 text-center text-gray-500 shadow-sm">Aucun domaine trouve.</div>
            @endforelse
        </div>
        <div class="mt-8">{{ $domains->links() }}</div>
    </section>

    @php $blocks = [['Domaines les plus aimes', $mostLiked], ['Domaines les plus consultes', $mostViewed], ['Domaines les mieux notes', $bestRated], ['Domaines du futur', $futureDomains]]; @endphp
    <section class="mx-auto text-black max-w-7xl px-4 pb-16 sm:px-6">
        <div class="grid gap-6 lg:grid-cols-4">
            @foreach($blocks as [$title, $items])
                <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-white/10">
                    <h3 class="font-extrabold text-gray-950 dark:text-white">{{ $title }}</h3>
                    <div class="mt-4 space-y-3">
                        @forelse($items as $item)
                            <a href="{{ route('domains.show', $item) }}" class="block rounded-xl bg-[#eff6ff] p-3 text-sm font-semibold text-slate-800 transition hover:bg-[#dbeafe] hover:text-[#2563eb] dark:bg-gray-900 dark:text-white dark:hover:bg-gray-950 dark:hover:text-blue-200">{{ $item->name }}</a>
                        @empty
                            <p class="text-sm text-slate-500 dark:text-slate-300">Pas encore de donnees.</p>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</main>

@include('layouts.footer')
</body>
</html>
