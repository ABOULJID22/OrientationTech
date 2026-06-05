<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Synthese d'orientation personnelle
        </x-slot>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-1">
                <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Plan d'action</h3>
                <div class="mt-4 space-y-4">
                    @foreach($timeline as $item)
                        <div class="border-l-2 border-primary-500 pl-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-primary-600">{{ $item['period'] }}</p>
                            <p class="mt-1 text-sm font-semibold text-gray-950 dark:text-white">{{ $item['title'] }}</p>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $item['description'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="lg:col-span-1">
                <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Metiers recommandes</h3>
                <div class="mt-4 overflow-hidden rounded-lg border border-gray-200 dark:border-white/10">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500 dark:bg-white/5">
                            <tr>
                                <th class="px-3 py-2">Metier</th>
                                <th class="px-3 py-2">Domaine</th>
                                <th class="px-3 py-2 text-right">Score</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                            @forelse($careers as $career)
                                <tr>
                                    <td class="px-3 py-2 font-medium text-gray-950 dark:text-white">{{ $career['title'] }}</td>
                                    <td class="px-3 py-2 text-gray-600 dark:text-gray-300">{{ $career['domain'] }}</td>
                                    <td class="px-3 py-2 text-right font-semibold text-primary-600">{{ $career['score'] }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-3 py-4 text-center text-gray-500">Complete le test personnalise pour voir les metiers.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="lg:col-span-1">
                <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Competences a ameliorer</h3>
                <div class="mt-4 space-y-3">
                    @forelse($skills as $skill)
                        <div class="rounded-lg border border-gray-200 p-3 dark:border-white/10">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-sm font-semibold text-gray-950 dark:text-white">{{ str_replace('_', ' ', $skill['skill']) }}</p>
                                <span class="text-sm font-semibold text-primary-600">{{ $skill['score'] }}%</span>
                            </div>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $skill['advice'] }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">Les axes de personnalite apparaitront apres le test.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
