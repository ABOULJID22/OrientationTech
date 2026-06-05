<x-filament-panels::page>
    @php
        $report = is_array($this->report ?? null) ? $this->report : [];

        $globalSummary = is_array($report['global_summary'] ?? null) ? $report['global_summary'] : [];
        $pedagogical = is_array($report['pedagogical_analysis'] ?? null) ? $report['pedagogical_analysis'] : [];
        $competences = is_array($report['competences_analysis'] ?? null) ? $report['competences_analysis'] : [];
        $personality = is_array($report['personality_analysis'] ?? null) ? $report['personality_analysis'] : [];
        $ikigai = is_array($report['ikigai_analysis'] ?? null) ? $report['ikigai_analysis'] : [];
        $futureDomains = collect($report['future_domains_analysis'] ?? [])->take(6);
        $strengths = is_array($report['strengths_radar'] ?? null) ? $report['strengths_radar'] : [];
        $filieres = collect($report['compatibility_filiales'] ?? [])->take(5);
        $orientationRec = collect($report['orientation_recommended'] ?? [])->take(3);
        $schools = is_array($report['schools_recommended'] ?? null) ? $report['schools_recommended'] : [];
        $careers = collect($report['compatible_careers'] ?? [])->take(8);
        $educationPaths = is_array($report['education_paths'] ?? null) ? $report['education_paths'] : [];
        $success = is_array($report['success_predictions'] ?? null) ? $report['success_predictions'] : [];
        $actionPlan = is_array($report['action_plan'] ?? null) ? $report['action_plan'] : [];
        $moroccoPaths = is_array($educationPaths['morocco'] ?? null) ? $educationPaths['morocco'] : [];
        $internationalPaths = is_array($educationPaths['international'] ?? null) ? $educationPaths['international'] : [];

        $globalScore = max(0, min(100, (int) ($globalSummary['global_score'] ?? $success['success_probability'] ?? 0)));
        $confidence = $success['confidence_level'] ?? 'Moyenne';
        $stars = max(1, min(5, (int) ($success['star_rating'] ?? 3)));

        $asList = function ($value): array {
            if (is_array($value)) {
                return collect($value)->flatten(1)->map(fn ($item) => is_array($item) ? implode(' - ', $item) : (string) $item)->filter()->values()->all();
            }

            return filled($value) ? [(string) $value] : [];
        };

        $scoreColor = function (int|float $score): string {
            return $score >= 80 ? '#2563eb' : ($score >= 65 ? '#3b82f6' : '#f59e0b');
        };

        $competenceLabels = [
            'mathematics' => 'Mathematiques',
            'physics' => 'Sciences physiques',
            'informatics' => 'Informatique',
            'french' => 'Francais',
            'english' => 'Anglais',
            'arabic' => 'Arabe',
            'communication' => 'Communication',
            'problem_solving' => 'Resolution de problemes',
            'creativity' => 'Creativite',
            'leadership' => 'Leadership',
        ];

        $moroccoPathLabels = [
            'college' => 'College',
            'lycee' => 'Lycee',
            'bts_dut' => 'BTS / DUT / EST',
            'fp_fs' => 'FP / OFPPT / FS / FST',
            'licences' => 'Licences',
            'engineering_schools' => 'Ecoles d ingenieurs',
            'universities' => 'Universites',
        ];

        $internationalPathLabels = [
            'degrees' => 'Formations reconnues',
            'certifications' => 'Certifications',
            'platforms' => 'Plateformes',
        ];
    @endphp

    <div class="or-report mx-auto max-w-7xl space-y-6 px-4 py-4 sm:px-6 lg:px-8">
        <style>
            .or-report {
                color: #0f172a;
            }

            .or-hero {
                overflow: hidden;
                border: 1px solid rgba(37, 99, 235, 0.14);
                background:
                    radial-gradient(circle at 92% 8%, rgba(37, 99, 235, 0.18), transparent 30%),
                    linear-gradient(135deg, #ffffff 0%, #eff6ff 55%, #dbeafe 100%);
                border-radius: 24px;
                box-shadow: 0 18px 48px rgba(15, 23, 42, 0.08);
            }

            .or-card {
                border: 1px solid rgba(148, 163, 184, 0.18);
                background: rgba(255, 255, 255, 0.96);
                border-radius: 16px;
                box-shadow: 0 10px 28px rgba(15, 23, 42, 0.04);
            }

            .or-flat {
                border: 1px solid rgba(148, 163, 184, 0.18);
                background: #f8fafc;
                border-radius: 14px;
            }

            .or-label {
                font-size: 0.72rem;
                font-weight: 800;
                letter-spacing: 0.12em;
                text-transform: uppercase;
                color: #64748b;
            }

            .or-chip {
                display: inline-flex;
                align-items: center;
                gap: 0.4rem;
                border-radius: 999px;
                border: 1px solid rgba(37, 99, 235, 0.16);
                background: #eff6ff;
                padding: 0.45rem 0.75rem;
                font-size: 0.76rem;
                font-weight: 800;
                color: #1d4ed8;
                white-space: nowrap;
            }

            .or-action {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 999px;
                padding: 0.72rem 1rem;
                font-size: 0.875rem;
                font-weight: 800;
                transition: all 160ms ease;
            }

            .or-action-primary {
                background: #2563eb;
                color: #fff;
                box-shadow: 0 12px 28px rgba(37, 99, 235, 0.22);
            }

            .or-action-primary:hover {
                background: #1d4ed8;
            }

            .or-score-ring {
                width: 9.5rem;
                height: 9.5rem;
                border-radius: 999px;
                display: grid;
                place-items: center;
                background:
                    radial-gradient(circle at center, #fff 0 58%, transparent 59%),
                    conic-gradient(#2563eb calc(var(--score) * 1%), #dbeafe 0);
                box-shadow: 0 18px 42px rgba(37, 99, 235, 0.18);
            }

            .or-progress {
                height: 0.75rem;
                overflow: hidden;
                border-radius: 999px;
                background: #e2e8f0;
            }

            .or-progress > span {
                display: block;
                height: 100%;
                border-radius: 999px;
                background: linear-gradient(90deg, #2563eb, #60a5fa);
            }

            .or-section-title {
                display: flex;
                align-items: center;
                gap: 0.8rem;
            }

            .or-section-number {
                display: grid;
                height: 2rem;
                width: 2rem;
                place-items: center;
                border-radius: 999px;
                background: #2563eb;
                color: #fff;
                font-size: 0.85rem;
                font-weight: 900;
                flex: 0 0 auto;
            }

            .or-section-heading {
                font-size: 1.25rem;
                font-weight: 900;
                letter-spacing: -0.01em;
                color: #0f172a;
            }

            .or-mini-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(14rem, 1fr));
                gap: 0.9rem;
            }

            .or-timeline {
                display: grid;
                gap: 0.9rem;
            }

            .or-timeline-item {
                display: grid;
                grid-template-columns: 2.25rem 1fr;
                gap: 0.8rem;
                align-items: start;
            }

            .or-dot {
                display: grid;
                height: 2.25rem;
                width: 2.25rem;
                place-items: center;
                border-radius: 999px;
                background: #dbeafe;
                color: #1d4ed8;
                font-weight: 900;
            }

            .or-stars {
                letter-spacing: 0.16rem;
                color: #f59e0b;
                font-size: 1.25rem;
            }
        </style>

        @if(empty($report))
            <section class="or-card p-6">
                <p class="or-label">Rapport indisponible</p>
                <h2 class="mt-2 text-2xl font-black text-slate-950">Le rapport n est pas encore pret</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600">
                    Complete le diagnostic academique et le test personnalise pour generer une analyse complete.
                </p>
            </section>
        @else
            <section class="or-hero p-5 sm:p-7 lg:p-8">
                <div class="grid gap-6 lg:grid-cols-[1.25fr_0.75fr] lg:items-center">
                    <div class="space-y-4">
                        <span class="or-chip">Rapport professionnel OrientationTech</span>
                        <div>
                            <h1 class="max-w-4xl text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">
                                {{ $this->user?->name ?? 'Eleve' }}
                            </h1>
                            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600 sm:text-base">
                                Analyse personnalisee construite a partir du diagnostic academique, du module Ikigai, du test de personnalite et des recommandations numeriques adaptees au contexte marocain.
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <span class="or-chip">{{ $this->diagnostic?->academic_level ?? 'Niveau non renseigne' }}</span>
                            <span class="or-chip">{{ $this->diagnostic?->interest_theme ?? 'Interet a preciser' }}</span>
                            <span class="or-chip">Genere le {{ now()->format('d/m/Y') }}</span>
                        </div>

                        <button type="button" wire:click="exportPdf" wire:loading.attr="disabled" class="or-action or-action-primary">
                            <span wire:loading.remove wire:target="exportPdf">Exporter le rapport PDF</span>
                            <span wire:loading wire:target="exportPdf">Generation du PDF...</span>
                        </button>
                    </div>

                    <div class="or-card p-5">
                        <div class="flex items-center justify-center">
                            <div class="or-score-ring" style="--score: {{ $globalScore }}">
                                <div class="text-center">
                                    <div class="text-3xl font-black text-slate-950">{{ $globalScore }}/100</div>
                                    <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Score global</div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 grid gap-3">
                            <div class="or-flat p-4">
                                <p class="or-label">Profil dominant</p>
                                <p class="mt-1 font-black text-slate-950">{{ $globalSummary['dominant_profile'] ?? 'Profil a preciser' }}</p>
                            </div>
                            <div class="or-flat p-4">
                                <p class="or-label">Potentiel</p>
                                <p class="mt-1 font-black text-blue-700">{{ $globalSummary['overall_potential'] ?? 'Potentiel a developper' }}</p>
                            </div>
                            <div class="or-flat p-4">
                                <p class="or-label">Confiance</p>
                                <p class="mt-1 font-black text-slate-950">{{ $confidence }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="or-card p-5 sm:p-6">
                <div class="or-section-title">
                    <span class="or-section-number">1</span>
                    <h2 class="or-section-heading">Resume global du profil</h2>
                </div>
                <p class="mt-4 max-w-5xl text-sm leading-7 text-slate-700">
                    {{ $globalSummary['summary_text'] ?? 'Le rapport est base sur les resultats reels disponibles.' }}
                </p>

                <div class="mt-5 grid gap-4 lg:grid-cols-2">
                    <div class="or-flat p-4">
                        <p class="font-black text-slate-950">Forces principales</p>
                        <ul class="mt-3 space-y-2 text-sm leading-6 text-slate-700">
                            @forelse($asList($globalSummary['main_strengths'] ?? []) as $strength)
                                <li>{{ $strength }}</li>
                            @empty
                                <li>Les forces seront precisees apres consolidation des resultats.</li>
                            @endforelse
                        </ul>
                    </div>
                    <div class="or-flat p-4">
                        <p class="font-black text-slate-950">Axes d amelioration</p>
                        <ul class="mt-3 space-y-2 text-sm leading-6 text-slate-700">
                            @forelse($asList($globalSummary['improvement_axes'] ?? []) as $axis)
                                <li>{{ $axis }}</li>
                            @empty
                                <li>Continuer a renforcer les bases, la pratique et l anglais technique.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </section>

            <section class="or-card p-5 sm:p-6">
                <div class="or-section-title">
                    <span class="or-section-number">2</span>
                    <h2 class="or-section-heading">Analyse intelligente et pedagogique</h2>
                </div>
                <div class="mt-5 grid gap-4 lg:grid-cols-3">
                    <div class="or-flat p-4">
                        <p class="or-label">Pour l eleve</p>
                        <p class="mt-2 text-sm leading-7 text-slate-700">{{ $pedagogical['student_explanation'] ?? 'Comprendre ses forces et choisir progressivement.' }}</p>
                    </div>
                    <div class="or-flat p-4">
                        <p class="or-label">Pour les parents</p>
                        <p class="mt-2 text-sm leading-7 text-slate-700">{{ $pedagogical['parents_explanation'] ?? 'Accompagner sans imposer, avec des preuves issues des tests.' }}</p>
                    </div>
                    <div class="or-flat p-4">
                        <p class="or-label">Pour les enseignants</p>
                        <p class="mt-2 text-sm leading-7 text-slate-700">{{ $pedagogical['teachers_explanation'] ?? 'Aider l eleve par projets, feedback et suivi.' }}</p>
                    </div>
                </div>
            </section>

            <section class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
                <div class="or-card p-5 sm:p-6">
                    <div class="or-section-title">
                        <span class="or-section-number">3</span>
                        <h2 class="or-section-heading">Personnalite et Ikigai</h2>
                    </div>
                    <div class="mt-5 grid gap-3">
                        <div class="or-flat p-4">
                            <p class="or-label">Profil psychologique</p>
                            <p class="mt-2 font-black text-slate-950">{{ $personality['dominant_profile'] ?? 'Profil non determine' }}</p>
                        </div>
                        <div class="or-flat p-4">
                            <p class="or-label">Style d apprentissage</p>
                            <p class="mt-2 text-sm leading-7 text-slate-700">{{ $personality['learning_style'] ?? 'Apprentissage progressif par exemples et projets.' }}</p>
                        </div>
                        <div class="or-flat p-4">
                            <p class="or-label">Decision et travail</p>
                            <p class="mt-2 text-sm leading-7 text-slate-700">
                                {{ $personality['decision_making'] ?? 'Decision reflechie' }} - {{ $personality['teamwork_style'] ?? 'Collaboration a developper selon le contexte' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="or-card p-5 sm:p-6">
                    <div class="or-section-title">
                        <span class="or-section-number">IK</span>
                        <h2 class="or-section-heading">Synthese Ikigai</h2>
                    </div>
                    <div class="mt-5 or-mini-grid">
                        <div class="or-flat p-4">
                            <p class="or-label">Ce qu il aime</p>
                            <p class="mt-2 text-sm leading-6 text-slate-700">{{ $ikigai['love'] ?? 'A preciser' }}</p>
                        </div>
                        <div class="or-flat p-4">
                            <p class="or-label">Ce dans quoi il excelle</p>
                            <p class="mt-2 text-sm leading-6 text-slate-700">{{ $ikigai['good_at'] ?? 'A preciser' }}</p>
                        </div>
                        <div class="or-flat p-4">
                            <p class="or-label">Besoin du monde</p>
                            <p class="mt-2 text-sm leading-6 text-slate-700">{{ $ikigai['world_needs'] ?? 'Solutions numeriques utiles et responsables.' }}</p>
                        </div>
                        <div class="or-flat p-4">
                            <p class="or-label">Profession possible</p>
                            <p class="mt-2 text-sm leading-6 text-slate-700">{{ $ikigai['profession'] ?? 'Metiers numeriques a explorer.' }}</p>
                        </div>
                    </div>
                    @if(!empty($ikigai['intersection']))
                        <p class="mt-4 rounded-xl bg-blue-50 p-4 text-sm leading-7 text-blue-900">{{ $ikigai['intersection'] }}</p>
                    @endif
                </div>
            </section>

            <section class="or-card p-5 sm:p-6">
                <div class="or-section-title">
                    <span class="or-section-number">4</span>
                    <h2 class="or-section-heading">Analyse des competences</h2>
                </div>
                <div class="mt-5 grid gap-3 md:grid-cols-2">
                    @foreach($competenceLabels as $key => $label)
                        @php
                            $score = max(0, min(100, (int) ($competences[$key] ?? 0)));
                        @endphp
                        <div class="or-flat p-4">
                            <div class="flex items-center justify-between gap-3">
                                <p class="font-bold text-slate-950">{{ $label }}</p>
                                <span class="font-black text-blue-700">{{ $score }}%</span>
                            </div>
                            <div class="or-progress mt-3">
                                <span style="width: {{ $score }}%; background: {{ $scoreColor($score) }}"></span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="grid gap-6 xl:grid-cols-2">
                <div class="or-card p-5 sm:p-6">
                    <div class="or-section-title">
                        <span class="or-section-number">5</span>
                        <h2 class="or-section-heading">Domaines recommandes</h2>
                    </div>
                    <div class="mt-5 space-y-3">
                        @forelse($futureDomains as $domain)
                            @php
                                $domain = is_array($domain) ? $domain : ['name' => (string) $domain];
                                $compatibility = max(0, min(100, (int) ($domain['compatibility'] ?? 0)));
                            @endphp
                            <div class="or-flat p-4">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <p class="font-black text-slate-950">{{ $domain['name'] ?? 'Domaine numerique' }}</p>
                                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $domain['why'] ?? 'Compatibilite deduite du diagnostic et du test personnalise.' }}</p>
                                    </div>
                                    <span class="or-chip">{{ $compatibility }}%</span>
                                </div>
                                <div class="or-progress mt-3"><span style="width: {{ $compatibility }}%"></span></div>
                                @if(!empty($domain['ai_impact']))
                                    <p class="mt-3 text-xs leading-5 text-slate-500"><strong>Impact IA:</strong> {{ $domain['ai_impact'] }}</p>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">Aucun domaine recommande pour le moment.</p>
                        @endforelse
                    </div>
                </div>

                <div class="or-card p-5 sm:p-6">
                    <div class="or-section-title">
                        <span class="or-section-number">6</span>
                        <h2 class="or-section-heading">Orientations marocaines</h2>
                    </div>
                    <div class="mt-5 space-y-3">
                        @forelse($orientationRec as $index => $choice)
                            @php
                                $choice = is_array($choice) ? $choice : ['filiere' => (string) $choice];
                                $compatibility = max(0, min(100, (int) ($choice['compatibility'] ?? 0)));
                            @endphp
                            <div class="or-timeline-item or-flat p-4">
                                <div class="or-dot">{{ $index + 1 }}</div>
                                <div>
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                        <p class="font-black text-slate-950">{{ $choice['filiere'] ?? 'Orientation recommandee' }}</p>
                                        <span class="font-black text-blue-700">{{ $compatibility }}%</span>
                                    </div>
                                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ $choice['justification'] ?? 'Recommandation basee sur les resultats.' }}</p>
                                </div>
                            </div>
                        @empty
                            @foreach($filieres as $index => $item)
                                @php $item = is_array($item) ? $item : ['filiere' => (string) $item]; @endphp
                                <div class="or-timeline-item or-flat p-4">
                                    <div class="or-dot">{{ $index + 1 }}</div>
                                    <div>
                                        <p class="font-black text-slate-950">{{ $item['filiere'] ?? 'Filiere numerique' }}</p>
                                        <p class="mt-1 text-sm text-blue-700">{{ $item['compatibility'] ?? 0 }}% compatible</p>
                                    </div>
                                </div>
                            @endforeach
                        @endforelse
                    </div>
                </div>
            </section>

            <section class="or-card p-5 sm:p-6">
                <div class="or-section-title">
                    <span class="or-section-number">7</span>
                    <h2 class="or-section-heading">Metiers compatibles</h2>
                </div>
                <div class="mt-5 grid gap-4 lg:grid-cols-2">
                    @forelse($careers as $career)
                        @php $career = is_array($career) ? $career : ['career' => (string) $career]; @endphp
                        <div class="or-flat p-4">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="font-black text-slate-950">{{ $career['career'] ?? 'Metier recommande' }}</p>
                                    <p class="text-xs font-bold text-slate-500">{{ $career['domain'] ?? 'Domaine numerique' }}</p>
                                </div>
                                <span class="or-chip">{{ $career['compatibility'] ?? 0 }}%</span>
                            </div>
                            <p class="mt-3 text-sm leading-6 text-slate-600">{{ $career['job_outlook'] ?? 'Perspectives favorables avec la transformation digitale.' }}</p>
                            @if(!empty($career['ai_impact']))
                                <p class="mt-2 text-xs leading-5 text-slate-500"><strong>IA:</strong> {{ $career['ai_impact'] }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Aucun metier compatible pour le moment.</p>
                    @endforelse
                </div>
            </section>

            <section class="or-card p-5 sm:p-6">
                <div class="or-section-title">
                    <span class="or-section-number">8</span>
                    <h2 class="or-section-heading">Parcours de formation</h2>
                </div>
                <div class="mt-5 grid gap-5 xl:grid-cols-2">
                    <div>
                        <p class="or-label">Au Maroc</p>
                        <div class="mt-3 space-y-3">
                            @foreach($moroccoPathLabels as $key => $label)
                                @php $items = $asList($moroccoPaths[$key] ?? []); @endphp
                                @if(!empty($items))
                                    <div class="or-flat p-4">
                                        <p class="font-black text-slate-950">{{ $label }}</p>
                                        <ul class="mt-2 space-y-1 text-sm leading-6 text-slate-700">
                                            @foreach($items as $item)
                                                <li>{{ $item }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <p class="or-label">International et apprentissage en ligne</p>
                        <div class="mt-3 space-y-3">
                            @foreach($internationalPathLabels as $key => $label)
                                @php $items = $asList($internationalPaths[$key] ?? []); @endphp
                                @if(!empty($items))
                                    <div class="or-flat p-4">
                                        <p class="font-black text-slate-950">{{ $label }}</p>
                                        <ul class="mt-2 space-y-1 text-sm leading-6 text-slate-700">
                                            @foreach($items as $item)
                                                <li>{{ $item }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            @if(!empty($schools))
                <section class="or-card p-5 sm:p-6">
                    <div class="or-section-title">
                        <span class="or-section-number">9</span>
                        <h2 class="or-section-heading">Ecoles et etablissements recommandes</h2>
                    </div>
                    <div class="mt-5 grid gap-4 lg:grid-cols-2">
                        @foreach($schools as $domain => $schoolList)
                            <div class="or-flat p-4">
                                <p class="or-label">{{ $domain }}</p>
                                <div class="mt-3 space-y-3">
                                    @foreach(collect($schoolList)->take(4) as $school)
                                        @php $school = is_array($school) ? $school : ['school' => (string) $school]; @endphp
                                        <div class="rounded-xl bg-white p-3">
                                            <p class="font-black text-slate-950">{{ $school['school'] ?? 'Etablissement' }}</p>
                                            <p class="mt-1 text-xs leading-5 text-slate-500">{{ $school['access_level'] ?? 'Acces a verifier' }} - {{ $school['conditions'] ?? 'Conditions a verifier' }}</p>
                                            <p class="mt-1 text-xs leading-5 text-slate-600">{{ $school['strengths'] ?? 'Parcours pertinent selon le profil.' }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <section class="grid gap-6 xl:grid-cols-[0.8fr_1.2fr]">
                <div class="or-card p-5 sm:p-6">
                    <div class="or-section-title">
                        <span class="or-section-number">10</span>
                        <h2 class="or-section-heading">Probabilite de reussite</h2>
                    </div>
                    <div class="mt-5 grid gap-3">
                        <div class="or-flat p-4">
                            <p class="or-label">Probabilite</p>
                            <p class="mt-2 text-3xl font-black text-slate-950">{{ $success['success_probability'] ?? $globalScore }}%</p>
                        </div>
                        <div class="or-flat p-4">
                            <p class="or-label">Confiance</p>
                            <p class="mt-2 text-xl font-black text-slate-950">{{ $confidence }}</p>
                        </div>
                        <div class="or-flat p-4">
                            <p class="or-label">Potentiel</p>
                            <p class="or-stars mt-2">{{ str_repeat('*', $stars) }}{{ str_repeat('-', 5 - $stars) }}</p>
                        </div>
                    </div>
                </div>

                <div class="or-card p-5 sm:p-6">
                    <div class="or-section-title">
                        <span class="or-section-number">11</span>
                        <h2 class="or-section-heading">Plan d action personnalise</h2>
                    </div>
                    <div class="or-timeline mt-5">
                        @foreach([
                            'short_term' => ['1 an', $actionPlan['short_term'] ?? []],
                            'medium_term' => ['3 ans', $actionPlan['medium_term'] ?? []],
                            'long_term' => ['5 ans', $actionPlan['long_term'] ?? []],
                        ] as $period)
                            <div class="or-timeline-item">
                                <div class="or-dot">{{ $loop->iteration }}</div>
                                <div class="or-flat p-4">
                                    <p class="font-black text-slate-950">Objectif sur {{ $period[0] }}</p>
                                    <ul class="mt-2 space-y-1 text-sm leading-6 text-slate-700">
                                        @forelse($asList($period[1]) as $item)
                                            <li>{{ $item }}</li>
                                        @empty
                                            <li>Explorer les domaines recommandes et realiser des projets concrets.</li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="or-card p-5 sm:p-6">
                <div class="or-section-title">
                    <span class="or-section-number">12</span>
                    <h2 class="or-section-heading">Validation OrientationTech</h2>
                </div>
                <div class="mt-5 grid gap-4 lg:grid-cols-[1.2fr_0.8fr]">
                    <p class="or-flat p-4 text-sm leading-7 text-slate-700">
                        Ce rapport est un support d aide a la decision. Il ne remplace pas l accompagnement humain, mais il aide a structurer la discussion entre l eleve, les parents et les enseignants a partir de donnees observables.
                    </p>
                    <div class="or-flat p-4">
                        <p class="or-label">Signature</p>
                        <p class="mt-2 text-xl font-black text-blue-700">OrientationTech</p>
                        <p class="mt-1 text-sm leading-6 text-slate-600">Conseiller d orientation - Systeme IA</p>
                        <p class="mt-3 text-xs font-bold text-slate-500">{{ now()->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </section>
        @endif
    </div>
</x-filament-panels::page>
