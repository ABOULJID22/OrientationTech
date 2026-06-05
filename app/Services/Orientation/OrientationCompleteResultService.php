<?php

namespace App\Services\Orientation;

use App\Models\AcademicDiagnostic;
use App\Models\TestPersonnalise;
use App\Services\Diagnostics\AcademicDiagnosticOptions;
use App\Services\TestPersonnalises\TestPersonnaliseQuestionnaire;
use App\Services\TestPersonnalises\TestPersonnaliseResultService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

class OrientationCompleteResultService
{
    public function buildCached(AcademicDiagnostic $diagnostic, TestPersonnalise $personnalise): array
    {
        $cacheKey = $this->cacheKey($diagnostic, $personnalise);

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($diagnostic, $personnalise) {
            return $this->build($diagnostic, $personnalise);
        });
    }

    public function forgetCached(AcademicDiagnostic $diagnostic, TestPersonnalise $personnalise): void
    {
        Cache::forget($this->cacheKey($diagnostic, $personnalise));
    }

    public function build(AcademicDiagnostic $diagnostic, TestPersonnalise $personnalise): array
    {
        $catalog = $this->loadDomainCatalog();
        $personalityDomainScores = $personnalise->domain_scores ?? [];
        $mergedDomainScores = $this->mergeDiagnosticDomainScores($personalityDomainScores, $diagnostic);
        $topTracks = $this->resolveTopTracks($catalog, $mergedDomainScores);

        $base = [
            'diagnostic' => [
                'academic_level' => $diagnostic->academic_level,
                'macro_cycle' => $diagnostic->macro_cycle,
                'label' => $diagnostic->result_label,
                'summary' => $diagnostic->result_summary,
                'interpretation' => $diagnostic->result_summary,
                'interest_theme' => $diagnostic->interest_theme,
                'interest_theme_label' => $this->interestThemeLabel($diagnostic->interest_theme),
                'specialty_family' => $diagnostic->specialty_family,
                'specialty_family_label' => $this->specialtyFamilyLabel($diagnostic->specialty_family),
                'specialty_label' => $diagnostic->specialty_label,
                'answers' => $this->extractDiagnosticAnswers($diagnostic),
                'domains' => $diagnostic->result_payload['orientation_domains'] ?? [],
            ],
            'personnalise' => [
                'axis_scores' => $personnalise->axis_scores ?? [],
                'domain_scores' => $personalityDomainScores,
                'merged_domain_scores' => $mergedDomainScores,
                'primary_domain' => $personnalise->primary_domain,
                'secondary_domain' => $personnalise->secondary_domain,
            ],
            'top_tracks' => $topTracks,
        ];

        [$narrative, $narrativeSource, $aiMessage, $aiRecommendations] = $this->generateNarrative($base);

        $base['narrative'] = $narrative;
        $base['narrative_source'] = $narrativeSource;
        $base['ai_recommendations'] = $aiRecommendations;
        $base['ai_status'] = [
            'enabled' => $narrativeSource === 'ai',
            'message' => $aiMessage,
        ];

        return $base;
    }

    private function cacheKey(AcademicDiagnostic $diagnostic, TestPersonnalise $personnalise): string
    {
        return 'orientation_complete_result:' . $diagnostic->id . ':' . $diagnostic->updated_at?->timestamp . ':' . $personnalise->id . ':' . $personnalise->updated_at?->timestamp;
    }

    private function loadDomainCatalog(): array
    {
        $path = base_path('resources/domainesIT/domaines_informatique.json');

        if (! File::exists($path)) {
            return ['categories' => []];
        }

        return json_decode(File::get($path), true) ?: ['categories' => []];
    }

    private function resolveTopTracks(array $catalog, array $domainScores): array
    {
        arsort($domainScores);

        $topKeys = array_slice(array_keys($domainScores), 0, 3);
        $matchedCategories = [];

        foreach ($topKeys as $key) {
            $category = $this->findCategoryByScoreKey($catalog['categories'] ?? [], $key);

            if (! $category) {
                continue;
            }

            $matchedCategories[] = [
                'score_key' => $key,
                'category' => $category['nom'] ?? 'Catégorie IT',
                'top_domaines' => array_slice($category['domaines'] ?? [], 0, 3),
            ];
        }

        return $matchedCategories;
    }

    private function findCategoryByScoreKey(array $categories, string $scoreKey): ?array
    {
        $map = [
            'development_web' => ['Développement & Web', 'Langages & Paradigmes'],
            'data_ia' => ['Data & Big Data', 'Intelligence Artificielle'],
            'intelligence_artificielle' => ['Intelligence Artificielle', 'Data & Big Data'],
            'cybersecurite' => ['Cybersécurité'],
            'cloud_infrastructure' => ['Cloud Computing', 'Systèmes & Administration'],
            'ui_ux' => ['UX/UI & Design'],
            'qa_test' => ['QA & Testing'],
            'mobile' => ['Développement & Web'],
            'systemes_reseaux' => ['Réseaux & Télécoms', 'Systèmes & Administration'],
            'bases_donnees' => ['Bases de Données'],
            'langages_paradigmes' => ['Langages & Paradigmes'],
            'management_it' => ['Management IT'],
            'erp_si' => ['ERP & SI'],
            'ecommerce_marketing' => ['E-Commerce & Marketing Digital'],
            'fintech_numerique' => ['FinTech & Numérique'],
            'edtech' => ['EdTech'],
            'informatique_scientifique' => ['Informatique Scientifique'],
            'sante_numerique' => ['Domaines Spéciaux', 'Informatique Scientifique'],
            'agritech_green' => ['Domaines Spéciaux', 'Robotique & Industrie 4.0'],
            'robotique_industrie4' => ['Robotique & Industrie 4.0'],
            'technologies_emergentes' => ['Technologies Émergentes'],
            'transformation_digitale' => ['Transformation Digitale'],
            'creation_contenu' => ['E-Commerce & Marketing Digital', 'UX/UI & Design'],
            'geomatique_smart_city' => ['Domaines Spéciaux', 'Data & Big Data'],
        ];

        $targets = $map[$scoreKey] ?? [];

        foreach ($targets as $target) {
            foreach ($categories as $category) {
                if (($category['nom'] ?? null) === $target) {
                    return $category;
                }
            }
        }

        return null;
    }

    private function mergeDiagnosticDomainScores(array $domainScores, AcademicDiagnostic $diagnostic): array
    {
        $scores = $domainScores;
        $answers = $diagnostic->diagnostic_answers ?? [];

        $boost = function (array $domains, float $points) use (&$scores): void {
            foreach ($domains as $domain) {
                $scores[$domain] = min(100, (float) ($scores[$domain] ?? 0) + $points);
            }
        };

        match ($diagnostic->interest_theme) {
            'ai' => $boost(['data_ia', 'intelligence_artificielle', 'bases_donnees', 'technologies_emergentes'], 10),
            'engineering' => $boost(['robotique_industrie4', 'cloud_infrastructure', 'systemes_reseaux', 'development_web'], 8),
            'medicine' => $boost(['sante_numerique', 'informatique_scientifique', 'data_ia'], 8),
            'business' => $boost(['fintech_numerique', 'ecommerce_marketing', 'erp_si', 'management_it'], 8),
            'education' => $boost(['edtech', 'management_it', 'transformation_digitale'], 8),
            'arts' => $boost(['ui_ux', 'creation_contenu', 'ecommerce_marketing'], 8),
            'agriculture' => $boost(['agritech_green', 'geomatique_smart_city', 'data_ia'], 8),
            default => null,
        };

        $specialtyText = mb_strtolower(trim(($diagnostic->specialty_family ?? '') . ' ' . ($diagnostic->specialty_label ?? '')));
        $freeText = mb_strtolower(implode(' ', array_filter([
            $answers['preferred_subjects'] ?? '',
            $answers['interests'] ?? '',
            $answers['favorite_activities'] ?? '',
            $answers['future_ambitions'] ?? '',
            $answers['career_goals'] ?? '',
            $answers['motivations'] ?? '',
            $answers['ikigai_love'] ?? '',
            $answers['ikigai_good_at'] ?? '',
            $answers['ikigai_profession'] ?? '',
        ])));

        if (str_contains($specialtyText . ' ' . $freeText, 'data') || str_contains($freeText, 'donnée') || str_contains($freeText, 'ia')) {
            $boost(['data_ia', 'intelligence_artificielle', 'bases_donnees'], 7);
        }

        if (str_contains($freeText, 'cyber') || str_contains($freeText, 'sécurité') || str_contains($freeText, 'securite') || str_contains($freeText, 'réseau') || str_contains($freeText, 'reseau')) {
            $boost(['cybersecurite', 'systemes_reseaux', 'cloud_infrastructure'], 7);
        }

        if (str_contains($freeText, 'design') || str_contains($freeText, 'création') || str_contains($freeText, 'creation') || str_contains($freeText, 'interface')) {
            $boost(['ui_ux', 'creation_contenu', 'ecommerce_marketing'], 7);
        }

        if (str_contains($freeText, 'application') || str_contains($freeText, 'programmation') || str_contains($freeText, 'web') || str_contains($freeText, 'logiciel')) {
            $boost(['development_web', 'mobile', 'langages_paradigmes'], 7);
        }

        $answerBoosts = [
            'programming_interest' => ['development_web', 'mobile', 'langages_paradigmes'],
            'data_ai_interest' => ['data_ia', 'intelligence_artificielle', 'bases_donnees'],
            'cyber_network_interest' => ['cybersecurite', 'systemes_reseaux', 'cloud_infrastructure'],
            'design_product_interest' => ['ui_ux', 'creation_contenu', 'ecommerce_marketing'],
            'future_market_interest' => ['technologies_emergentes', 'transformation_digitale', 'data_ia'],
            'service_motivation' => ['edtech', 'sante_numerique', 'agritech_green', 'transformation_digitale'],
        ];

        foreach ($answerBoosts as $answerKey => $domains) {
            $score = (int) ($answers[$answerKey] ?? 0);
            if ($score >= 4) {
                $boost($domains, $score * 1.8);
            }
        }

        arsort($scores);

        return $scores;
    }

    private function extractDiagnosticAnswers(AcademicDiagnostic $diagnostic): array
    {
        $answers = $diagnostic->diagnostic_answers ?? [];

        return [
            'matieres_preferees' => $answers['preferred_subjects'] ?? '',
            'centres_interet' => $answers['interests'] ?? '',
            'activites_favorites' => $answers['favorite_activities'] ?? '',
            'ambitions_futures' => $answers['future_ambitions'] ?? '',
            'objectifs_professionnels' => $answers['career_goals'] ?? '',
            'motivations' => $answers['motivations'] ?? '',
            'environnement' => $answers['family_school_environment'] ?? '',
            'ikigai_aime' => $answers['ikigai_love'] ?? '',
            'ikigai_excelle' => $answers['ikigai_good_at'] ?? '',
            'ikigai_profession' => $answers['ikigai_profession'] ?? '',
            'interet_programmation' => (int) ($answers['programming_interest'] ?? 0),
            'interet_data_ia' => (int) ($answers['data_ai_interest'] ?? 0),
            'interet_cyber_reseaux' => (int) ($answers['cyber_network_interest'] ?? 0),
            'interet_design_produit' => (int) ($answers['design_product_interest'] ?? 0),
        ];
    }

    private function interestThemeLabel(?string $theme): ?string
    {
        return $theme ? (AcademicDiagnosticOptions::interestThemes()[$theme] ?? $theme) : null;
    }

    private function specialtyFamilyLabel(?string $family): ?string
    {
        return $family ? (AcademicDiagnosticOptions::specialtyFamilies()[$family] ?? $family) : null;
    }

    private function generateNarrative(array $base): array
    {
        $prompt = $this->buildPromptV2($base);

        [$aiRecommendations, $aiMessage] = $this->tryGenerateNarrativeWithAi($prompt);
        $aiNarrative = is_array($aiRecommendations) ? ($aiRecommendations['summary'] ?? null) : null;

        if ($aiNarrative !== null && trim($aiNarrative) !== '') {
            return [$aiNarrative, 'ai', $aiMessage, $aiRecommendations];
        }

        $fallbackRecommendations = $this->buildFallbackRecommendations($base);
        $fallbackSummary = $fallbackRecommendations['summary'] ?? 'Votre profil présente des pistes numériques cohérentes avec vos résultats.';

        return [$fallbackSummary, 'fallback', $aiMessage, $fallbackRecommendations];
    }

    private function buildPrompt(array $base): string
    {
        return "Tu es un conseiller d'orientation spécialisé au Maroc pour les parcours IT et numériques. "
            . "Analyse la fusion de deux tests d'orientation et réponds STRICTEMENT en JSON valide sans texte hors JSON. "
            . "Le JSON doit respecter exactement cette structure: "
            . '{"summary":"...","recommended_schools":[{"name":"...","city":"...","why_fit":"...","admission_path":"..."}],'
            . '"program_choices":[{"title":"...","fit_percent":0,"morocco_demand_percent":0,"importance":"élevée|moyenne|faible","notes":"..."}],'
            . '"job_roles":[{"title":"...","fit_percent":0,"morocco_demand_percent":0,"importance":"élevée|moyenne|faible","skills":["..."],"salary_range_mad_month":"..."}],'
            . '"market_insights":["..."],"next_actions":["..."]}'
            . "Contraintes: fit_percent et morocco_demand_percent sont des entiers 0..100, "
            . "minimum 4 écoles au Maroc, minimum 4 choix de filières, minimum 4 métiers. "
            . "Contexte JSON: " . json_encode($base, JSON_UNESCAPED_UNICODE);
    }

    private function buildPromptV2(array $base): string
    {
        $schema = [
            'summary' => 'Synthese professionnelle basee sur diagnostic + personnalite.',
            'intelligent_analysis' => [
                'strengths' => ['Force detectee avec preuve des scores ou reponses.'],
                'improvements' => ['Point d amelioration formule de maniere bienveillante.'],
                'compatibility_explanation' => 'Explication pedagogique de la compatibilite entre profil, domaines et metiers.',
                'student_explanation' => 'Explication simple pour l eleve.',
                'parents_explanation' => 'Explication claire pour les parents.',
                'teachers_explanation' => 'Lecture utile pour enseignants ou conseiller d orientation.',
            ],
            'recommended_domains' => [[
                'domain_key' => 'cle_score_si_disponible',
                'label' => 'Nom lisible du domaine',
                'compatibility' => 0,
                'why' => 'Justification personnalisee avec preuves.',
                'ai_impact' => 'Impact concret de l IA dans ce domaine.',
            ]],
            'recommended_schools' => [[
                'name' => 'Etablissement marocain',
                'city' => 'Ville',
                'why_fit' => 'Pourquoi ce parcours correspond.',
                'admission_path' => 'Voie d acces prudente.',
            ]],
            'program_choices' => [[
                'title' => 'Filiere ou parcours',
                'fit_percent' => 0,
                'morocco_demand_percent' => 0,
                'importance' => 'elevee|moyenne|faible',
                'notes' => 'Justification.',
            ]],
            'job_roles' => [[
                'title' => 'Metier recommande',
                'domain' => 'Domaine associe',
                'fit_percent' => 0,
                'morocco_demand_percent' => 0,
                'importance' => 'elevee|moyenne|faible',
                'missions' => ['Mission principale.'],
                'skills' => ['Competence requise.'],
                'outlook' => 'Perspectives emploi au Maroc.',
                'ai_impact' => 'Impact de l IA sur ce metier.',
                'salary_range_mad_month' => 'Fourchette prudente si pertinente.',
            ]],
            'market_insights' => ['Observation courte sur le marche marocain.'],
            'next_actions' => ['Action concrete et realiste.'],
        ];

        return "Tu es un conseiller d'orientation expert au Maroc pour les parcours IT et numeriques. "
            . "Analyse la fusion du test diagnostique, de l'Ikigai et du test de personnalite. "
            . "Reponds STRICTEMENT en JSON valide sans texte hors JSON. "
            . "Respecte cette structure et remplace chaque valeur par une analyse personnalisee: "
            . json_encode($schema, JSON_UNESCAPED_UNICODE)
            . " Contraintes: scores entiers 0..100, minimum 4 domaines recommandes, minimum 4 metiers, minimum 4 ecoles ou parcours marocains. "
            . "Chaque domaine et metier doit etre justifie par les scores, forces, axes faibles ou reponses disponibles. "
            . "Tu dois obligatoirement tenir compte de l'interpretation diagnostique, du centre d'interet, de la specialite et des reponses diagnostic_answers avant de classer les domaines. "
            . "Le texte doit etre professionnel, bienveillant, transparent, adapte au contexte marocain, et comprehensible pour l'eleve, les parents et les enseignants. "
            . "Ne recommande pas toujours development_web: classe les domaines selon les donnees reelles. "
            . "Contexte JSON: " . json_encode($base, JSON_UNESCAPED_UNICODE);
    }

    private function tryGenerateNarrativeWithAi(string $prompt): array
    {
        try {
            $apiKey = config('services.gemini.api_key');

            if (! is_string($apiKey) || trim($apiKey) === '') {
                return [null, 'Clé Gemini absente dans la configuration.'];
            }

            $primaryModel = $this->normalizeModelName((string) config('services.gemini.model', 'gemini-1.5-flash'));
            $fallbackModels = config('services.gemini.fallback_models', ['gemini-1.5-flash']);
            $retryableStatuses = [429, 500, 502, 503, 504];
            $maxAttempts = max(1, (int) config('services.gemini.max_attempts', 3));
            $retryDelayMs = max(0, (int) config('services.gemini.retry_delay_ms', 700));

            $modelsToTry = [$primaryModel];

            if (is_array($fallbackModels)) {
                foreach ($fallbackModels as $fallbackModel) {
                    $normalized = $this->normalizeModelName((string) $fallbackModel);

                    if ($normalized !== '' && ! in_array($normalized, $modelsToTry, true)) {
                        $modelsToTry[] = $normalized;
                    }
                }
            }

            $lastErrorMessage = 'Aucune réponse exploitable retournée par Gemini.';

            foreach ($modelsToTry as $model) {
                $endpoint = sprintf(
                    'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent',
                    $model
                );

                for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
                    $response = Http::timeout(30)
                        ->acceptJson()
                        ->asJson()
                        ->withQueryParameters(['key' => $apiKey])
                        ->post($endpoint, [
                            'contents' => [[
                                'role' => 'user',
                                'parts' => [[
                                    'text' => $prompt,
                                ]],
                            ]],
                            'generationConfig' => [
                                'temperature' => 0.45,
                                'topP' => 0.85,
                                'maxOutputTokens' => 1800,
                            ],
                        ]);

                    if ($response->successful()) {
                        $text = data_get($response->json(), 'candidates.0.content.parts.0.text');

                        if (is_string($text) && trim($text) !== '') {
                            $parsed = $this->extractJsonPayload($text);

                            if (is_array($parsed)) {
                                return [$parsed, 'Analyse détaillée générée via Gemini (' . $model . ').'];
                            }

                            return [[
                                'summary' => trim($text),
                                'recommended_schools' => [],
                                'program_choices' => [],
                                'job_roles' => [],
                                'market_insights' => [],
                                'next_actions' => [],
                            ], 'Gemini a répondu en texte libre (' . $model . ').'];
                        }

                        $finishReason = data_get($response->json(), 'candidates.0.finishReason');

                        if (is_string($finishReason) && trim($finishReason) !== '') {
                            $lastErrorMessage = 'Gemini a répondu sans contenu exploitable (' . $finishReason . ') avec le modèle ' . $model . '.';
                        }

                        break;
                    }

                    $status = $response->status();
                    $errorMessage = data_get($response->json(), 'error.message');
                    $apiMessage = is_string($errorMessage) && trim($errorMessage) !== ''
                        ? ' Détail: ' . trim($errorMessage)
                        : '';

                    $lastErrorMessage = 'Gemini a répondu avec un statut HTTP ' . $status
                        . ' (modèle: ' . $model . ', tentative ' . $attempt . '/' . $maxAttempts . ').'
                        . $apiMessage;

                    $isRetryable = in_array($status, $retryableStatuses, true);

                    if (! $isRetryable || $attempt >= $maxAttempts) {
                        break;
                    }

                    if ($retryDelayMs > 0) {
                        usleep($retryDelayMs * 1000);
                    }
                }
            }

            return [null, $lastErrorMessage];
        } catch (\Throwable $exception) {
            return [null, 'Echec de generation Gemini: ' . $exception->getMessage()];
        }

        return [null, 'Aucune réponse exploitable retournée par Gemini.'];
    }

    private function extractJsonPayload(string $text): ?array
    {
        $cleaned = trim($text);

        if (preg_match('/```(?:json)?\s*(\{.*\})\s*```/is', $cleaned, $matches)) {
            $cleaned = trim($matches[1]);
        }

        $decoded = json_decode($cleaned, true);

        if (is_array($decoded)) {
            return $decoded;
        }

        $firstBrace = strpos($cleaned, '{');
        $lastBrace = strrpos($cleaned, '}');

        if ($firstBrace === false || $lastBrace === false || $lastBrace <= $firstBrace) {
            return null;
        }

        $jsonSlice = substr($cleaned, $firstBrace, $lastBrace - $firstBrace + 1);
        $decodedSlice = json_decode($jsonSlice, true);

        return is_array($decodedSlice) ? $decodedSlice : null;
    }

    private function buildFallbackRecommendations(array $base): array
    {
        $diagnostic = $base['diagnostic']['label'] ?? 'Orientation générale';
        $interestTheme = $base['diagnostic']['interest_theme_label'] ?? $base['diagnostic']['interest_theme'] ?? 'centre d interet non precise';
        $specialty = $base['diagnostic']['specialty_label']
            ?? $base['diagnostic']['specialty_family_label']
            ?? $base['diagnostic']['specialty_family']
            ?? 'specialite non precise';
        $primary = $base['personnalise']['primary_domain'] ?? 'profil IT global';
        $secondary = $base['personnalise']['secondary_domain'] ?? 'piste complémentaire';

        $domainScores = $base['personnalise']['merged_domain_scores'] ?? $base['personnalise']['domain_scores'] ?? [];
        $topScoreKey = $base['top_tracks'][0]['score_key'] ?? null;
        $secondScoreKey = $base['top_tracks'][1]['score_key'] ?? null;

        $fitTop = $this->resolveFitPercent($domainScores, $topScoreKey, 78);
        $fitSecond = $this->resolveFitPercent($domainScores, $secondScoreKey, 72);

        $demandMap = [
            'development_web' => 78,
            'data_ia' => 88,
            'cybersecurite' => 84,
            'cloud_infrastructure' => 82,
            'ui_ux' => 68,
            'qa_test' => 70,
            'mobile' => 72,
            'systemes_reseaux' => 74,
        ];

        $topDemand = $demandMap[$topScoreKey] ?? 75;
        $secondDemand = $demandMap[$secondScoreKey] ?? 70;
        $recommendedDomains = $this->buildRecommendedDomainsFromScores($domainScores, $base['diagnostic'] ?? []);
        $analysis = $this->buildIntelligentAnalysis($base, $recommendedDomains);

        return [
            'summary' => "Votre profil combine l'interpretation diagnostique ({$diagnostic}), le centre d'interet {$interestTheme}, la specialite {$specialty} et le test personnalise. "
                . "La dominante {$primary} et la piste secondaire {$secondary} sont relues avec ces donnees pour produire une orientation plus juste. "
                . "Les recommandations ci-dessous donnent des écoles, filières et métiers à prioriser au Maroc.",
            'intelligent_analysis' => $analysis,
            'recommended_domains' => $recommendedDomains,
            'recommended_schools' => [
                [
                    'name' => 'ENSA (réseau national)',
                    'city' => 'Plusieurs villes',
                    'why_fit' => 'Bonne base ingénierie pour les parcours logiciels, data et systèmes.',
                    'admission_path' => 'Après bac scientifique via concours commun.',
                ],
                [
                    'name' => 'ENSIAS',
                    'city' => 'Rabat',
                    'why_fit' => 'Spécialisations solides en informatique, cybersécurité et data.',
                    'admission_path' => 'Concours passerelle (Bac+2) ou voies internes.',
                ],
                [
                    'name' => 'INPT',
                    'city' => 'Rabat',
                    'why_fit' => 'Fort positionnement en réseaux, cloud, sécurité et télécom.',
                    'admission_path' => 'Concours national après classes préparatoires ou équivalent.',
                ],
                [
                    'name' => 'EMI',
                    'city' => 'Rabat',
                    'why_fit' => 'Parcours ingénieur polyvalent avec débouchés numériques.',
                    'admission_path' => 'Concours national après classes préparatoires.',
                ],
                [
                    'name' => 'UM6P School of Computer Science',
                    'city' => 'Benguerir',
                    'why_fit' => 'Approche orientée innovation, IA et projets appliqués.',
                    'admission_path' => 'Sélection sur dossier et processus d’admission propre.',
                ],
            ],
            'program_choices' => [
                [
                    'title' => 'Ingénierie logicielle & web',
                    'fit_percent' => $fitTop,
                    'morocco_demand_percent' => $topDemand,
                    'importance' => $this->resolveImportance($fitTop, $topDemand),
                    'notes' => 'Parcours adaptable vers backend, frontend et architecture applicative.',
                ],
                [
                    'title' => 'Data, IA et analytique',
                    'fit_percent' => max($fitSecond, 70),
                    'morocco_demand_percent' => 88,
                    'importance' => $this->resolveImportance(max($fitSecond, 70), 88),
                    'notes' => 'Fort potentiel dans les secteurs finance, retail, industrie et services.',
                ],
                [
                    'title' => 'Cybersécurité & gouvernance SI',
                    'fit_percent' => max($fitSecond - 2, 68),
                    'morocco_demand_percent' => 84,
                    'importance' => $this->resolveImportance(max($fitSecond - 2, 68), 84),
                    'notes' => 'Demande régulière sur audit, SOC, conformité et sécurité applicative.',
                ],
                [
                    'title' => 'Cloud, DevOps & infrastructure',
                    'fit_percent' => max($fitSecond - 4, 66),
                    'morocco_demand_percent' => 82,
                    'importance' => $this->resolveImportance(max($fitSecond - 4, 66), 82),
                    'notes' => 'Compétences recherchées pour la transformation cloud des entreprises.',
                ],
            ],
            'job_roles' => [
                [
                    'title' => 'Développeur Full-Stack',
                    'fit_percent' => max($fitTop, 74),
                    'morocco_demand_percent' => 80,
                    'importance' => $this->resolveImportance(max($fitTop, 74), 80),
                    'domain' => 'Ingenierie logicielle & web',
                    'missions' => ['Concevoir des applications web', 'Developper des interfaces et API', 'Maintenir la qualite du code'],
                    'skills' => ['PHP/Laravel', 'JavaScript/TypeScript', 'SQL', 'Git'],
                    'outlook' => 'Demande stable au Maroc dans les ESN, startups, banques et services digitaux.',
                    'ai_impact' => 'L IA aide a generer du code, tester plus vite et documenter les solutions.',
                    'salary_range_mad_month' => '7000 - 16000 MAD (junior à intermédiaire)',
                ],
                [
                    'title' => 'Data Analyst / Junior Data Scientist',
                    'fit_percent' => max($fitSecond, 70),
                    'morocco_demand_percent' => 86,
                    'importance' => $this->resolveImportance(max($fitSecond, 70), 86),
                    'domain' => 'Data / IA',
                    'missions' => ['Nettoyer et analyser les donnees', 'Creer des tableaux de bord', 'Aider les equipes a prendre des decisions'],
                    'skills' => ['Python', 'SQL', 'Power BI/Tableau', 'Statistiques'],
                    'outlook' => 'Perspectives fortes au Maroc dans finance, industrie, retail, telecom et services publics.',
                    'ai_impact' => 'L IA automatise l analyse, la prediction et la detection de tendances.',
                    'salary_range_mad_month' => '8000 - 18000 MAD',
                ],
                [
                    'title' => 'Ingénieur Sécurité Junior',
                    'fit_percent' => max($fitSecond - 3, 67),
                    'morocco_demand_percent' => 84,
                    'importance' => $this->resolveImportance(max($fitSecond - 3, 67), 84),
                    'domain' => 'Cybersecurite',
                    'missions' => ['Surveiller les risques', 'Analyser les incidents', 'Renforcer la securite des applications et reseaux'],
                    'skills' => ['Réseaux', 'SIEM', 'OWASP', 'Gestion des risques'],
                    'outlook' => 'Besoin croissant avec la digitalisation des entreprises marocaines.',
                    'ai_impact' => 'L IA aide a detecter les anomalies, prioriser les alertes et anticiper les attaques.',
                    'salary_range_mad_month' => '9000 - 20000 MAD',
                ],
                [
                    'title' => 'Ingénieur Cloud / DevOps Junior',
                    'fit_percent' => max($fitSecond - 4, 66),
                    'morocco_demand_percent' => 82,
                    'importance' => $this->resolveImportance(max($fitSecond - 4, 66), 82),
                    'domain' => 'Cloud / Infrastructure',
                    'missions' => ['Automatiser les deploiements', 'Superviser les plateformes', 'Optimiser performance et disponibilite'],
                    'skills' => ['Linux', 'Docker', 'CI/CD', 'AWS/Azure/GCP'],
                    'outlook' => 'Tres utile pour les entreprises qui migrent vers le cloud et les architectures modernes.',
                    'ai_impact' => 'L IA facilite la supervision, l optimisation des couts et l automatisation DevOps.',
                    'salary_range_mad_month' => '9000 - 19000 MAD',
                ],
            ],
            'market_insights' => [
                'Le marché marocain reste dynamique sur software, data, cybersécurité et cloud.',
                'Les profils combinant technique + communication projet sont les plus valorisés.',
                'Les stages et projets concrets augmentent fortement l’employabilité en sortie.',
            ],
            'next_actions' => [
                'Choisir 2 filières principales et 1 filière de secours selon vos scores.',
                'Préparer un plan de candidatures écoles (dossier, concours, calendrier).',
                'Construire 2 projets portfolio alignés avec votre piste principale.',
                'Passer une certification courte (cloud, data ou sécurité) avant la prochaine rentrée.',
            ],
        ];
    }

    private function buildRecommendedDomainsFromScores(array $domainScores, array $diagnostic = []): array
    {
        arsort($domainScores);
        $interest = $diagnostic['interest_theme_label'] ?? $diagnostic['interest_theme'] ?? null;
        $specialty = $diagnostic['specialty_label']
            ?? $diagnostic['specialty_family_label']
            ?? $diagnostic['specialty_family']
            ?? null;

        $impactMap = [
            'data_ia' => 'L IA aide a analyser de grands volumes de donnees et a construire des modeles predictifs.',
            'intelligence_artificielle' => 'L IA est le coeur du domaine: automatisation, prediction, agents intelligents et aide a la decision.',
            'cybersecurite' => 'L IA sert a detecter les attaques, analyser les alertes et renforcer la protection des systemes.',
            'cloud_infrastructure' => 'L IA optimise les ressources cloud, l automatisation DevOps et la supervision.',
            'ui_ux' => 'L IA aide a prototyper, tester des interfaces et personnaliser l experience utilisateur.',
            'management_it' => 'L IA soutient la priorisation, le pilotage projet et la transformation digitale.',
            'edtech' => 'L IA permet des parcours d apprentissage personnalises et du tutorat intelligent.',
            'technologies_emergentes' => 'L IA accelere l innovation dans les nouveaux usages numeriques.',
        ];

        return collect($domainScores)
            ->take(6)
            ->map(function ($score, $domain) use ($impactMap, $interest, $specialty): array {
                $compatibility = $this->resolveFitPercent([$domain => $score], $domain, 60);
                $label = TestPersonnaliseResultService::domainLabel((string) $domain);

                return [
                    'domain_key' => (string) $domain,
                    'label' => $label,
                    'compatibility' => $compatibility,
                    'why' => "Ce domaine ressort avec une compatibilite de {$compatibility}% apres fusion du test personnalise"
                        . ($interest ? ", du centre d'interet {$interest}" : '')
                        . ($specialty ? " et de la specialite {$specialty}" : '')
                        . ". La recommandation doit etre confirmee avec les objectifs et l'Ikigai de l eleve.",
                    'ai_impact' => $impactMap[$domain] ?? 'L IA renforce les competences, automatise certaines taches et cree de nouvelles opportunites dans ce domaine.',
                ];
            })
            ->values()
            ->all();
    }

    private function buildIntelligentAnalysis(array $base, array $recommendedDomains): array
    {
        $axisScores = $base['personnalise']['axis_scores'] ?? [];
        $diagnostic = $base['diagnostic'] ?? [];
        arsort($axisScores);

        $topAxes = collect($axisScores)->take(3);
        $lowAxes = collect($axisScores)->sort()->take(2);
        $topDomains = collect($recommendedDomains)->take(3)->pluck('label')->join(', ');
        $interest = $diagnostic['interest_theme_label'] ?? $diagnostic['interest_theme'] ?? 'non precise';
        $specialty = $diagnostic['specialty_label']
            ?? $diagnostic['specialty_family_label']
            ?? $diagnostic['specialty_family']
            ?? 'non precisee';
        $diagnosticLabel = $diagnostic['label'] ?? 'diagnostic non precise';
        $diagnosticInterpretation = $diagnostic['interpretation'] ?? $diagnostic['summary'] ?? '';

        return [
            'strengths' => $topAxes
                ->map(fn ($score, $axis): string => TestPersonnaliseQuestionnaire::axisLabel((string) $axis) . ": {$score}%")
                ->prepend("Diagnostic: {$diagnosticLabel}")
                ->values()
                ->all(),
            'improvements' => $lowAxes
                ->map(fn ($score, $axis): string => 'Renforcer progressivement ' . TestPersonnaliseQuestionnaire::axisLabel((string) $axis) . " pour stabiliser le projet d orientation ({$score}%).")
                ->values()
                ->all(),
            'compatibility_explanation' => $topDomains !== ''
                ? "Les compatibilites les plus fortes se dirigent vers {$topDomains}. Ce classement vient de la fusion entre le test personnalise, l'interpretation diagnostique, le centre d'interet {$interest} et la specialite {$specialty}."
                : 'Les compatibilites seront plus fiables apres la completion des tests.',
            'student_explanation' => trim('Ces resultats indiquent les domaines ou tes traits personnels, tes centres d interet et ta specialite peuvent devenir des forces pour apprendre, creer des projets et choisir une voie numerique. ' . $diagnosticInterpretation),
            'parents_explanation' => 'Le rapport ne ferme pas les choix: il montre les pistes les plus coherentes et les points a accompagner avec encouragement, projets et information sur les parcours.',
            'teachers_explanation' => 'L analyse permet de cibler les forces, les besoins pedagogiques et les domaines numeriques a explorer par projets, stages, clubs ou modules complementaires.',
        ];
    }

    private function resolveFitPercent(array $domainScores, ?string $scoreKey, int $default): int
    {
        if ($scoreKey === null || ! isset($domainScores[$scoreKey])) {
            return $default;
        }

        $value = (float) $domainScores[$scoreKey];

        if ($value <= 1.0) {
            $value *= 100;
        }

        return (int) max(0, min(100, round($value)));
    }

    private function resolveImportance(int $fitPercent, int $demandPercent): string
    {
        $score = ($fitPercent + $demandPercent) / 2;

        if ($score >= 78) {
            return 'élevée';
        }

        if ($score >= 62) {
            return 'moyenne';
        }

        return 'faible';
    }

    private function normalizeModelName(string $model): string
    {
        $normalized = trim($model);
        $normalized = preg_replace('/^models\//i', '', $normalized) ?: $normalized;
        $normalized = strtolower($normalized);

        if ($normalized === '') {
            return 'gemini-1.5-flash';
        }

        return $normalized;
    }
}
