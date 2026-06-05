<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $resourceContent->title }} - OrientationTech</title>
    @include('layouts.theme-init')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('layouts.favicon')
</head>
<body class="bg-gray-50 text-gray-900 dark:bg-gray-900 dark:text-gray-100">
    @include('layouts.navbar')

    <main class="min-h-screen py-12 sm:py-16">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <nav class="mb-8 text-sm text-gray-500 dark:text-gray-400">
                <ol class="flex items-center gap-2">
                    <li><a href="{{ route('home') }}#ressources" class="hover:text-[#2563eb]">Ressources</a></li>
                    <li>/</li>
                    <li class="line-clamp-1">{{ $resourceContent->title }}</li>
                </ol>
            </nav>

            <article class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-white/10">
                <img
                    src="{{ $resourceContent->cover_url }}"
                    alt="{{ $resourceContent->title }}"
                    class="h-64 w-full object-cover sm:h-80"
                    loading="lazy"
                    decoding="async"
                >

                <div class="p-6 sm:p-8 lg:p-10">
                    <div class="mb-5 flex flex-wrap items-center gap-3">
                        <span class="rounded-full bg-[#2563eb]/10 px-3 py-1 text-xs font-bold uppercase tracking-wide text-[#2563eb]">
                            {{ $resourceContent->type_label }}
                        </span>
                        @if($resourceContent->domain_key)
                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600 dark:bg-white/10 dark:text-gray-300">
                                {{ $resourceContent->domain_key }}
                            </span>
                        @endif
                        @if($resourceContent->career_name)
                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600 dark:bg-white/10 dark:text-gray-300">
                                {{ $resourceContent->career_name }}
                            </span>
                        @endif
                    </div>

                    <h1 class="text-3xl font-extrabold tracking-tight text-gray-950 dark:text-white sm:text-4xl">
                        {{ $resourceContent->title }}
                    </h1>

                    @if($resourceContent->summary)
                        <p class="mt-4 text-lg leading-8 text-gray-600 dark:text-gray-300">
                            {{ $resourceContent->summary }}
                        </p>
                    @endif

                    <div class="mt-6 flex flex-wrap gap-3">
                        @auth
                            <form method="POST" action="{{ route('pages.resources.like', $resourceContent) }}">
                                @csrf
                                <button type="submit" aria-label="{{ $liked ? 'Retirer le like' : 'Aimer cette ressource' }}" title="{{ $liked ? 'Retirer le like' : 'Aimer cette ressource' }}" class="flex h-11 w-11 items-center justify-center rounded-full border p-0 leading-none transition hover:-translate-y-0.5 hover:shadow-md {{ $liked ? 'border-red-500 bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-300' : 'border-gray-200 bg-white text-gray-500 hover:border-red-300 hover:text-red-500 dark:border-white/10 dark:bg-gray-900 dark:text-gray-300' }}">
                                    <svg class="block h-5 w-5 shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M12 21s-7.2-4.55-9.45-9.15C.78 8.23 2.4 4.5 6.25 4.5c2.08 0 3.55 1.1 4.42 2.27.28.38.78.38 1.06 0C12.6 5.6 14.07 4.5 16.15 4.5c3.85 0 5.47 3.73 3.7 7.35C17.6 16.45 12 21 12 21Z" fill="{{ $liked ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('pages.resources.favorite', $resourceContent) }}">
                                @csrf
                                <button type="submit" aria-label="{{ $favorited ? 'Retirer des favoris' : 'Sauvegarder cette ressource' }}" title="{{ $favorited ? 'Retirer des favoris' : 'Sauvegarder cette ressource' }}" class="flex h-11 w-11 items-center justify-center rounded-full border p-0 leading-none transition hover:-translate-y-0.5 hover:shadow-md {{ $favorited ? 'border-[#2563eb] bg-[#eff6ff] text-[#2563eb] dark:bg-blue-500/10 dark:text-blue-300' : 'border-gray-200 bg-white text-gray-500 hover:border-[#93c5fd] hover:text-[#2563eb] dark:border-white/10 dark:bg-gray-900 dark:text-gray-300' }}">
                                    <svg class="block h-5 w-5 shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M6.5 4.75A2.25 2.25 0 0 1 8.75 2.5h6.5a2.25 2.25 0 0 1 2.25 2.25v16.1a.65.65 0 0 1-1.02.53L12 18.25l-4.48 3.13a.65.65 0 0 1-1.02-.53V4.75Z" fill="{{ $favorited ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
                                    </svg>
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" aria-label="Se connecter pour aimer" title="Se connecter pour aimer" class="flex h-11 w-11 items-center justify-center rounded-full border border-gray-200 bg-white p-0 leading-none text-gray-500 transition hover:-translate-y-0.5 hover:border-red-300 hover:text-red-500 hover:shadow-md dark:border-white/10 dark:bg-gray-900 dark:text-gray-300">
                                <svg class="block h-5 w-5 shrink-0" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s-7.2-4.55-9.45-9.15C.78 8.23 2.4 4.5 6.25 4.5c2.08 0 3.55 1.1 4.42 2.27.28.38.78.38 1.06 0C12.6 5.6 14.07 4.5 16.15 4.5c3.85 0 5.47 3.73 3.7 7.35C17.6 16.45 12 21 12 21Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            </a>
                            <a href="{{ route('login') }}" aria-label="Se connecter pour sauvegarder" title="Se connecter pour sauvegarder" class="flex h-11 w-11 items-center justify-center rounded-full border border-gray-200 bg-white p-0 leading-none text-gray-500 transition hover:-translate-y-0.5 hover:border-[#93c5fd] hover:text-[#2563eb] hover:shadow-md dark:border-white/10 dark:bg-gray-900 dark:text-gray-300">
                                <svg class="block h-5 w-5 shrink-0" viewBox="0 0 24 24" aria-hidden="true"><path d="M6.5 4.75A2.25 2.25 0 0 1 8.75 2.5h6.5a2.25 2.25 0 0 1 2.25 2.25v16.1a.65.65 0 0 1-1.02.53L12 18.25l-4.48 3.13a.65.65 0 0 1-1.02-.53V4.75Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" /></svg>
                            </a>
                        @endauth

                        @if($resourceContent->file_url)
                            <a href="{{ $resourceContent->file_url }}" target="_blank" rel="noopener" class="inline-flex items-center rounded-lg bg-[#2563eb] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#1d4ed8]">
                                Ouvrir le fichier
                            </a>
                            <a href="{{ $resourceContent->file_url }}" download class="inline-flex items-center rounded-lg border border-[#2563eb] px-4 py-2 text-sm font-semibold text-[#2563eb] hover:bg-[#eff6ff] dark:hover:bg-white/10">
                                Telecharger
                            </a>
                        @endif

                        @if($resourceContent->video_url)
                            <a href="{{ $resourceContent->video_url }}" target="_blank" rel="noopener" class="inline-flex items-center rounded-lg border border-[#2563eb] px-4 py-2 text-sm font-semibold text-[#2563eb] hover:bg-[#eff6ff] dark:hover:bg-white/10">
                                Voir la video
                            </a>
                        @endif
                    </div>

                    @if($resourceContent->content)
                        @php
                            $renderedContent = \Filament\Forms\Components\RichEditor\RichContentRenderer::make($resourceContent->content)->toHtml();
                        @endphp
                        <div class="prose prose-slate mt-10 max-w-none dark:prose-invert">
                            {!! $renderedContent !!}
                        </div>
                    @endif
                </div>
            </article>

            @if($recentResources->count())
                <section class="mt-12">
                    <h2 class="mb-5 text-2xl font-bold text-gray-950 dark:text-white">Autres ressources</h2>
                    <div class="grid gap-5 sm:grid-cols-3">
                        @foreach($recentResources as $resource)
                            <a href="{{ route('pages.resources.show', $resource) }}" class="group rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 transition hover:-translate-y-1 hover:shadow-lg dark:bg-gray-800 dark:ring-white/10">
                                <span class="text-xs font-bold uppercase tracking-wide text-[#2563eb]">{{ $resource->type_label }}</span>
                                <h3 class="mt-2 line-clamp-2 font-bold text-gray-950 group-hover:text-[#2563eb] dark:text-white">{{ $resource->title }}</h3>
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
