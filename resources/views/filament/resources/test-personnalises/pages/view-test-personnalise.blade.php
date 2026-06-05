<x-filament-panels::page>
    @php
        $record = $this->getRecord();
        $axisSeries = $this->axisScoreSeries();
        $domainSeries = $this->domainScoreSeries();
        $metadata = $this->getMetadata();
        $chartIdSuffix = 'personality-test-' . $record->getKey();

        $axisLabels = collect($axisSeries)->pluck('label')->all();
        $axisValues = collect($axisSeries)->pluck('score')->all();
        $domainLabels = collect($domainSeries)->pluck('label')->all();
        $domainValues = collect($domainSeries)->pluck('score')->all();
    @endphp

    <div class="tp-page mx-auto max-w-7xl space-y-6 px-4 py-4 sm:px-6 lg:px-8">
        <style>
            .tp-page { color: #0f172a; }
            .tp-hero {
                border: 1px solid rgba(37, 99, 235, 0.14);
                background: linear-gradient(135deg, #ffffff 0%, #eff6ff 56%, #dbeafe 100%);
                border-radius: 22px;
                box-shadow: 0 18px 48px rgba(15, 23, 42, 0.08);
            }
            .tp-card {
                border: 1px solid rgba(148, 163, 184, 0.18);
                background: rgba(255, 255, 255, 0.96);
                border-radius: 16px;
                box-shadow: 0 10px 28px rgba(15, 23, 42, 0.04);
            }
            .tp-flat {
                border: 1px solid rgba(148, 163, 184, 0.18);
                background: #f8fafc;
                border-radius: 14px;
            }
            .tp-label {
                font-size: 0.72rem;
                font-weight: 800;
                letter-spacing: 0.12em;
                text-transform: uppercase;
                color: #64748b;
            }
            .tp-chip {
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
            .tp-chart-box {
                height: 290px;
                min-height: 290px;
                max-height: 290px;
                position: relative;
            }
            .tp-chart-box canvas {
                display: block;
                width: 100% !important;
                height: 100% !important;
            }
            .tp-action {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 999px;
                padding: 0.72rem 1rem;
                font-size: 0.875rem;
                font-weight: 800;
            }
            .tp-action-primary {
                background: #2563eb;
                color: #fff;
                box-shadow: 0 12px 28px rgba(37, 99, 235, 0.22);
            }
            .tp-action-primary:hover { background: #1d4ed8; }
            .tp-action-soft {
                border: 1px solid rgba(37, 99, 235, 0.18);
                background: #fff;
                color: #1d4ed8;
            }
            .tp-action-soft:hover { background: #eff6ff; }
        </style>

        <section class="tp-hero p-5 sm:p-7 lg:p-8">
            <div class="grid gap-6 lg:grid-cols-[1.3fr_0.7fr] lg:items-center">
                <div class="space-y-4">
                    <span class="tp-chip">Etape 2 - Test personnalise</span>
                    <div>
                        <h1 class="max-w-3xl text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">
                            {{ $metadata['primary_domain'] }}
                        </h1>
                        <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600 sm:text-base">
                            Cette page presente tes traits dominants, tes axes psychometriques et les domaines numeriques qui correspondent le mieux a ton profil.
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ \App\Filament\Pages\MesResultatsDePersonnalites::getUrl() }}" class="tp-action tp-action-primary">
                            Voir mes resultats fusionnes
                        </a>
                        <a href="{{ \App\Filament\Resources\TestPersonnalises\TestPersonnaliseResource::getUrl('edit', ['record' => $record]) }}" class="tp-action tp-action-soft">
                            Ajuster mes reponses
                        </a>
                    </div>
                </div>

                <div class="tp-card p-4">
                    <p class="tp-label">Synthese du test</p>
                    <div class="mt-4 grid gap-3">
                        <div class="tp-flat p-4">
                            <p class="tp-label">Domaine principal</p>
                            <p class="mt-2 font-black text-slate-950">{{ $metadata['primary_domain'] }}</p>
                        </div>
                        <div class="tp-flat p-4">
                            <p class="tp-label">Domaine secondaire</p>
                            <p class="mt-2 font-black text-slate-950">{{ $metadata['secondary_domain'] }}</p>
                        </div>
                        <div class="tp-flat p-4">
                            <p class="tp-label">Soumis le</p>
                            <p class="mt-2 font-black text-slate-950">{{ optional($metadata['submitted_at'])->format('d/m/Y H:i') ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="tp-card p-4">
                <p class="tp-label">Nom du test</p>
                <p class="mt-2 text-lg font-black text-slate-950">{{ $metadata['test_name'] }}</p>
            </div>
            <div class="tp-card p-4">
                <p class="tp-label">Niveau cible</p>
                <p class="mt-2 text-lg font-black text-slate-950">{{ $metadata['target_level'] }}</p>
            </div>
            <div class="tp-card p-4">
                <p class="tp-label">Statut</p>
                <p class="mt-2 text-lg font-black text-slate-950">{{ $metadata['status'] }}</p>
            </div>
            <div class="tp-card p-4">
                <p class="tp-label">Annee</p>
                <p class="mt-2 text-lg font-black text-slate-950">{{ $metadata['annee'] }}</p>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
            <div class="tp-card p-5 sm:p-6">
                <p class="tp-label">Interpretation</p>
                <h2 class="mt-2 text-xl font-black text-slate-950">Lecture du profil psychometrique</h2>
                <p class="mt-3 text-sm leading-7 text-slate-700">{{ $this->getResultSummary() }}</p>
            </div>

            <div class="tp-card p-5 sm:p-6">
                <p class="tp-label">Scores dominants</p>
                <h2 class="mt-2 text-xl font-black text-slate-950">Axes les plus marques</h2>
                <div class="mt-4 flex flex-wrap gap-2">
                    @forelse($axisSeries as $axis)
                        <span class="tp-chip">{{ $axis['label'] }} : {{ $axis['score'] }}%</span>
                    @empty
                        <span class="text-sm text-slate-500">Aucun score disponible.</span>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <div class="tp-card p-5 sm:p-6">
                <p class="tp-label">Personnalite</p>
                <h2 class="mt-2 text-xl font-black text-slate-950">Radar des axes psychometriques</h2>
                <div class="tp-chart-box mt-4">
                    <canvas id="axis-chart-{{ $chartIdSuffix }}"></canvas>
                </div>
            </div>

            <div class="tp-card p-5 sm:p-6">
                <p class="tp-label">Domaines IT</p>
                <h2 class="mt-2 text-xl font-black text-slate-950">Compatibilite avec les domaines</h2>
                <div class="tp-chart-box mt-4">
                    <canvas id="domain-chart-{{ $chartIdSuffix }}"></canvas>
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
                        plugins: { legend: { display: false } },
                    },
                });

                buildChart('domain-chart-{{ $chartIdSuffix }}', {
                    type: 'bar',
                    data: {
                        labels: @json($domainLabels),
                        datasets: [{
                            label: 'Score',
                            data: @json($domainValues),
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
                                ticks: { callback: value => value + '%' },
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
