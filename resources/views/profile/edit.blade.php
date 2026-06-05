<x-app-layout>
    @php
        $user = Auth::user();

        $roleKey = 'student';
        $roleLabel = 'Eleve';
        $roleDescription = 'Ton espace personnel centralise ton profil, tes informations scolaires et ton parcours OrientationTech.';
        $roleBadgeClasses = 'bg-blue-50 text-[#2563eb] ring-blue-100';
        $primaryActionLabel = 'Continuer mon orientation';
        $primaryActionUrl = route('filament.admin.pages.dashboard');

        if ($user?->hasRole(\App\Models\User::ROLE_SUPER_ADMIN)) {
            $roleKey = 'super_admin';
            $roleLabel = 'Administration';
            $roleDescription = 'Profil administrateur pour superviser les utilisateurs, les contenus, les rapports et la qualite de la plateforme.';
            $roleBadgeClasses = 'bg-slate-900 text-white ring-slate-700';
            $primaryActionLabel = 'Ouvrir le panel admin';
        } elseif ($user?->hasRole(\App\Models\User::ROLE_TEACHER)) {
            $roleKey = 'teacher';
            $roleLabel = 'Enseignant';
            $roleDescription = 'Profil enseignant pour publier des ressources, accompagner les eleves et suivre les interactions pedagogiques.';
            $roleBadgeClasses = 'bg-indigo-50 text-indigo-700 ring-indigo-100';
            $primaryActionLabel = 'Gerer mes contenus';
        }

        $studentProfile = $user?->studentProfile;
        $diagnosticCompleted = $user
            ? \App\Models\AcademicDiagnostic::query()->where('user_id', $user->id)->where('status', 'completed')->exists()
            : false;
        $personalityCompleted = $user
            ? \App\Models\TestPersonnalise::query()->where('user_id', $user->id)->where('status', 'completed')->exists()
            : false;

        $baseFields = collect([
            $user?->name,
            $user?->email,
            $user?->phone,
            $user?->city,
            $user?->country,
        ])->filter()->count();

        $profileScore = min(100, (int) round(($baseFields / 5) * 100));

        if ($roleKey === 'student') {
            $profileScore = min(100, $profileScore + ($studentProfile?->is_complete ? 25 : 0));
        }

        $statusItems = match ($roleKey) {
            'super_admin' => [
                ['label' => 'Role', 'value' => 'Super admin', 'state' => 'Actif'],
                ['label' => 'Acces', 'value' => 'Administration complete', 'state' => 'Autorise'],
                ['label' => 'Suivi', 'value' => 'Utilisateurs et rapports', 'state' => 'Centralise'],
            ],
            'teacher' => [
                ['label' => 'Role', 'value' => 'Enseignant', 'state' => $user?->is_active ? 'Actif' : 'En attente'],
                ['label' => 'Mission', 'value' => 'Contenus pedagogiques', 'state' => 'Publier'],
                ['label' => 'Interaction', 'value' => 'Commentaires eleves', 'state' => 'Suivre'],
            ],
            default => [
                ['label' => 'Profil eleve', 'value' => $studentProfile?->is_complete ? 'Complete' : 'A completer', 'state' => $studentProfile?->is_complete ? 'Pret' : 'Important'],
                ['label' => 'Diagnostic', 'value' => $diagnosticCompleted ? 'Termine' : 'Non termine', 'state' => $diagnosticCompleted ? 'OK' : 'A faire'],
                ['label' => 'Test personnalise', 'value' => $personalityCompleted ? 'Termine' : 'Non termine', 'state' => $personalityCompleted ? 'OK' : 'A faire'],
            ],
        };
    @endphp

    <div class="min-h-screen bg-[#eff6ff] pt-24 sm:pt-28">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <section class="overflow-hidden rounded-[2rem] border border-blue-100 bg-white shadow-xl shadow-blue-950/5">
                <div class="grid gap-0 lg:grid-cols-[1.15fr,0.85fr]">
                    <div class="relative p-6 sm:p-8 lg:p-10">
                        <div class="absolute inset-x-0 top-0 h-1 bg-[#2563eb]"></div>

                        <div class="flex flex-col gap-6 sm:flex-row sm:items-center">
                            <div class="relative shrink-0">
                                <div class="absolute -inset-2 rounded-[1.75rem] bg-blue-100"></div>
                                <img src="{{ $user->avatar }}" alt="Avatar" class="relative h-28 w-28 rounded-[1.5rem] border-4 border-white object-cover shadow-lg">
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-3">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wide ring-1 {{ $roleBadgeClasses }}">
                                        {{ $roleLabel }}
                                    </span>
                                    @if ($user?->is_active)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            Compte actif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-100">
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                            Activation en attente
                                        </span>
                                    @endif
                                </div>

                                <h1 class="mt-4 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">
                                    {{ $user->name }}
                                </h1>
                                <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 sm:text-base">
                                    {{ $roleDescription }}
                                </p>

                                <div class="mt-6 flex flex-wrap gap-3">
                                    <a href="{{ $primaryActionUrl }}" class="inline-flex items-center justify-center rounded-xl bg-[#2563eb] px-5 py-3 text-sm font-bold text-white shadow-lg shadow-blue-600/20 transition hover:bg-[#1d4ed8]">
                                        {{ $primaryActionLabel }}
                                    </a>

                                    @if ($roleKey === 'student')
                                        <a href="{{ route('student-profile.show') }}" class="inline-flex items-center justify-center rounded-xl border border-blue-200 bg-white px-5 py-3 text-sm font-bold text-[#2563eb] transition hover:bg-blue-50">
                                            Profil scolaire
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-blue-100 bg-slate-950 p-6 text-white sm:p-8 lg:border-l lg:border-t-0 lg:p-10">
                        <p class="text-xs font-bold uppercase tracking-[0.24em] text-blue-200">Synthese du profil</p>

                        <div class="mt-6">
                            <div class="flex items-end justify-between gap-4">
                                <div>
                                    <p class="text-sm text-slate-300">Completion generale</p>
                                    <p class="mt-1 text-4xl font-black">{{ $profileScore }}%</p>
                                </div>
                                <div class="text-right text-xs text-slate-400">
                                    Mis a jour<br>{{ optional($user->updated_at)->format('d/m/Y H:i') }}
                                </div>
                            </div>
                            <div class="mt-4 h-2 overflow-hidden rounded-full bg-white/10">
                                <div class="h-full rounded-full bg-[#60a5fa]" style="width: {{ $profileScore }}%"></div>
                            </div>
                        </div>

                        <div class="mt-8 grid gap-3">
                            @foreach ($statusItems as $item)
                                <div class="rounded-2xl border border-white/10 bg-white/[0.06] p-4">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="text-xs font-semibold uppercase tracking-wide text-blue-200">{{ $item['label'] }}</p>
                                            <p class="mt-1 text-sm font-bold text-white">{{ $item['value'] }}</p>
                                        </div>
                                        <span class="rounded-full bg-white/10 px-3 py-1 text-xs font-bold text-blue-100">{{ $item['state'] }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <div class="mt-8 grid gap-8 xl:grid-cols-[minmax(0,1fr),24rem]">
                <div class="space-y-8">
                    <div class="rounded-[1.5rem] border border-blue-100 bg-white p-5 shadow-lg shadow-blue-950/5 sm:p-8">
                        @include('profile.partials.update-profile-information-form', ['roleKey' => $roleKey, 'roleLabel' => $roleLabel])
                    </div>
                </div>

                <aside class="space-y-6">
                    <div class="rounded-[1.5rem] border border-blue-100 bg-white p-6 shadow-lg shadow-blue-950/5">
                        <h2 class="text-lg font-black text-slate-950">Informations utiles</h2>
                        <dl class="mt-5 space-y-4 text-sm">
                            <div class="flex items-start justify-between gap-4">
                                <dt class="font-semibold text-slate-500">Email</dt>
                                <dd class="text-right font-bold text-slate-900">{{ $user->email }}</dd>
                            </div>
                            <div class="flex items-start justify-between gap-4">
                                <dt class="font-semibold text-slate-500">Telephone</dt>
                                <dd class="text-right font-bold text-slate-900">{{ $user->phone ?: 'Non renseigne' }}</dd>
                            </div>
                            <div class="flex items-start justify-between gap-4">
                                <dt class="font-semibold text-slate-500">Ville</dt>
                                <dd class="text-right font-bold text-slate-900">{{ $user->city ?: 'Non renseignee' }}</dd>
                            </div>
                            <div class="flex items-start justify-between gap-4">
                                <dt class="font-semibold text-slate-500">Pays</dt>
                                <dd class="text-right font-bold text-slate-900">{{ $user->country ?: 'Non renseigne' }}</dd>
                            </div>
                        </dl>
                    </div>

                    @if ($roleKey === 'student')
                        <div class="rounded-[1.5rem] border border-blue-100 bg-white p-6 shadow-lg shadow-blue-950/5">
                            <h2 class="text-lg font-black text-slate-950">Parcours eleve</h2>
                            <div class="mt-5 space-y-3 text-sm">
                                <div class="rounded-2xl bg-blue-50 p-4">
                                    <p class="font-bold text-slate-950">Niveau scolaire</p>
                                    <p class="mt-1 text-slate-600">{{ $studentProfile?->education_level ?: 'A completer' }}</p>
                                </div>
                                <div class="rounded-2xl bg-blue-50 p-4">
                                    <p class="font-bold text-slate-950">Etablissement</p>
                                    <p class="mt-1 text-slate-600">{{ $studentProfile?->school_name ?: 'A completer' }}</p>
                                </div>
                                <a href="{{ route('student-profile.show') }}" class="inline-flex w-full items-center justify-center rounded-xl bg-[#eff6ff] px-4 py-3 text-sm font-bold text-[#2563eb] transition hover:bg-[#dbeafe]">
                                    Modifier mon profil scolaire
                                </a>
                            </div>
                        </div>
                    @elseif ($roleKey === 'teacher')
                        <div class="rounded-[1.5rem] border border-blue-100 bg-white p-6 shadow-lg shadow-blue-950/5">
                            <h2 class="text-lg font-black text-slate-950">Espace enseignant</h2>
                            <p class="mt-2 text-sm leading-6 text-slate-600">
                                Complete ton titre professionnel et tes contacts pour renforcer la confiance dans les ressources publiees.
                            </p>
                            <a href="{{ route('filament.admin.resources.posts.index') }}" class="mt-5 inline-flex w-full items-center justify-center rounded-xl bg-[#eff6ff] px-4 py-3 text-sm font-bold text-[#2563eb] transition hover:bg-[#dbeafe]">
                                Mes articles
                            </a>
                        </div>
                    @else
                        <div class="rounded-[1.5rem] border border-blue-100 bg-white p-6 shadow-lg shadow-blue-950/5">
                            <h2 class="text-lg font-black text-slate-950">Administration</h2>
                            <p class="mt-2 text-sm leading-6 text-slate-600">
                                Ce profil doit rester clair et securise, car il donne acces aux donnees sensibles de la plateforme.
                            </p>
                            <a href="{{ route('filament.admin.resources.users.index') }}" class="mt-5 inline-flex w-full items-center justify-center rounded-xl bg-[#eff6ff] px-4 py-3 text-sm font-bold text-[#2563eb] transition hover:bg-[#dbeafe]">
                                Gerer les utilisateurs
                            </a>
                        </div>
                    @endif

                    <div class="rounded-[1.5rem] border border-blue-100 bg-white p-6 shadow-lg shadow-blue-950/5">
                        @include('profile.partials.update-password-form', ['compact' => true])
                    </div>
                </aside>
            </div>
        </div>
    </div>
</x-app-layout>
