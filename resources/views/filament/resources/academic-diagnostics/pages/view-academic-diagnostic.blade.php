<x-filament-panels::page>
    @php
        $record = $this->getRecord();
        $metadata = $this->getMetadata();
        $domainSeries = $this->getDomainSeries();
        $stats = $this->getRecommendationStats();
        $summary = $this->getSummary();
        $chartIdSuffix = 'academic-diagnostic-' . $record->getKey();

        $domainLabels = collect($domainSeries)->pluck('label')->all();
        $domainValues = collect($domainSeries)->pluck('score')->all();
        $payload = $record->result_payload ?? [];
        $skills = collect($payload['skills_match'] ?? [])->take(8);
        $schools = collect($payload['recommended_schools'] ?? [])->flatten()->take(8);
    @endphp

    <div class="ad-page mx-auto max-w-7xl space-y-6 px-4 py-4 sm:px-6 lg:px-8">
        <style>
            .ad-page { color: #0f172a; }
            .ad-hero {
                border: 1px solid rgba(37, 99, 235, 0.14);
                background: linear-gradient(135deg, #ffffff 0%, #eff6ff 56%, #dbeafe 100%);
                border-radius: 22px;
                box-shadow: 0 18px 48px rgba(15, 23, 42, 0.08);
            }
            .ad-card {
                border: 1px solid rgba(148, 163, 184, 0.18);
                background: rgba(255, 255, 255, 0.96);
                border-radius: 16px;
                box-shadow: 0 10px 28px rgba(15, 23, 42, 0.04);
            }
            .ad-flat {
                border: 1px solid rgba(148, 163, 184, 0.18);
                background: #f8fafc;
                border-radius: 14px;
            }
            .ad-label {
                font-size: 0.72rem;
                font-weight: 800;
                letter-spacing: 0.12em;
                text-transform: uppercase;
                color: #64748b;
            }
            .ad-chip {
                display: inline-flex;
                align-items: center;
                border-radius: 999px;
                border: 1px solid rgba(37, 99, 235, 0.16);
                background: #eff6ff;
                padding: 0.45rem 0.75rem;
                font-size: 0.76rem;
                font-weight: 800;
                color: #1d4ed8;
                white-space: nowrap;
            }
            .ad-chart-box {
                height: 290px;
                min-height: 290px;
                max-height: 290px;
                position: relative;
            }
            .ad-chart-box canvas {
                display: block;
                width: 100% !important;
                height: 100% !important;
            }
            .ad-action {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 999px;
                padding: 0.72rem 1rem;
                font-size: 0.875rem;
                font-weight: 800;
                background: #2563eb;
                color: #fff;
                box-shadow: 0 12px 28px rgba(37, 99, 235, 0.22);
            }
            .ad-action:hover { background: #1d4ed8; }
        </style>

        <section class="ad-hero p-5 sm:p-7 lg:p-8">
            <div class="grid gap-6 lg:grid-cols-[1.3fr_0.7fr] lg:items-center">
                <div class="space-y-4">
                    <span class="ad-chip">Etape 1 - Diagnostic academique</span>
                    <div>
                        <h1 class="max-w-3xl text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">
                            {{ $metadata['result_label'] }}
                        </h1>
                        <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600 sm:text-base">
                            Cette lecture resume ton contexte scolaire, tes centres d'interet, ton Ikigai simple et les premieres pistes d'orientation numerique.
                        </p>
                    </div>
                    <a href="{{ \App\Filament\Resources\TestPersonnalises\TestPersonnaliseResource::getUrl('index') }}" class="ad-action">
                        Continuer vers le test personnalise
                    </a>
                </div>

                <div class="ad-card p-4">
                    <p class="ad-label">Statut du dossier</p>
                    <div class="mt-4 grid gap-3">
                        <div class="ad-flat p-4">
                            <p class="ad-label">Cycle</p>
                            <p class="mt-2 font-black text-slate-950">{{ $metadata['macro_cycle'] }}</p>
                        </div>
                        <div class="ad-flat p-4">
                            <p class="ad-label">Niveau</p>
                            <p class="mt-2 font-black text-slate-950">{{ $metadata['academic_level'] }}</p>
                        </div>
                        <div class="ad-flat p-4">
                            <p class="ad-label">Soumis le</p>
                            <p class="mt-2 font-black text-slate-950">{{ optional($metadata['submitted_at'])->format('d/m/Y H:i') ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="ad-card p-4">
                <p class="ad-label">Centre d'interet</p>
                <p class="mt-2 text-lg font-black text-slate-950">{{ $metadata['interest_theme'] }}</p>
            </div>
            <div class="ad-card p-4">
                <p class="ad-label">Branche</p>
                <p class="mt-2 text-lg font-black text-slate-950">{{ $metadata['track_branch'] }}</p>
            </div>
            <div class="ad-card p-4">
                <p class="ad-label">Specialite</p>
                <p class="mt-2 text-lg font-black text-slate-950">{{ $metadata['specialty_label'] }}</p>
            </div>
            <div class="ad-card p-4">
                <p class="ad-label">Langue</p>
                <p class="mt-2 text-lg font-black text-slate-950">{{ $metadata['biof_language'] }}</p>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
            <div class="ad-card p-5 sm:p-6">
                <p class="ad-label">Interpretation pedagogique</p>
                <h2 class="mt-2 text-xl font-black text-slate-950">Premiere comprehension du profil</h2>
                <p class="mt-3 text-sm leading-7 text-slate-700">{{ $summary }}</p>
            </div>

            <div class="ad-card p-5 sm:p-6">
                <p class="ad-label">Indicateurs</p>
                <h2 class="mt-2 text-xl font-black text-slate-950">Donnees exploitees par l'analyse</h2>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div class="ad-flat p-4">
                        <p class="ad-label">Domaines</p>
                        <p class="mt-2 text-3xl font-black text-slate-950">{{ $stats['domains_count'] }}</p>
                    </div>
                    <div class="ad-flat p-4">
                        <p class="ad-label">Groupes d'ecoles</p>
                        <p class="mt-2 text-3xl font-black text-slate-950">{{ $stats['school_groups_count'] }}</p>
                    </div>
                    <div class="ad-flat p-4">
                        <p class="ad-label">Ecoles</p>
                        <p class="mt-2 text-3xl font-black text-slate-950">{{ $stats['schools_count'] }}</p>
                    </div>
                    <div class="ad-flat p-4">
                        <p class="ad-label">Competences</p>
                        <p class="mt-2 text-3xl font-black text-slate-950">{{ $stats['skills_count'] }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <div class="ad-card p-5 sm:p-6">
                <p class="ad-label">Domaines suggeres</p>
                <h2 class="mt-2 text-xl font-black text-slate-950">Premieres pistes numeriques</h2>
                <div class="ad-chart-box mt-4">
                    <canvas id="domain-chart-{{ $chartIdSuffix }}"></canvas>
                </div>
            </div>

            <div class="ad-card p-5 sm:p-6">
                <p class="ad-label">Elements utiles</p>
                <h2 class="mt-2 text-xl font-black text-slate-950">Competences et formations possibles</h2>
                <div class="mt-4 space-y-4">
                    <div>
                        <p class="font-black text-slate-950">Competences detectees</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @forelse($skills as $skill)
                                <span class="ad-chip">{{ $skill }}</span>
                            @empty
                                <span class="text-sm text-slate-500">Aucune competence detaillee disponible.</span>
                            @endforelse
                        </div>
                    </div>
                    <div>
                        <p class="font-black text-slate-950">Ecoles et formations</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @forelse($schools as $school)
                                <span class="ad-chip">{{ $school }}</span>
                            @empty
                                <span class="text-sm text-slate-500">Les formations seront precisees dans le rapport complet.</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
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
                const canvasId = 'domain-chart-{{ $chartIdSuffix }}';
                const canvas = document.getElementById(canvasId);
                const charts = window.orientationTechCharts || (window.orientationTechCharts = {});

                if (!canvas || typeof Chart === 'undefined') {
                    return;
                }

                if (charts[canvasId]) {
                    charts[canvasId].destroy();
                }

                charts[canvasId] = new Chart(canvas, {
                    type: 'doughnut',
                    data: {
                        labels: @json($domainLabels),
                        datasets: [{
                            data: @json($domainValues),
                            backgroundColor: ['#2563eb', '#3b82f6', '#60a5fa', '#1d4ed8', '#1e40af', '#93c5fd', '#172554'],
                            borderColor: '#ffffff',
                            borderWidth: 2,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '66%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                    color: '#334155',
                                    font: { size: 11, weight: '600' },
                                },
                            },
                        },
                    },
                });
            })();
        </script>
    @endpush
</x-filament-panels::page>
