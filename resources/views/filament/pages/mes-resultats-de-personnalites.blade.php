<x-filament-panels::page>
    @php
        $diagnosticDone = $this->diagnosticCompleted();
        $personnaliseDone = $this->personnaliseCompleted();
        $bothDone = $this->hasBothTestsCompleted();

        $recommendations = $this->mergedResult['ai_recommendations'] ?? [];
        $recommendations = is_array($recommendations) ? $recommendations : [];
        $analysis = $recommendations['intelligent_analysis'] ?? [];
        $analysis = is_array($analysis) ? $analysis : [];
        $recommendedDomains = collect($recommendations['recommended_domains'] ?? [])->take(6);
        $jobRoles = collect($recommendations['job_roles'] ?? [])->take(6);
        $programChoices = collect($recommendations['program_choices'] ?? [])->take(6);
        $diagnosticContext = $this->mergedResult['diagnostic'] ?? [];
        $diagnosticContext = is_array($diagnosticContext) ? $diagnosticContext : [];
        $summary = $this->mergedResult['narrative'] ?? 'Complete les deux tests pour obtenir une synthese personnalisee.';
        $aiMessage = $this->mergedResult['ai_status']['message'] ?? 'Analyse locale basee sur tes resultats.';

        $domainLabels = \App\Services\TestPersonnalises\TestPersonnaliseResultService::domainLabels();
        $mergedDomainScores = collect($this->mergedResult['personnalise']['merged_domain_scores'] ?? $this->personnalise?->domain_scores ?? [])->sortDesc();
        $primaryDomain = $mergedDomainScores->keys()->get(0) ?? $this->personnalise?->primary_domain;
        $secondaryDomain = $mergedDomainScores->keys()->get(1) ?? $this->personnalise?->secondary_domain;
        $primaryDomainLabel = $primaryDomain ? ($domainLabels[$primaryDomain] ?? $primaryDomain) : '-';
        $secondaryDomainLabel = $secondaryDomain ? ($domainLabels[$secondaryDomain] ?? $secondaryDomain) : '-';

        $axisScores = collect($this->personnalise?->axis_scores ?? [])->sortDesc()->take(7);
        $itScores = $mergedDomainScores->take(7);
        $axisLabels = $axisScores->keys()->values()->all();
        $axisValues = $axisScores->values()->map(fn ($value) => (float) $value)->all();
        $itLabels = $itScores->keys()->map(fn ($key) => $domainLabels[$key] ?? $key)->values()->all();
        $itValues = $itScores->values()->map(fn ($value) => (float) $value)->all();
        $chartIdSuffix = 'orientation-result-' . ($this->personnalise?->id ?? 'new');
    @endphp

    <div class="ot-page mx-auto max-w-7xl space-y-6 px-4 py-4 sm:px-6 lg:px-8">
        <style>
            .ot-page {
                color: #0f172a;
            }

            .ot-hero {
                overflow: hidden;
                border: 1px solid rgba(37, 99, 235, 0.14);
                background:
                    radial-gradient(circle at 92% 0%, rgba(37, 99, 235, 0.16), transparent 30%),
                    linear-gradient(135deg, #ffffff 0%, #eff6ff 54%, #dbeafe 100%);
                border-radius: 22px;
                box-shadow: 0 18px 48px rgba(15, 23, 42, 0.08);
            }

            .ot-card {
                border: 1px solid rgba(148, 163, 184, 0.18);
                background: rgba(255, 255, 255, 0.94);
                border-radius: 16px;
                box-shadow: 0 10px 28px rgba(15, 23, 42, 0.04);
            }

            .ot-flat {
                border: 1px solid rgba(148, 163, 184, 0.18);
                background: #f8fafc;
                border-radius: 14px;
            }

            .ot-chip {
                display: inline-flex;
                align-items: center;
                gap: 0.4rem;
                border-radius: 999px;
                border: 1px solid rgba(37, 99, 235, 0.16);
                background: #eff6ff;
                padding: 0.45rem 0.75rem;
                font-size: 0.76rem;
                font-weight: 700;
                color: #1d4ed8;
                white-space: nowrap;
            }

            .ot-step {
                position: relative;
                display: grid;
                grid-template-columns: 2.25rem 1fr;
                gap: 0.75rem;
                align-items: start;
            }

            .ot-step-number {
                display: grid;
                height: 2.25rem;
                width: 2.25rem;
                place-items: center;
                border-radius: 999px;
                background: #dbeafe;
                color: #1d4ed8;
                font-size: 0.85rem;
                font-weight: 800;
            }

            .ot-step.is-done .ot-step-number {
                background: #2563eb;
                color: #fff;
            }

            .ot-chart-box {
                height: 280px;
                min-height: 280px;
                max-height: 280px;
                position: relative;
            }

            .ot-chart-box canvas {
                display: block;
                width: 100% !important;
                height: 100% !important;
            }

            .ot-label {
                font-size: 0.72rem;
                font-weight: 800;
                letter-spacing: 0.12em;
                text-transform: uppercase;
                color: #64748b;
            }

            .ot-action {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 999px;
                padding: 0.72rem 1rem;
                font-size: 0.875rem;
                font-weight: 700;
                transition: all 160ms ease;
            }

            .ot-action-primary {
                background: #2563eb;
                color: #fff;
                box-shadow: 0 12px 28px rgba(37, 99, 235, 0.22);
            }

            .ot-action-primary:hover {
                background: #1d4ed8;
            }

            .ot-action-soft {
                border: 1px solid rgba(37, 99, 235, 0.18);
                background: #fff;
                color: #1d4ed8;
            }

            .ot-action-soft:hover {
                background: #eff6ff;
            }
        </style>

        <section class="ot-hero p-5 sm:p-7 lg:p-8">
            <div class="grid gap-6 lg:grid-cols-[1.35fr_0.65fr] lg:items-center">
                <div class="space-y-4">
                    <span class="ot-chip">Mon orientation</span>
                    <div>
                        <h1 class="max-w-3xl text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">
                            Synthese intelligente de ton profil d'orientation
                        </h1>
                        <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600 sm:text-base">
                            Cette page fusionne ton diagnostic academique, ton Ikigai simple et ton test personnalise pour proposer des domaines, metiers et parcours coherents avec ton profil.
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        @if(! $diagnosticDone)
                            <a href="{{ $this->diagnosticLink() }}" class="ot-action ot-action-primary">Commencer le diagnostic</a>
                        @elseif(! $personnaliseDone)
                            <a href="{{ $this->personnaliseLink() }}" class="ot-action ot-action-primary">Passer le test personnalise</a>
                        @else
                            <a href="{{ $this->rapportLink() }}" class="ot-action ot-action-primary">Ouvrir le rapport complet</a>
                            <a href="{{ $this->personnaliseEditLink() }}" class="ot-action ot-action-soft">Ajuster mes reponses</a>
                        @endif
                    </div>
                </div>

                <div class="ot-card p-4">
                    <p class="ot-label">Progression du parcours</p>
                    <div class="mt-4 space-y-4">
                        <div class="ot-step {{ $diagnosticDone ? 'is-done' : '' }}">
                            <div class="ot-step-number">1</div>
                            <div>
                                <p class="font-bold text-slate-950">Diagnostic academique</p>
                                <p class="text-sm leading-6 text-slate-600">{{ $diagnosticDone ? 'Profil scolaire et Ikigai recueillis.' : 'A completer pour comprendre ton contexte scolaire.' }}</p>
                            </div>
                        </div>
                        <div class="ot-step {{ $personnaliseDone ? 'is-done' : '' }}">
                            <div class="ot-step-number">2</div>
                            <div>
                                <p class="font-bold text-slate-950">Test personnalise</p>
                                <p class="text-sm leading-6 text-slate-600">{{ $personnaliseDone ? 'Traits psychometriques analyses.' : 'A completer apres le diagnostic.' }}</p>
                            </div>
                        </div>
                        <div class="ot-step {{ $bothDone ? 'is-done' : '' }}">
                            <div class="ot-step-number">3</div>
                            <div>
                                <p class="font-bold text-slate-950">Resultats & rapport</p>
                                <p class="text-sm leading-6 text-slate-600">{{ $bothDone ? 'Recommandations disponibles.' : 'Disponible quand les deux tests sont termines.' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="ot-card p-4">
                <p class="ot-label">Diagnostic</p>
                <p class="mt-2 text-lg font-black text-slate-950">{{ $diagnosticDone ? 'Termine' : 'A completer' }}</p>
                <p class="mt-1 text-sm text-slate-600">{{ $diagnosticContext['label'] ?? 'Premiere lecture du profil.' }}</p>
            </div>
            <div class="ot-card p-4">
                <p class="ot-label">Test personnalise</p>
                <p class="mt-2 text-lg font-black text-slate-950">{{ $personnaliseDone ? 'Termine' : 'A completer' }}</p>
                <p class="mt-1 text-sm text-slate-600">Creativite, analyse, leadership, autonomie et curiosite.</p>
            </div>
            <div class="ot-card p-4">
                <p class="ot-label">Domaine prioritaire</p>
                <p class="mt-2 text-lg font-black text-slate-950">{{ $primaryDomainLabel }}</p>
                <p class="mt-1 text-sm text-slate-600">Score fusionne diagnostic + personnalite.</p>
            </div>
            <div class="ot-card p-4">
                <p class="ot-label">Domaine secondaire</p>
                <p class="mt-2 text-lg font-black text-slate-950">{{ $secondaryDomainLabel }}</p>
                <p class="mt-1 text-sm text-slate-600">Piste complementaire a explorer.</p>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
            <div class="ot-card p-5 sm:p-6">
                <p class="ot-label">Resume d'orientation</p>
                <h2 class="mt-2 text-xl font-black text-slate-950">Lecture pedagogique du profil</h2>
                <p class="mt-3 text-sm leading-7 text-slate-700">{{ $summary }}</p>
                <p class="mt-4 rounded-xl bg-blue-50 p-3 text-xs font-semibold leading-6 text-blue-800">{{ $aiMessage }}</p>
            </div>

            <div class="ot-card p-5 sm:p-6">
                <p class="ot-label">Diagnostic pris en compte</p>
                <h2 class="mt-2 text-xl font-black text-slate-950">Contexte scolaire et centres d'interet</h2>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div class="ot-flat p-4">
                        <p class="ot-label">Interpretation</p>
                        <p class="mt-2 font-bold text-slate-950">{{ $diagnosticContext['label'] ?? '-' }}</p>
                    </div>
                    <div class="ot-flat p-4">
                        <p class="ot-label">Centre d'interet</p>
                        <p class="mt-2 font-bold text-slate-950">{{ $diagnosticContext['interest_theme_label'] ?? $diagnosticContext['interest_theme'] ?? '-' }}</p>
                    </div>
                    <div class="ot-flat p-4">
                        <p class="ot-label">Specialite</p>
                        <p class="mt-2 font-bold text-slate-950">{{ $diagnosticContext['specialty_label'] ?? $diagnosticContext['specialty_family_label'] ?? $diagnosticContext['specialty_family'] ?? '-' }}</p>
                    </div>
                    <div class="ot-flat p-4">
                        <p class="ot-label">Niveau</p>
                        <p class="mt-2 font-bold text-slate-950">{{ $diagnosticContext['academic_level'] ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </section>

        @if(!empty(array_filter($analysis)))
            <section class="ot-card p-5 sm:p-6">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <p class="ot-label">Analyse intelligente</p>
                        <h2 class="mt-2 text-xl font-black text-slate-950">Forces, ameliorations et compatibilites</h2>
                    </div>
                    @if($bothDone)
                        <button
                            type="button"
                            wire:click="reconnectGemini"
                            wire:loading.attr="disabled"
                            class="ot-action ot-action-soft"
                        >
                            <span wire:loading.remove wire:target="reconnectGemini">Rafraichir l'analyse</span>
                            <span wire:loading wire:target="reconnectGemini">Analyse...</span>
                        </button>
                    @endif
                </div>

                @if(!empty($analysis['compatibility_explanation']))
                    <p class="mt-4 max-w-5xl text-sm leading-7 text-slate-700">{{ $analysis['compatibility_explanation'] }}</p>
                @endif

                <div class="mt-5 grid gap-4 lg:grid-cols-2">
                    <div class="ot-flat p-4">
                        <p class="font-black text-slate-950">Forces detectees</p>
                        <ul class="mt-3 space-y-2 text-sm leading-6 text-slate-700">
                            @forelse($analysis['strengths'] ?? [] as $strength)
                                <li>{{ $strength }}</li>
                            @empty
                                <li>Les forces seront affichees apres calcul complet.</li>
                            @endforelse
                        </ul>
                    </div>
                    <div class="ot-flat p-4">
                        <p class="font-black text-slate-950">Points a developper</p>
                        <ul class="mt-3 space-y-2 text-sm leading-6 text-slate-700">
                            @forelse($analysis['improvements'] ?? [] as $improvement)
                                <li>{{ $improvement }}</li>
                            @empty
                                <li>Les pistes de progression seront affichees apres calcul complet.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </section>
        @endif

        <section class="grid gap-6 xl:grid-cols-2">
            <div class="ot-card p-5 sm:p-6">
                <p class="ot-label">Visualisation</p>
                <h2 class="mt-2 text-xl font-black text-slate-950">Profil de personnalite</h2>
                <div class="ot-chart-box mt-4">
                    <canvas id="axis-chart-{{ $chartIdSuffix }}"></canvas>
                </div>
            </div>

            <div class="ot-card p-5 sm:p-6">
                <p class="ot-label">Compatibilite</p>
                <h2 class="mt-2 text-xl font-black text-slate-950">Domaines numeriques</h2>
                <div class="ot-chart-box mt-4">
                    <canvas id="it-chart-{{ $chartIdSuffix }}"></canvas>
                </div>
            </div>
        </section>

        @if($recommendedDomains->isNotEmpty())
            <section class="ot-card p-5 sm:p-6">
                <p class="ot-label">Recommandations</p>
                <h2 class="mt-2 text-xl font-black text-slate-950">Domaines recommandes</h2>
                <div class="mt-5 grid gap-4 lg:grid-cols-2">
                    @foreach($recommendedDomains as $domain)
                        <div class="ot-flat p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-black text-slate-950">{{ $domain['label'] ?? ($domainLabels[$domain['domain_key'] ?? ''] ?? 'Domaine numerique') }}</p>
                                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ $domain['why'] ?? 'Compatibilite detectee a partir de tes resultats.' }}</p>
                                </div>
                                <span class="ot-chip">{{ $domain['compatibility'] ?? 0 }}%</span>
                            </div>
                            @if(!empty($domain['ai_impact']))
                                <p class="mt-3 text-xs leading-5 text-slate-500"><strong>Impact IA:</strong> {{ $domain['ai_impact'] }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if($jobRoles->isNotEmpty())
            <section class="ot-card p-5 sm:p-6">
                <p class="ot-label">Metiers</p>
                <h2 class="mt-2 text-xl font-black text-slate-950">Metiers compatibles avec ton profil</h2>
                <div class="mt-5 space-y-3">
                    @foreach($jobRoles as $job)
                        <div class="ot-flat p-4">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="font-black text-slate-950">{{ $job['title'] ?? 'Metier recommande' }}</p>
                                    <p class="text-xs font-semibold text-slate-500">{{ $job['domain'] ?? 'Domaine numerique' }}</p>
                                </div>
                                <span class="ot-chip">{{ $job['fit_percent'] ?? 0 }}% compatible</span>
                            </div>

                            @if(!empty($job['missions']))
                                <p class="mt-3 text-xs font-black uppercase tracking-[0.12em] text-slate-500">Missions principales</p>
                                <ul class="mt-2 grid gap-1 text-sm leading-6 text-slate-700 sm:grid-cols-2">
                                    @foreach($job['missions'] as $mission)
                                        <li>{{ $mission }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="ot-card p-5 sm:p-6">
            <p class="ot-label">Parcours de formation</p>
            <h2 class="mt-2 text-xl font-black text-slate-950">Pistes conseillees</h2>
            @if($programChoices->isNotEmpty())
                <div class="mt-4 grid gap-3 lg:grid-cols-2">
                    @foreach($programChoices as $choice)
                        <div class="ot-flat p-4">
                            <div class="flex items-center justify-between gap-3">
                                <p class="font-bold text-slate-950">{{ $choice['title'] ?? '-' }}</p>
                                <span class="text-sm font-black text-blue-700">{{ $choice['fit_percent'] ?? 0 }}%</span>
                            </div>
                            <p class="mt-2 text-sm leading-6 text-slate-600">Demande Maroc : {{ $choice['morocco_demand_percent'] ?? 0 }}%</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="mt-3 text-sm leading-7 text-slate-600">Les parcours detailles seront disponibles apres completion des deux tests.</p>
            @endif
        </section>
    </div>

    @once
        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
        @endpush
    @endonce

    @push('scripts')
        <script>
            (function () {
                const charts = window.orientationTechCharts || (window.orientationTechCharts = {});

                function buildChart(canvasId, config) {
                    const canvas = document.getElementById(canvasId);
                    if (!canvas || typeof Chart === 'undefined') {
                        return;
                    }

                    if (charts[canvasId]) {
                        charts[canvasId].destroy();
                    }

                    charts[canvasId] = new Chart(canvas, config);
                }

                buildChart('axis-chart-{{ $chartIdSuffix }}', {
                    type: 'radar',
                    data: {
                        labels: @json($axisLabels),
                        datasets: [{
                            label: 'Profil',
                            data: @json($axisValues),
                            backgroundColor: 'rgba(37, 99, 235, 0.16)',
                            borderColor: '#2563eb',
                            pointBackgroundColor: '#1d4ed8',
                            borderWidth: 2,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            r: {
                                beginAtZero: true,
                                max: 100,
                                ticks: { display: false },
                                grid: { color: 'rgba(148, 163, 184, 0.25)' },
                                angleLines: { color: 'rgba(148, 163, 184, 0.25)' },
                                pointLabels: { color: '#334155', font: { size: 11, weight: '600' } },
                            },
                        },
                        plugins: {
                            legend: { display: false },
                        },
                    },
                });

                buildChart('it-chart-{{ $chartIdSuffix }}', {
                    type: 'bar',
                    data: {
                        labels: @json($itLabels),
                        datasets: [{
                            label: 'Compatibilite',
                            data: @json($itValues),
                            backgroundColor: '#2563eb',
                            borderRadius: 10,
                            borderSkipped: false,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                ticks: {
                                    callback: value => value + '%',
                                },
                                grid: { color: 'rgba(148, 163, 184, 0.18)' },
                            },
                            x: {
                                grid: { display: false },
                                ticks: { color: '#334155', font: { size: 11, weight: '600' } },
                            },
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: context => context.parsed.y + '%',
                                },
                            },
                        },
                    },
                });
            })();
        </script>
    @endpush
</x-filament-panels::page>
