<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $domain->name }} - OrientationTech</title>
    @include('layouts.favicon')
    @include('layouts.theme-init')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 dark:bg-gray-900 dark:text-gray-100">
@include('layouts.navbar')

<main class="mx-auto max-w-6xl px-4 py-10 sm:px-6">
    <a href="{{ route('domains.index') }}" class="text-sm font-semibold text-[#2563eb]">Retour aux domaines</a>

    <section class="mt-6 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-white/10">
        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr),auto] lg:items-start">
            <div class="min-w-0">
                <span class="inline-flex rounded-full bg-[#eff6ff] px-3 py-1 text-xs font-bold text-[#2563eb]">{{ $domain->category }}</span>
                <h1 class="mt-4 text-4xl font-extrabold text-gray-950 dark:text-white sm:text-5xl">{{ $domain->name }}</h1>
                <p class="mt-4 max-w-3xl text-lg leading-8 text-gray-600 dark:text-gray-300">{{ $domain->short_description }}</p>
                <div class="mt-4 flex flex-wrap gap-3 text-sm font-semibold text-gray-500 dark:text-gray-300">
                    <span>{{ $domain->views_count }} vues</span>
                    <span>{{ $domain->likes_count }} likes</span>
                    <span>{{ $domain->comments_count }} commentaires</span>
                    <span>{{ number_format((float) $domain->rating_average, 1) }}/5</span>
                </div>
            </div>

            @auth
                <div class="flex items-center gap-3 lg:flex-col lg:items-end">
                    <form method="POST" action="{{ route('domains.like', $domain) }}">
                        @csrf
                        <button
                            type="submit"
                            aria-label="{{ $liked ? 'Retirer le like' : 'Aimer ce domaine' }}"
                            title="{{ $liked ? 'Retirer le like' : 'Aimer ce domaine' }}"
                            class="inline-flex h-12 w-12 items-center justify-center rounded-full border p-0 leading-none shadow-sm transition hover:-translate-y-0.5 hover:shadow-md {{ $liked ? 'border-red-500 bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-300' : 'border-gray-200 bg-gray-50 text-gray-700 hover:border-red-300 hover:text-red-500 dark:border-white/10 dark:bg-gray-900 dark:text-gray-300' }}"
                        >
                            <svg class="block h-6 w-6 shrink-0" viewBox="0 0 24 24" aria-hidden="true">
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
                            class="inline-flex h-12 w-12 items-center justify-center rounded-full border p-0 leading-none shadow-sm transition hover:-translate-y-0.5 hover:shadow-md {{ $favorited ? 'border-[#2563eb] bg-[#eff6ff] text-[#2563eb] dark:bg-blue-500/10 dark:text-blue-300' : 'border-gray-200 bg-gray-50 text-gray-700 hover:border-[#93c5fd] hover:text-[#2563eb] dark:border-white/10 dark:bg-gray-900 dark:text-gray-300' }}"
                        >
                            <svg class="block h-6 w-6 shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M6.5 4.75A2.25 2.25 0 0 1 8.75 2.5h6.5a2.25 2.25 0 0 1 2.25 2.25v16.1a.65.65 0 0 1-1.02.53L12 18.25l-4.48 3.13a.65.65 0 0 1-1.02-.53V4.75Z" fill="{{ $favorited ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </form>
                </div>
            @endauth
        </div>
    </section>

    @php
        $sections = [
            ['Pour qui ?', $domain->student_profile],
            ['Matieres utiles', $domain->school_subjects],
            ['Competences techniques', $domain->technical_skills],
            ['Competences personnelles', $domain->soft_skills],
            ['Outils et technologies', $domain->tools],
            ['Metiers associes', $domain->related_jobs],
            ['Parcours apprentissage', $domain->learning_path],
            ['Formations au Maroc', $domain->schools_morocco],
            ['Certifications', $domain->certifications],
            ['Avantages', $domain->advantages],
            ['Difficultes possibles', $domain->challenges],
            ['Projets pratiques', $domain->practical_projects],
        ];
    @endphp

    <section class="mt-8 grid gap-6 lg:grid-cols-3">
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-white/10 lg:col-span-2">
            <h2 class="text-2xl font-bold">Description complete</h2>
            <div class="prose prose-slate mt-4 max-w-none dark:prose-invert">{!! $domain->full_description !!}</div>
            @if($domain->why_important)
                <p class="mt-5 rounded-xl bg-[#eff6ff] p-4 text-[#1e40af] dark:bg-blue-500/10 dark:text-blue-200">{{ $domain->why_important }}</p>
            @endif
        </div>

        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-white/10">
            <h2 class="text-xl font-bold">Salaires estimes</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Junior Maroc</dt>
                    <dd class="font-bold">{{ number_format((int) $domain->junior_salary_min) }} - {{ number_format((int) $domain->junior_salary_max) }} {{ $domain->currency }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Senior Maroc</dt>
                    <dd class="font-bold">{{ number_format((int) $domain->senior_salary_min) }} - {{ number_format((int) $domain->senior_salary_max) }} {{ $domain->currency }}</dd>
                </div>
            </dl>
            <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">{{ $domain->salary_note ?: 'Estimations pedagogiques variables selon experience, ville et marche.' }}</p>
            <div class="mt-5 grid grid-cols-2 gap-2 text-sm">
                <span class="rounded-lg bg-gray-50 p-3 dark:bg-gray-900">Freelance<br><b>{{ $domain->freelance_opportunity }}/5</b></span>
                <span class="rounded-lg bg-gray-50 p-3 dark:bg-gray-900">Remote<br><b>{{ $domain->remote_opportunity }}/5</b></span>
            </div>
        </div>
    </section>

    <section class="mt-8 grid gap-6 md:grid-cols-2">
        @foreach($sections as [$title, $items])
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-white/10">
                <h3 class="font-bold text-gray-950 dark:text-white">{{ $title }}</h3>
                <ul class="mt-3 space-y-2 text-sm text-gray-600 dark:text-gray-300">
                    @forelse((array) $items as $item)
                        <li class="rounded-lg bg-gray-50 p-3 dark:bg-gray-900">{{ $item }}</li>
                    @empty
                        <li>Non precise.</li>
                    @endforelse
                </ul>
            </div>
        @endforeach
    </section>

    @auth
        <section class="mt-8 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-white/10">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-950 dark:text-white">Evaluer ce domaine</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-300">
                        Ton avis aide les autres eleves a mieux comparer les specialites.
                    </p>
                </div>
                <div class="rounded-full bg-[#eff6ff] px-4 py-2 text-sm font-bold text-[#2563eb] dark:bg-blue-500/10 dark:text-blue-200">
                    {{ number_format((float) $domain->rating_average, 1) }}/5
                </div>
            </div>

            <form method="POST" action="{{ route('domains.rate', $domain) }}" class="mt-5 flex flex-wrap items-center gap-2" aria-label="Evaluer ce domaine">
                @csrf
                @for($i = 1; $i <= 5; $i++)
                    <button
                        type="submit"
                        name="rating"
                        value="{{ $i }}"
                        aria-label="Donner {{ $i }} etoile{{ $i > 1 ? 's' : '' }}"
                        title="{{ $i }} etoile{{ $i > 1 ? 's' : '' }}"
                        class="group inline-flex h-12 w-12 items-center justify-center rounded-full border p-0 leading-none transition hover:-translate-y-0.5 hover:border-yellow-400 hover:bg-yellow-50 hover:shadow-md dark:hover:bg-yellow-400/10 {{ (int) $myRating >= $i ? 'border-yellow-400 bg-yellow-50 text-yellow-500 dark:bg-yellow-400/10' : 'border-gray-200 bg-gray-50 text-gray-300 dark:border-white/10 dark:bg-gray-900 dark:text-gray-600' }}"
                    >
                        <svg class="block h-7 w-7 shrink-0 transition group-hover:scale-110 group-hover:text-yellow-500" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="m12 2.75 2.78 5.63 6.22.9-4.5 4.39 1.06 6.2L12 16.95l-5.56 2.92 1.06-6.2L3 9.28l6.22-.9L12 2.75Z" fill="currentColor" />
                        </svg>
                    </button>
                @endfor
            </form>

            @if($myRating)
                <p class="mt-3 text-sm font-semibold text-gray-600 dark:text-gray-300">Ton evaluation : {{ $myRating }}/5</p>
            @endif
        </section>
    @endauth

    <section class="mt-8 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-white/10">
        <h2 class="text-2xl font-bold">Commentaires</h2>
        @auth
            <form method="POST" action="{{ route('domains.comments.store', $domain) }}" class="mt-4 space-y-3">
                @csrf
                <textarea name="content" rows="3" required class="w-full rounded-xl border border-gray-200 p-4 dark:border-white/10 dark:bg-gray-900" placeholder="Pose une question ou partage ton avis..."></textarea>
                <button class="rounded-xl bg-[#2563eb] px-5 py-2 font-bold text-white">Publier</button>
            </form>
        @else
            <p class="mt-3 rounded-xl bg-[#eff6ff] p-4 text-[#1e40af] dark:bg-blue-500/10 dark:text-blue-200">Connecte-toi pour commenter, aimer et sauvegarder ce domaine.</p>
        @endauth

        <div class="mt-6 space-y-4">
            @forelse($comments as $comment)
                <div class="rounded-xl border border-gray-200 p-4 dark:border-white/10">
                    <p class="font-bold">{{ $comment->user?->name }}</p>
                    <p class="mt-2 whitespace-pre-line text-sm">{{ $comment->content }}</p>
                    @auth
                        <div class="mt-3 flex flex-wrap gap-2">
                            <details>
                                <summary class="cursor-pointer text-sm font-bold text-[#2563eb]">Repondre</summary>
                                <form method="POST" action="{{ route('domains.comments.store', $domain) }}" class="mt-2">
                                    @csrf
                                    <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                    <textarea name="content" rows="2" class="w-full rounded-lg border p-3 dark:bg-gray-900"></textarea>
                                    <button class="mt-2 rounded-lg bg-[#2563eb] px-3 py-2 text-xs font-bold text-white">Envoyer</button>
                                </form>
                            </details>
                            @if(auth()->id() !== $comment->user_id)
                                <form method="POST" action="{{ route('domains.comments.report', $comment) }}">
                                    @csrf
                                    <input type="hidden" name="reason" value="Contenu inapproprie">
                                    <button class="text-sm font-bold text-amber-600">Signaler</button>
                                </form>
                            @endif
                        </div>
                    @endauth
                    @foreach($comment->replies as $reply)
                        <div class="mt-3 rounded-lg bg-gray-50 p-3 text-sm dark:bg-gray-900">
                            <b>{{ $reply->user?->name }}</b>
                            <p>{{ $reply->content }}</p>
                        </div>
                    @endforeach
                </div>
            @empty
                <p class="text-sm text-gray-500 dark:text-gray-300">Aucun commentaire pour le moment.</p>
            @endforelse
        </div>
    </section>
</main>

@include('layouts.footer')
</body>
</html>
