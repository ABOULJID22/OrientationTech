@php
    $recentPosts = $recentPosts ?? collect();
    $recentResources = $recentResources ?? collect();
    $pendingContacts = $pendingContacts ?? collect();
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <div class="grid gap-6 lg:grid-cols-3">
            <div>
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-gray-950 dark:text-white">Articles recents</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Contenus publies pour guider les eleves.</p>
                    </div>
                    <x-filament::icon icon="heroicon-o-document-text" class="h-8 w-8 text-primary-600" />
                </div>

                <div class="space-y-3">
                    @forelse ($recentPosts as $post)
                        <div class="rounded-xl border border-gray-200 bg-white p-3 dark:border-white/10 dark:bg-white/5">
                            <p class="line-clamp-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $post->title }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ $post->created_at?->format('d/m/Y') }}</p>
                        </div>
                    @empty
                        <p class="rounded-xl bg-gray-50 p-4 text-sm text-gray-500 dark:bg-white/5 dark:text-gray-400">Aucun article publie pour le moment.</p>
                    @endforelse
                </div>
            </div>

            <div>
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-gray-950 dark:text-white">Ressources pedagogiques</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">PDF, videos, guides, metiers, domaines et actualites.</p>
                    </div>
                    <x-filament::icon icon="heroicon-o-book-open" class="h-8 w-8 text-primary-600" />
                </div>

                <div class="space-y-3">
                    @forelse ($recentResources as $resource)
                        <div class="rounded-xl border border-gray-200 bg-white p-3 dark:border-white/10 dark:bg-white/5">
                            <p class="line-clamp-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $resource->title }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ $resource->type_label }} - {{ $resource->created_at?->format('d/m/Y') }}</p>
                        </div>
                    @empty
                        <p class="rounded-xl bg-gray-50 p-4 text-sm text-gray-500 dark:bg-white/5 dark:text-gray-400">Aucune ressource ajoutee.</p>
                    @endforelse
                </div>
            </div>

            <div>
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-gray-950 dark:text-white">Support a traiter</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Messages sans reponse.</p>
                    </div>
                    <x-filament::icon icon="heroicon-o-lifebuoy" class="h-8 w-8 text-primary-600" />
                </div>

                <div class="space-y-3">
                    @forelse ($pendingContacts as $contact)
                        <div class="rounded-xl border border-gray-200 bg-white p-3 dark:border-white/10 dark:bg-white/5">
                            <p class="line-clamp-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $contact->name }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ $contact->user_type }} - {{ $contact->created_at?->format('d/m/Y') }}</p>
                        </div>
                    @empty
                        <p class="rounded-xl bg-emerald-50 p-4 text-sm text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">Aucun message en attente.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
