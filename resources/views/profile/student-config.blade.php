<x-guest-layout>
    @php
        $preferredTypes = old('preferred_school_types', $profile->preferred_school_types ?? []);
        $services = old('interested_services', $profile->interested_services ?? []);
    @endphp

    <div class="min-h-screen bg-[#eff6ff] px-4 py-10 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-5xl">
            @if (session('alert'))
                <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm font-semibold text-amber-800">
                    {{ session('alert') }}
                </div>
            @endif

            <section class="overflow-hidden rounded-[2rem] border border-blue-100 bg-white shadow-xl shadow-blue-950/5">
                <div class="grid lg:grid-cols-[0.95fr,1.05fr]">
                    <div class="bg-slate-950 p-6 text-white sm:p-8 lg:p-10">
                        <span class="inline-flex rounded-full bg-white/10 px-3 py-1 text-xs font-bold uppercase tracking-wide text-blue-100">
                            Profil eleve
                        </span>
                        <h1 class="mt-5 text-3xl font-black leading-tight sm:text-4xl">
                            Complete ton profil scolaire
                        </h1>
                        <p class="mt-4 text-sm leading-7 text-slate-300">
                            Ces informations aident OrientationTech a adapter le diagnostic, les recommandations, les ressources et le rapport d'orientation au contexte marocain.
                        </p>

                        <div class="mt-8 grid gap-3">
                            @foreach ([
                                ['01', 'Niveau et filiere'],
                                ['02', 'Preferences de formation'],
                                ['03', 'Informations personnelles'],
                                ['04', 'Consentement de suivi'],
                            ] as $item)
                                <div class="rounded-2xl border border-white/10 bg-white/[0.06] p-4">
                                    <p class="text-xs font-bold text-blue-200">{{ $item[0] }}</p>
                                    <p class="mt-1 font-bold">{{ $item[1] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="p-5 sm:p-8 lg:p-10">
                        <form method="POST" action="{{ route('student-profile.store') }}" class="space-y-8">
                            @csrf

                            <section class="space-y-5">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#2563eb]">Academique</p>
                                    <h2 class="mt-2 text-xl font-black text-slate-950">Situation scolaire</h2>
                                </div>

                                <div class="grid gap-5 md:grid-cols-2">
                                    <div>
                                        <x-input-label for="education_level" :value="'Niveau d etude'" />
                                        <select id="education_level" name="education_level" required class="mt-2 block w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-[#2563eb] focus:ring-[#2563eb]">
                                            <option value="">Selectionner</option>
                                            @foreach ([
                                                '1ere_bac' => '1ere annee Baccalaureat',
                                                '2eme_bac' => '2eme annee Baccalaureat',
                                                'bac_plus_1' => 'BAC+1',
                                                'bac_plus_2' => 'BAC+2',
                                                'bac_plus_3' => 'BAC+3',
                                                'bac_plus_4' => 'BAC+4',
                                                'bac_plus_5' => 'BAC+5',
                                                'bac_plus_6' => 'BAC+6',
                                                'doctorant' => 'Doctorant',
                                            ] as $value => $label)
                                                <option value="{{ $value }}" @selected(old('education_level', $profile->education_level ?? '') === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <x-input-error :messages="$errors->get('education_level')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="school_name" :value="'Etablissement'" />
                                        <x-text-input id="school_name" name="school_name" type="text" required class="mt-2 block w-full" placeholder="Nom de ton etablissement" value="{{ old('school_name', $profile->school_name ?? '') }}" />
                                        <x-input-error :messages="$errors->get('school_name')" class="mt-2" />
                                    </div>

                                    <div id="bac-type-section">
                                        <x-input-label for="bac_type" :value="'Type de baccalaureat'" />
                                        <select id="bac_type" name="bac_type" class="mt-2 block w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-[#2563eb] focus:ring-[#2563eb]">
                                            <option value="">Selectionner</option>
                                            <option value="marocain" @selected(old('bac_type', $profile->bac_type ?? '') === 'marocain')>Marocain</option>
                                            <option value="mission" @selected(old('bac_type', $profile->bac_type ?? '') === 'mission')>Mission</option>
                                        </select>
                                        <x-input-error :messages="$errors->get('bac_type')" class="mt-2" />
                                    </div>

                                    <div id="bac-field-section">
                                        <x-input-label for="bac_field" :value="'Filiere du baccalaureat'" />
                                        <select id="bac_field" name="bac_field" class="mt-2 block w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-[#2563eb] focus:ring-[#2563eb]">
                                            <option value="">Selectionner</option>
                                            @foreach ([
                                                'sciences_physiques' => 'Sciences Physiques',
                                                'sciences_vie' => 'Sciences de la Vie et de la Terre',
                                                'sciences_math' => 'Sciences Mathematiques',
                                                'sciences_eco' => 'Sciences Economiques',
                                                'lettres_humaines' => 'Lettres et Sciences Humaines',
                                                'tech_elec' => 'Sciences et Technologies Electriques',
                                                'tech_meca' => 'Sciences et Technologies Mecaniques',
                                                'arts_appliques' => 'Arts Appliques',
                                            ] as $value => $label)
                                                <option value="{{ $value }}" @selected(old('bac_field', $profile->bac_field ?? '') === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <x-input-error :messages="$errors->get('bac_field')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="school_type" :value="'Type d etablissement actuel'" />
                                        <select id="school_type" name="school_type" required class="mt-2 block w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-[#2563eb] focus:ring-[#2563eb]">
                                            <option value="">Selectionner</option>
                                            <option value="public" @selected(old('school_type', $profile->school_type ?? '') === 'public')>Public</option>
                                            <option value="private" @selected(old('school_type', $profile->school_type ?? '') === 'private')>Prive</option>
                                        </select>
                                        <x-input-error :messages="$errors->get('school_type')" class="mt-2" />
                                    </div>
                                </div>
                            </section>

                            <section class="space-y-5 border-t border-slate-200 pt-8">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#2563eb]">Preferences</p>
                                    <h2 class="mt-2 text-xl font-black text-slate-950">Formations et services</h2>
                                </div>

                                <div>
                                    <x-input-label :value="'Types d etablissements preferes'" />
                                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                        @foreach (['public' => 'Public', 'private' => 'Prive', 'military' => 'Militaire', 'semi-public' => 'Semi-public'] as $value => $label)
                                            <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700">
                                                <input type="checkbox" name="preferred_school_types[]" value="{{ $value }}" class="h-4 w-4 rounded accent-[#2563eb]" @checked(in_array($value, $preferredTypes, true))>
                                                {{ $label }}
                                            </label>
                                        @endforeach
                                    </div>
                                    <x-input-error :messages="$errors->get('preferred_school_types')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label :value="'Services qui t interessent'" />
                                    <div class="mt-3 grid gap-3">
                                        @foreach ([
                                            'orientation_session' => 'Seance d orientation avec un conseiller',
                                            'school_registration' => 'Accompagnement aux inscriptions',
                                            'notifications' => 'Notifications concours et opportunites',
                                        ] as $value => $label)
                                            <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700">
                                                <input type="checkbox" name="interested_services[]" value="{{ $value }}" class="h-4 w-4 rounded accent-[#2563eb]" @checked(in_array($value, $services, true))>
                                                {{ $label }}
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </section>

                            <section class="space-y-5 border-t border-slate-200 pt-8">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#2563eb]">Personnel</p>
                                    <h2 class="mt-2 text-xl font-black text-slate-950">Informations de contact</h2>
                                </div>

                                <div class="grid gap-5 md:grid-cols-2">
                                    <div>
                                        <x-input-label for="birth_date" :value="'Date de naissance'" />
                                        <x-text-input id="birth_date" name="birth_date" type="date" required class="mt-2 block w-full" value="{{ old('birth_date', $profile->birth_date?->format('Y-m-d') ?? '') }}" />
                                        <x-input-error :messages="$errors->get('birth_date')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="gender" :value="'Genre'" />
                                        <select id="gender" name="gender" required class="mt-2 block w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-[#2563eb] focus:ring-[#2563eb]">
                                            <option value="">Selectionner</option>
                                            <option value="masculine" @selected(old('gender', $profile->gender ?? '') === 'masculine')>Masculin</option>
                                            <option value="feminine" @selected(old('gender', $profile->gender ?? '') === 'feminine')>Feminin</option>
                                        </select>
                                        <x-input-error :messages="$errors->get('gender')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="city" :value="'Ville'" />
                                        <x-text-input id="city" name="city" type="text" required class="mt-2 block w-full" value="{{ old('city', $profile->city ?? '') }}" />
                                        <x-input-error :messages="$errors->get('city')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="phone" :value="'Telephone'" />
                                        <x-text-input id="phone" name="phone" type="tel" class="mt-2 block w-full" value="{{ old('phone', $user->phone ?? '') }}" placeholder="06 XX XX XX XX" />
                                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                                    </div>
                                </div>
                            </section>

                            <section class="space-y-5 border-t border-slate-200 pt-8">
                                <div class="rounded-2xl border border-blue-100 bg-[#eff6ff] p-5 text-sm leading-6 text-slate-700">
                                    <p class="font-bold text-slate-950">Consentement</p>
                                    <p class="mt-2">
                                        OrientationTech peut te contacter pour expliquer les services, proposer un accompagnement et t informer des opportunites utiles pour ton orientation.
                                    </p>
                                </div>

                                <label class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-white p-4 text-sm font-semibold text-slate-700">
                                    <input type="checkbox" name="consent_contact" value="1" required class="mt-1 h-5 w-5 accent-[#2563eb]" @checked(old('consent_contact', $profile->consent_contact ?? false))>
                                    <span>J accepte d etre contacte par OrientationTech dans le cadre de mon accompagnement d orientation.</span>
                                </label>
                                <x-input-error :messages="$errors->get('consent_contact')" class="mt-2" />
                            </section>

                            <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-8 sm:flex-row sm:justify-end">
                                <a href="{{ route('profile.edit') }}" class="inline-flex items-center justify-center rounded-xl border border-blue-200 bg-white px-5 py-3 text-sm font-bold text-[#2563eb] transition hover:bg-blue-50">
                                    Retour au profil
                                </a>
                                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-[#2563eb] px-6 py-3 text-sm font-bold text-white shadow-lg shadow-blue-600/20 transition hover:bg-[#1d4ed8]">
                                    Enregistrer mon profil eleve
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script>
        (function () {
            const educationLevel = document.getElementById('education_level');
            const bacTypeSection = document.getElementById('bac-type-section');
            const bacFieldSection = document.getElementById('bac-field-section');
            const bacType = document.getElementById('bac_type');
            const bacField = document.getElementById('bac_field');

            function toggleBacFields() {
                const value = educationLevel.value || '';
                const isBacLevel = value === '1ere_bac' || value === '2eme_bac';

                bacTypeSection.classList.toggle('hidden', !isBacLevel);
                bacFieldSection.classList.toggle('hidden', !isBacLevel);
                bacType.required = isBacLevel;
                bacField.required = isBacLevel;
            }

            educationLevel.addEventListener('change', toggleBacFields);
            toggleBacFields();
        })();
    </script>
</x-guest-layout>
