<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Pilotage de la plateforme
        </x-slot>

        <div class="grid gap-6 lg:grid-cols-3">
            <div>
                <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Funnel OrientationTech</h3>
                <div class="mt-4 space-y-3">
                    @php($max = max(1, collect($funnel)->max('value')))
                    @foreach($funnel as $step)
                        <div>
                            <div class="mb-1 flex items-center justify-between text-sm">
                                <span class="font-medium text-gray-700 dark:text-gray-200">{{ $step['label'] }}</span>
                                <span class="font-semibold text-gray-950 dark:text-white">{{ number_format($step['value']) }}</span>
                            </div>
                            <div class="h-2 rounded-full bg-gray-100 dark:bg-white/10">
                                <div class="h-2 rounded-full bg-primary-500" style="width: {{ max(6, ($step['value'] / $max) * 100) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Etablissements a suivre</h3>
                <div class="mt-4 overflow-hidden rounded-lg border border-gray-200 dark:border-white/10">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500 dark:bg-white/5">
                            <tr>
                                <th class="px-3 py-2">Etablissement</th>
                                <th class="px-3 py-2">Ville</th>
                                <th class="px-3 py-2 text-right">Completion</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                            @forelse($schools as $school)
                                <tr>
                                    <td class="px-3 py-2 font-medium text-gray-950 dark:text-white">{{ $school['name'] }}</td>
                                    <td class="px-3 py-2 text-gray-600 dark:text-gray-300">{{ $school['city'] }}</td>
                                    <td class="px-3 py-2 text-right font-semibold text-primary-600">{{ $school['completion'] }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-3 py-4 text-center text-gray-500">Aucun etablissement renseigne.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Utilisateurs recents</h3>
                <div class="mt-4 space-y-3">
                    @forelse($recentUsers as $user)
                        <div class="rounded-lg border border-gray-200 p-3 dark:border-white/10">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-sm font-semibold text-gray-950 dark:text-white">{{ $user['name'] }}</p>
                                <span class="text-xs font-medium text-gray-500">{{ $user['created_at'] }}</span>
                            </div>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $user['role'] }} | {{ $user['city'] }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">Aucune activite recente.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
