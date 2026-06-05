<?php

namespace App\Services\Orientation;

use App\Models\AcademicDiagnostic;
use App\Models\TestPersonnalise;
use App\Models\User;
use App\Services\TestPersonnalises\TestPersonnaliseQuestionnaire;
use App\Services\TestPersonnalises\TestPersonnaliseResultService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIOrientationService
{
    private AcademicDiagnostic $diagnostic;
    private TestPersonnalise $personality;
    private User $user;
    private string $apiKey;
    private string $apiEndpoint;

    public function __construct(AcademicDiagnostic $diagnostic, TestPersonnalise $personality, User $user)
    {
        $this->diagnostic = $diagnostic;
        $this->personality = $personality;
        $this->user = $user;
        $this->apiKey = config('services.gemini.api_key') ?? env('GEMINI_API_KEY');
        $model = $this->normalizeModelName((string) config('services.gemini.model', 'gemini-1.5-flash'));
        $this->apiEndpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";
    }

    public function generateFullReport(): array
    {
        return Cache::remember($this->cacheKey(), now()->addHours(6), function () {
            try {
                $prompt = $this->buildPrompt();
                $response = $this->callAI($prompt);

                return $this->parseAIResponse($response);
            } catch (\Throwable $e) {
                Log::error('AI Orientation Report Error: ' . $e->getMessage());
                return $this->buildLocalReport($e->getMessage());
            }
        });
    }

    private function buildPrompt(): string
    {
        $studentData = $this->formatStudentData();
        $diagnosticData = $this->formatDiagnosticData();
        $personalityData = $this->formatPersonalityData();

        return <<<PROMPT
Tu es un conseiller d'orientation scolaire expert du système éducatif marocain, spécialisé dans les parcours informatiques, data, IA, cybersécurité, cloud, réseaux, développement logiciel, mobile, QA et UX/UI.

OBJECTIF DE LA PLATEFORME:
Accompagner les élèves marocains dans leur orientation vers les métiers de l'informatique avec l'aide de l'IA. Le rapport doit être professionnel, personnalisé et basé sur les réponses réelles des tests.

DONNÉES ÉTUDIANT:
{$studentData}

RÉSULTATS TEST DIAGNOSTIQUE:
{$diagnosticData}

RÉSULTATS TEST DE PERSONNALITÉ:
{$personalityData}

TÂCHE: Génère un rapport d'orientation complet basé sur les données ci-dessus, en reliant explicitement diagnostic, test personnalisé, réponses détaillées, scores et objectifs IT.

IMPORTANT: Retourne la réponse UNIQUEMENT en JSON valide, sans aucun texte avant ou après.

CONTRAT DE SORTIE OBLIGATOIRE:
Le bloc ci-dessous décrit les cles et le type de contenu attendu. Ce n'est PAS un exemple de réponse à copier.
Dans ta réponse finale, retourne un JSON valide: les scores doivent être des nombres entiers, et tous les textes doivent être remplacés par une analyse issue des tests.
{
  "global_summary": {
    "score": <entier 0-100 calcule a partir de la coherence diagnostic + Ikigai + personnalite>,
    "profile": "<profil dominant personnalise, jamais generique>",
    "strengths": ["<force prouvee par une reponse ou un score>", "<force prouvee>", "<force prouvee>"],
    "improvements": ["<axe de progression lie a un score faible ou une reponse>", "<axe de progression concret>"],
    "potential": "<potentiel formule selon les resultats reels>"
  },
  "pedagogical_analysis": {
    "student_explanation": "<explication simple pour l'eleve: ce que ses resultats veulent dire et comment les utiliser>",
    "parents_explanation": "<lecture rassurante et concrete pour les parents: appuis, vigilance, accompagnement>",
    "teachers_explanation": "<lecture professionnelle pour enseignant/conseiller: besoins pedagogiques, style d'accompagnement>",
    "strengths_reading": "<analyse des forces detectees avec preuves issues des tests>",
    "improvement_reading": "<analyse des points d'amelioration avec conseils bienveillants>"
  },
  "competences": {
    "math": <score deduit du niveau, matieres preferees, logique et reponses>,
    "physics": <score deduit du niveau et des matieres>,
    "it": <score deduit des interets numeriques, domaines et objectifs>,
    "french": <score deduit de langue/parcours si disponible, sinon estimation prudente>,
    "english": <score deduit de readiness langue technique si disponible, sinon estimation prudente>,
    "arabic": <score deduit du contexte si disponible, sinon estimation prudente>,
    "communication": <score lie au trait communication>,
    "problem_solving": <score lie aux traits analytique, autonomie, adaptabilite>,
    "creativity": <score lie au trait creativite>,
    "leadership": <score lie au trait leadership>
  },
  "personality": {
    "profile": "<profil psychologique dominant tire des scores de personnalite>",
    "learning_style": "<style d'apprentissage deduit des traits dominants>",
    "teamwork": "<style de travail deduit du score travail en equipe + communication>",
    "decision_making": "<mode de decision deduit de analytique, stress, autonomie>",
    "motivations": ["<motivation issue du diagnostic>", "<motivation issue de l'Ikigai>", "<motivation issue de la personnalite>"]
  },
  "ikigai": {
    "love": "<synthese de ce que l'eleve aime selon ses reponses>",
    "good_at": "<forces naturelles et academiques selon reponses + scores>",
    "world_needs": "<besoin du monde ou du Maroc relie aux interets de l'eleve>",
    "profession": "<profession ou famille de metiers plausible>",
    "intersection": "<intersection passion + mission + vocation + profession, personnalisee>"
  },
  "future_domains": [
    {
      "name": "<domaine numerique d'avenir adapte au profil>",
      "compatibility": <entier 0-100>,
      "why": "<preuve: citer reponse, score ou trait qui justifie>",
      "ai_impact": "<impact concret de l'IA dans ce domaine>",
      "associated_careers": ["<metier associe>", "<metier associe>"]
    }
  ],
  "strengths": {
    "major": ["<force majeure prouvee>", "<force majeure prouvee>", "<force majeure prouvee>"],
    "to_develop": ["<competence a developper avec raison>", "<competence a developper avec raison>"]
  },
  "filieres": [
    {"name": "<filiere marocaine ou numerique adaptee>", "compatibility": <entier 0-100>}
  ],
  "orientation": [
    {
      "filiere": "<premier choix reellement adapte>",
      "compatibility": <entier 0-100>,
      "justification": "<justification avec au moins deux preuves des tests>"
    },
    {
      "filiere": "<deuxieme choix reellement adapte>",
      "compatibility": <entier 0-100>,
      "justification": "<justification avec preuves>"
    },
    {
      "filiere": "<troisieme choix reellement adapte>",
      "compatibility": <entier 0-100>,
      "justification": "<justification avec preuves>"
    }
  ],
  "schools": [
    {
      "school": "<ecole, universite, BTS, EST, FST, ENSA, OFPPT ou parcours marocain adapte>",
      "domain": "<domaine relie au choix>",
      "access_level": "<niveau d'acces adapte au niveau de l'eleve>",
      "conditions": "<conditions ou voie d'acces prudente>",
      "strengths": "<pourquoi ce parcours correspond aux resultats>"
    }
  ],
  "careers": [
    {
      "title": "<metier adapte au profil>",
      "domain": "<domaine recommande auquel ce metier appartient>",
      "compatibility": <entier 0-100>,
      "education": "<niveau d'etudes ou parcours>",
      "outlook": "<perspective au Maroc + international si pertinent>",
      "missions": ["<mission principale>", "<mission principale>"],
      "required_skills": ["<competence technique ou humaine>", "<competence technique ou humaine>"],
      "ai_impact": "<comment l'IA transforme ou renforce ce metier>"
    }
  ],
  "education_paths": {
    "morocco": {
      "college": ["<choix ou habitudes utiles au college selon le niveau>"],
      "lycee": ["<filiere lycee conseillee: sciences maths, sciences physiques, sciences eco, etc.>"],
      "bts_dut": ["<BTS, DUT/EST ou parcours court adapte>"],
      "fp_fs": ["<FP/OFPPT, FS, FST ou passerelle adaptee>"],
      "licences": ["<licence ou licence professionnelle adaptee>"],
      "engineering_schools": ["<ENSA, ENSIAS, INPT, EMI, EHTP, autres ecoles selon pertinence>"],
      "universities": ["<universite ou faculte marocaine pertinente>"]
    },
    "international": {
      "degrees": ["<formation reconnue internationale>"],
      "certifications": ["<certification pertinente: Google, Microsoft, Cisco, AWS, etc.>"],
      "platforms": ["<plateforme d'apprentissage adaptee>"]
    }
  },
  "success": {
    "probability": <entier 0-100>,
    "confidence": "<Tres elevee|Elevee|Moyenne|A renforcer>",
    "risks": ["<risque concret deduit d'un score ou contexte, formule positivement>"],
    "factors": ["<facteur de reussite deduit des forces>", "<facteur de reussite concret>"],
    "stars": <entier 1-5>
  },
  "action_plan": {
    "one_year": ["<action sur 1 an adaptee aux resultats>"],
    "three_years": ["<action sur 3 ans adaptee aux resultats>"],
    "five_years": ["<action sur 5 ans adaptee aux resultats>"]
  },
  "motivation": {
    "message": "<message personnalise qui cite le prenom/profil/Ikigai si disponible, sans formule standard>"
  }
}

REGLES ANTI-RAPPORT GENERIQUE:
1. Ne recopie jamais les valeurs d'exemple du schema. Les chevrons <...> doivent etre remplaces par des donnees deduites des tests.
2. Si une donnee manque, fais une estimation prudente et indique-la indirectement comme hypothese, sans inventer de faux fait.
3. Chaque score doit varier selon les reponses: ne donne pas toujours 75, 80, 85 ou 90.
4. Chaque recommandation importante doit mentionner une preuve: matiere preferee, activite, objectif, dimension Ikigai, trait de personnalite ou score.
5. Si le profil ne correspond pas fortement a l'informatique pure, propose une passerelle numerique coherente: sante numerique, edtech, fintech, agritech, design numerique, data, transformation digitale.
6. Le diagnostic sert au profil initial et a l'Ikigai; le test personnalise sert aux traits dominants de personnalite.
7. Le rapport doit etre different d'un eleve a l'autre.
8. Ton bienveillant, pedagogique, professionnel, jamais decourageant.
9. Adapte au systeme educatif marocain et au niveau scolaire de l'eleve.
10. Propose seulement des ecoles/parcours plausibles pour le niveau et l'orientation.
11. Avant de retourner le JSON, verifie mentalement chaque champ: s'il ne depend pas d'au moins une donnee fournie, rends-le plus prudent ou remplace-le.
12. Les listes doivent etre classees par compatibilite reelle avec le profil, pas par popularite generale.
13. Les domaines recommandes doivent avoir un score de compatibilite justifie et classe: propose 5 a 8 domaines maximum.
14. Pour chaque metier, explique missions, competences, perspectives et impact de l'IA de maniere simple.
15. Le parcours de formation doit distinguer clairement Maroc et international, avec des options realistes selon le niveau.
16. Le rapport doit etre comprehensible pour trois publics: eleve, parents et enseignants.

RETOUR: JSON UNIQUEMENT, valide et parsable.
PROMPT;
    }

    private function formatStudentData(): string
    {
        $profile = $this->user->studentProfile;
        $name = $this->user->name ?? 'Non spécifié';
        $level = $this->diagnostic->academic_level ?? 'Non spécifié';
        $age = $this->calculateAge() ?? 'Non spécifié';
        $city = $profile?->city ?? $this->user->city ?? 'Non spécifié';
        $school = $profile?->school_name ?? 'Non spécifié';
        $schoolType = $profile?->school_type ?? 'Non spécifié';

        return <<<DATA
Nom: {$name}
Niveau: {$level}
Âge: {$age}
Ville: {$city}
Ecole actuelle: {$school}
Type d'ecole: {$schoolType}
DATA;
    }

    private function formatDiagnosticData(): string
    {
        $payload = $this->diagnostic->result_payload ?? [];
        $domains = $payload['orientation_domains'] ?? [];
        $schools = $payload['recommended_schools'] ?? [];
        $code = $this->diagnostic->result_code ?? 'Non spécifié';
        $label = $this->diagnostic->result_label ?? 'Non spécifié';
        $summary = $this->diagnostic->result_summary ?? 'Non disponible';
        $domainsStr = $this->stringifyList($domains);
        $schoolsStr = $this->stringifyList($schools);
        $answersStr = $this->formatDiagnosticAnswers();

        return <<<DATA
Code Résultat: {$code}
Libellé: {$label}
Résumé: {$summary}
Domaines d'Orientation: {$domainsStr}
Écoles Recommandées: {$schoolsStr}
Reponses Diagnostic IT: {$answersStr}
DATA;
    }

    private function formatPersonalityData(): string
    {
        $axiScores = $this->personality->axis_scores ?? [];
        $domainScores = $this->personality->domain_scores ?? [];
        $summary = $this->personality->result_summary ?? 'Non disponible';
        $primaryDomain = $this->personality->primary_domain ?? 'Non déterminé';

        $axisFormatted = collect($axiScores)->map(fn ($score, $axis) => "$axis: $score")->join(", ");
        $domainFormatted = collect($domainScores)->map(fn ($score, $domain) => "$domain: $score")->join(", ");
        $answersFormatted = $this->formatPersonalityAnswers();

        return <<<DATA
Résumé: {$summary}
Domaine Principal: {$primaryDomain}
Scores Axes: {$axisFormatted}
Scores Domaines: {$domainFormatted}
Reponses Detaillees: {$answersFormatted}
DATA;
    }

    private function formatDiagnosticAnswers(): string
    {
        $answers = $this->diagnostic->diagnostic_answers ?? [];

        if (empty($answers)) {
            return 'Aucune reponse detaillee disponible.';
        }

        $labels = [
            'preferred_subjects' => 'Matieres preferees',
            'interests' => 'Centres d interet',
            'favorite_activities' => 'Activites favorites',
            'future_ambitions' => 'Ambitions futures',
            'career_goals' => 'Objectifs professionnels',
            'motivations' => 'Motivations',
            'family_school_environment' => 'Environnement familial et scolaire',
            'ikigai_love' => 'Ikigai - Ce que l eleve aime',
            'ikigai_good_at' => 'Ikigai - Ce dans quoi l eleve excelle',
            'ikigai_world_needs' => 'Ikigai - Ce dont le monde a besoin',
            'ikigai_profession' => 'Ikigai - Ce qui peut devenir une profession',
            'math_logic' => 'Mathematiques, logique et raisonnement',
            'computer_ease' => 'Aisance avec ordinateur, Internet et outils numeriques',
            'digital_ease' => 'Culture numerique et outils informatiques',
            'programming_interest' => 'Interet pour programmation et applications',
            'data_ai_interest' => 'Interet pour donnees, IA et tableaux',
            'cyber_network_interest' => 'Interet pour cybersecurite, reseaux et protection',
            'design_product_interest' => 'Interet pour interfaces, produit et experience utilisateur',
            'autonomy_project' => 'Autonomie sur projet long',
            'french_english_readiness' => 'Disponibilite pour renforcer francais et anglais technique',
            'service_motivation' => 'Motivation pour un metier utile aux autres',
            'future_market_interest' => 'Interet pour metiers d avenir et secteurs en croissance',
        ];

        return collect($answers)
            ->map(function ($answer, $key) use ($labels): string {
                $value = is_numeric($answer) ? $answer . '/5' : $this->stringifyAnswerValue($answer);

                return ($labels[$key] ?? $key) . ': ' . $value;
            })
            ->join(' | ');
    }

    private function stringifyAnswerValue(mixed $answer): string
    {
        if (is_array($answer)) {
            return collect($answer)
                ->map(fn ($value) => is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string) $value)
                ->filter(fn ($value) => trim((string) $value) !== '')
                ->join(', ');
        }

        if (is_bool($answer)) {
            return $answer ? 'oui' : 'non';
        }

        return (string) $answer;
    }

    private function stringifyList(array $items): string
    {
        return collect($items)
            ->map(function ($item): string {
                if (is_array($item)) {
                    return json_encode($item, JSON_UNESCAPED_UNICODE) ?: '';
                }

                return (string) $item;
            })
            ->filter(fn ($item) => trim($item) !== '')
            ->join(', ');
    }

    private function formatPersonalityAnswers(): string
    {
        $answers = $this->personality->answers ?? [];

        if (empty($answers)) {
            return 'Aucune reponse detaillee disponible.';
        }

        $questionMap = TestPersonnaliseQuestionnaire::questionMap();

        return collect($answers)
            ->map(function ($score, $questionId) use ($questionMap): string {
                $question = $questionMap[$questionId]['text'] ?? $questionId;
                $axis = $questionMap[$questionId]['axis_label'] ?? 'Axe non specifie';

                return "{$axis} - {$question}: {$score}/5";
            })
            ->join(' | ');
    }

    private function callAI(string $prompt): string
    {
        if (!$this->apiKey) {
            throw new \Exception('Gemini API Key not configured');
        }

        $url = $this->apiEndpoint . '?key=' . $this->apiKey;

        $response = Http::timeout(45)->acceptJson()->asJson()->post($url, [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        [
                            'text' => $prompt,
                        ],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.35,
                'topP' => 0.85,
                'maxOutputTokens' => 4000,
            ],
        ]);

        if ($response->failed()) {
            Log::error('Gemini API Error: ' . $response->status() . ' - ' . $response->body());
            throw new \Exception('API Error: ' . $response->status());
        }

        $data = $response->json();
        
        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            return $data['candidates'][0]['content']['parts'][0]['text'];
        }

        throw new \Exception('Invalid response structure from Gemini API');
    }

    private function parseAIResponse(string $response): array
    {
        // Extraire le JSON de la réponse
        $jsonMatch = preg_match('/\{[\s\S]*\}/', $response, $matches);
        
        if (!$jsonMatch) {
            throw new \Exception('Invalid JSON response from AI');
        }

        $data = json_decode($matches[0], true);

        if (!$data) {
            throw new \Exception('Failed to parse AI response');
        }

        return $this->formatResponse($data);
    }

    private function formatResponse(array $aiData): array
    {
        return [
            'global_summary' => [
                'global_score' => $aiData['global_summary']['score'] ?? 75,
                'dominant_profile' => $aiData['global_summary']['profile'] ?? 'Profil Équilibré',
                'main_strengths' => $aiData['global_summary']['strengths'] ?? [],
                'improvement_axes' => $aiData['global_summary']['improvements'] ?? [],
                'overall_potential' => $aiData['global_summary']['potential'] ?? 'Bon potentiel',
                'summary_text' => $this->buildSummaryText($aiData['global_summary'] ?? []),
            ],
            'pedagogical_analysis' => [
                'student_explanation' => $aiData['pedagogical_analysis']['student_explanation'] ?? '',
                'parents_explanation' => $aiData['pedagogical_analysis']['parents_explanation'] ?? '',
                'teachers_explanation' => $aiData['pedagogical_analysis']['teachers_explanation'] ?? '',
                'strengths_reading' => $aiData['pedagogical_analysis']['strengths_reading'] ?? '',
                'improvement_reading' => $aiData['pedagogical_analysis']['improvement_reading'] ?? '',
            ],
            'competences_analysis' => $this->formatCompetences($aiData['competences'] ?? []),
            'personality_analysis' => [
                'dominant_profile' => $aiData['personality']['profile'] ?? 'Non déterminé',
                'learning_style' => $aiData['personality']['learning_style'] ?? 'Apprentissage équilibré',
                'teamwork_style' => $aiData['personality']['teamwork'] ?? 'Collaboratif',
                'decision_making' => $aiData['personality']['decision_making'] ?? 'Réfléchie',
                'main_motivations' => $aiData['personality']['motivations'] ?? [],
                'axis_scores' => [],
            ],
            'ikigai_analysis' => [
                'love' => $aiData['ikigai']['love'] ?? '',
                'good_at' => $aiData['ikigai']['good_at'] ?? '',
                'world_needs' => $aiData['ikigai']['world_needs'] ?? '',
                'profession' => $aiData['ikigai']['profession'] ?? '',
                'intersection' => $aiData['ikigai']['intersection'] ?? '',
            ],
            'future_domains_analysis' => $this->formatFutureDomains($aiData['future_domains'] ?? []),
            'strengths_radar' => [
                'major_strengths' => $aiData['strengths']['major'] ?? [],
                'skills_to_develop' => $aiData['strengths']['to_develop'] ?? [],
                'quick_wins' => $this->generateQuickWins(),
            ],
            'compatibility_filiales' => $this->formatFilieres($aiData['filieres'] ?? []),
            'orientation_recommended' => $this->formatOrientation($aiData['orientation'] ?? []),
            'schools_recommended' => $this->formatSchools($aiData['schools'] ?? []),
            'compatible_careers' => $this->formatCareers($aiData['careers'] ?? []),
            'education_paths' => $this->formatEducationPaths($aiData['education_paths'] ?? []),
            'success_predictions' => [
                'success_probability' => $aiData['success']['probability'] ?? 75,
                'confidence_level' => $aiData['success']['confidence'] ?? 'Élevée',
                'potential_risks' => $aiData['success']['risks'] ?? [],
                'success_factors' => $aiData['success']['factors'] ?? [],
                'star_rating' => $aiData['success']['stars'] ?? 4,
            ],
            'action_plan' => [
                'short_term' => $aiData['action_plan']['one_year'] ?? $aiData['action_plan']['short_term'] ?? [],
                'medium_term' => $aiData['action_plan']['three_years'] ?? $aiData['action_plan']['medium_term'] ?? [],
                'long_term' => $aiData['action_plan']['five_years'] ?? $aiData['action_plan']['long_term'] ?? [],
            ],
            'motivation_message' => $aiData['motivation']['message'] ?? $this->getDefaultMotivation(),
        ];
    }

    private function formatCompetences(array $data): array
    {
        return [
            'mathematics' => $data['math'] ?? 75,
            'physics' => $data['physics'] ?? 72,
            'informatics' => $data['it'] ?? 80,
            'french' => $data['french'] ?? 68,
            'english' => $data['english'] ?? 70,
            'arabic' => $data['arabic'] ?? 65,
            'communication' => $data['communication'] ?? 78,
            'problem_solving' => $data['problem_solving'] ?? 82,
            'creativity' => $data['creativity'] ?? 76,
            'leadership' => $data['leadership'] ?? 71,
        ];
    }

    private function formatFilieres(array $data): array
    {
        return array_map(fn ($item) => [
            'filiere' => $item['name'] ?? 'Non spécifié',
            'compatibility' => (int) ($item['compatibility'] ?? 50),
        ], $data);
    }

    private function formatOrientation(array $data): array
    {
        $orientations = [];
        foreach ($data as $item) {
            $orientations[] = [
                'filiere' => $item['filiere'] ?? 'Non spécifié',
                'compatibility' => (int) ($item['compatibility'] ?? 70),
                'justification' => $item['justification'] ?? 'Recommandé selon le profil.',
            ];
        }
        return array_slice($orientations, 0, 3);
    }

    private function formatSchools(array $data): array
    {
        $grouped = [];
        foreach ($data as $school) {
            $domain = $school['domain'] ?? 'Général';
            if (!isset($grouped[$domain])) {
                $grouped[$domain] = [];
            }
            $grouped[$domain][] = [
                'school' => $school['school'] ?? 'Non spécifié',
                'access_level' => $school['access_level'] ?? 'À déterminer',
                'conditions' => $school['conditions'] ?? 'À vérifier',
                'strengths' => $school['strengths'] ?? 'Établissement réputé',
            ];
        }
        return $grouped;
    }

    private function formatCareers(array $data): array
    {
        return array_map(fn ($career) => [
            'career' => $career['title'] ?? 'Non spécifié',
            'domain' => $career['domain'] ?? 'Domaine numérique',
            'compatibility' => (int) ($career['compatibility'] ?? 50),
            'education_level' => $career['education'] ?? 'Variable',
            'job_outlook' => $career['outlook'] ?? 'Bon',
            'missions' => $career['missions'] ?? [],
            'required_skills' => $career['required_skills'] ?? [],
            'ai_impact' => $career['ai_impact'] ?? '',
        ], array_slice($data, 0, 10));
    }

    private function formatFutureDomains(array $data): array
    {
        return array_map(fn ($domain) => [
            'name' => $domain['name'] ?? 'Domaine numérique',
            'compatibility' => (int) ($domain['compatibility'] ?? 50),
            'why' => $domain['why'] ?? 'Correspondance à approfondir selon les réponses.',
            'ai_impact' => $domain['ai_impact'] ?? 'L IA renforce les compétences et les opportunités dans ce domaine.',
            'associated_careers' => $domain['associated_careers'] ?? [],
        ], array_slice($data, 0, 6));
    }

    private function formatEducationPaths(array $data): array
    {
        $morocco = $data['morocco'] ?? [];
        $international = $data['international'] ?? [];

        return [
            'morocco' => [
                'college' => $morocco['college'] ?? [],
                'lycee' => $morocco['lycee'] ?? [],
                'bts_dut' => $morocco['bts_dut'] ?? [],
                'fp_fs' => $morocco['fp_fs'] ?? [],
                'licences' => $morocco['licences'] ?? [],
                'engineering_schools' => $morocco['engineering_schools'] ?? [],
                'universities' => $morocco['universities'] ?? [],
            ],
            'international' => [
                'degrees' => $international['degrees'] ?? [],
                'certifications' => $international['certifications'] ?? [],
                'platforms' => $international['platforms'] ?? [],
            ],
        ];
    }

    private function buildSummaryText(array $summary): string
    {
        $profile = $summary['profile'] ?? 'équilibré';
        $level = $this->diagnostic->academic_level ?? 'non spécifié';

        return "L'élève présente un profil {$profile} avec un niveau académique de {$level}. " .
               "Les analyses montrent des aptitudes solides et un potentiel certain pour la réussite.";
    }

    private function generateQuickWins(): array
    {
        return [
            'Approfondir les domaines forts avec des projets personnels',
            'Créer un portfolio de réalisations et projets',
            'Participer aux clubs et activités extrascolaires',
            'Chercher un mentor dans le domaine visé',
            'Suivre des cours en ligne dans les domaines d\'intérêt',
        ];
    }

    private function buildLocalReport(string $reason = ''): array
    {
        $diagnosticAnswers = $this->diagnostic->diagnostic_answers ?? [];
        $axisScores = $this->personality->axis_scores ?? [];
        $domainScores = $this->personality->domain_scores ?? [];

        if (empty($domainScores) && ! empty($this->personality->answers)) {
            $calculated = app(TestPersonnaliseResultService::class)->calculate($this->personality->answers ?? []);
            $axisScores = $axisScores ?: ($calculated['axis_scores'] ?? []);
            $domainScores = $calculated['domain_scores'] ?? [];
        }

        $domainScores = $this->mergeDiagnosticSignals($domainScores, $diagnosticAnswers);
        arsort($domainScores);

        $domainLabels = TestPersonnaliseResultService::domainLabels();
        $topDomains = array_slice($domainScores, 0, 6, true);
        $topDomainKey = array_key_first($topDomains);
        $topDomainLabel = $topDomainKey ? ($domainLabels[$topDomainKey] ?? $topDomainKey) : 'Informatique et numerique';
        $globalScore = $this->localGlobalScore($topDomains, $axisScores);
        $profile = $this->localPersonalityProfile($axisScores);
        $careers = $this->localCareers($topDomains, $domainLabels);

        return [
            'global_summary' => [
                'global_score' => $globalScore,
                'dominant_profile' => $profile,
                'main_strengths' => $this->localStrengths($axisScores, $diagnosticAnswers),
                'improvement_axes' => $this->localImprovements($axisScores),
                'overall_potential' => "Potentiel favorable vers {$topDomainLabel}",
                'summary_text' => "Le profil combine le diagnostic ({$this->diagnostic->result_label}), la specialite {$this->diagnostic->specialty_label}, les centres d'interet et le test personnalise. La piste la plus forte actuellement est {$topDomainLabel}.",
            ],
            'pedagogical_analysis' => [
                'student_explanation' => "Tes resultats montrent une orientation numerique coherente vers {$topDomainLabel}. Ce choix vient de tes reponses, de tes centres d'interet et de tes traits de personnalite.",
                'parents_explanation' => "Le rapport croise le diagnostic scolaire, la specialite, l'Ikigai et le test personnalise. Il donne des pistes progressives, sans fermer les autres choix.",
                'teachers_explanation' => "Les domaines recommandes peuvent etre explores par projets, clubs, mini-stages, portfolio et accompagnement sur les competences a renforcer.",
                'strengths_reading' => implode(' ', $this->localStrengths($axisScores, $diagnosticAnswers)),
                'improvement_reading' => implode(' ', $this->localImprovements($axisScores)),
            ],
            'competences_analysis' => $this->localCompetences($axisScores, $diagnosticAnswers),
            'personality_analysis' => [
                'dominant_profile' => $profile,
                'learning_style' => $this->axisScore($axisScores, 'curiosity', 70) >= 75 ? 'Apprentissage par exploration, projets et recherche' : 'Apprentissage progressif avec exemples concrets',
                'teamwork_style' => $this->axisScore($axisScores, 'teamwork', 70) >= 70 ? 'Collaboratif et ouvert au travail en equipe' : 'Plus efficace avec un cadre clair et des roles definis',
                'decision_making' => $this->axisScore($axisScores, 'analytical', 70) >= 70 ? 'Decision analytique basee sur les faits' : 'Decision a renforcer par comparaison et conseil',
                'main_motivations' => array_values(array_filter([
                    $diagnosticAnswers['motivations'] ?? null,
                    $diagnosticAnswers['career_goals'] ?? null,
                    $diagnosticAnswers['ikigai_profession'] ?? null,
                ])),
                'axis_scores' => $axisScores,
            ],
            'ikigai_analysis' => [
                'love' => $diagnosticAnswers['ikigai_love'] ?? $diagnosticAnswers['interests'] ?? '',
                'good_at' => $diagnosticAnswers['ikigai_good_at'] ?? $diagnosticAnswers['preferred_subjects'] ?? '',
                'world_needs' => $diagnosticAnswers['ikigai_world_needs'] ?? 'Le Maroc et le monde ont besoin de competences numeriques, d innovation, de securite et de solutions utiles.',
                'profession' => $diagnosticAnswers['ikigai_profession'] ?? ($careers[0]['career'] ?? $topDomainLabel),
                'intersection' => "L'intersection Ikigai indique une piste entre ce que l'eleve aime, ses forces scolaires/personnelles et des metiers numeriques comme {$topDomainLabel}.",
            ],
            'future_domains_analysis' => $this->localFutureDomains($topDomains, $domainLabels),
            'strengths_radar' => [
                'major_strengths' => $this->localStrengths($axisScores, $diagnosticAnswers),
                'skills_to_develop' => $this->localImprovements($axisScores),
                'quick_wins' => $this->generateQuickWins(),
            ],
            'compatibility_filiales' => $this->localFilieres($topDomains, $domainLabels),
            'orientation_recommended' => $this->localOrientation($topDomains, $domainLabels),
            'schools_recommended' => $this->localSchools($topDomainLabel),
            'compatible_careers' => $careers,
            'education_paths' => $this->localEducationPaths($topDomainLabel),
            'success_predictions' => [
                'success_probability' => $globalScore,
                'confidence_level' => $globalScore >= 80 ? 'Elevee' : 'Moyenne',
                'potential_risks' => $this->localImprovements($axisScores),
                'success_factors' => $this->localStrengths($axisScores, $diagnosticAnswers),
                'star_rating' => max(3, min(5, (int) round($globalScore / 20))),
            ],
            'action_plan' => [
                'short_term' => [
                    "Sur 1 an: consolider les bases en mathematiques, logique, programmation et anglais technique.",
                    "Realiser 2 petits projets lies a {$topDomainLabel} et les mettre dans un portfolio.",
                ],
                'medium_term' => [
                    "Sur 3 ans: choisir une filiere coherente avec {$topDomainLabel} et chercher des stages ou clubs numeriques.",
                    'Developper des certifications courtes selon le domaine recommande.',
                ],
                'long_term' => [
                    'Sur 5 ans: viser une licence, une ecole d ingenieur ou une formation specialisee avec projets reels.',
                    'Construire un profil professionnel: portfolio, stages, soft skills et veille IA.',
                ],
            ],
            'motivation_message' => "Les resultats mettent en avant une orientation prometteuse vers {$topDomainLabel}. Cette piste doit etre exploree progressivement a travers des projets concrets, un renforcement des bases scolaires et un accompagnement regulier avec les parents et les enseignants.",
        ];
    }

    private function mergeDiagnosticSignals(array $domainScores, array $answers): array
    {
        $scores = $domainScores;
        $boost = function (array $domains, float $points) use (&$scores): void {
            foreach ($domains as $domain) {
                $scores[$domain] = min(100, (float) ($scores[$domain] ?? 0) + $points);
            }
        };

        match ($this->diagnostic->interest_theme) {
            'ai' => $boost(['data_ia', 'intelligence_artificielle', 'bases_donnees'], 10),
            'engineering' => $boost(['cloud_infrastructure', 'systemes_reseaux', 'robotique_industrie4'], 8),
            'medicine' => $boost(['sante_numerique', 'informatique_scientifique', 'data_ia'], 8),
            'business' => $boost(['fintech_numerique', 'ecommerce_marketing', 'erp_si'], 8),
            'education' => $boost(['edtech', 'transformation_digitale', 'management_it'], 8),
            'arts' => $boost(['ui_ux', 'creation_contenu', 'ecommerce_marketing'], 8),
            'agriculture' => $boost(['agritech_green', 'geomatique_smart_city', 'data_ia'], 8),
            default => null,
        };

        $answerBoosts = [
            'programming_interest' => ['development_web', 'mobile', 'langages_paradigmes'],
            'data_ai_interest' => ['data_ia', 'intelligence_artificielle', 'bases_donnees'],
            'cyber_network_interest' => ['cybersecurite', 'systemes_reseaux', 'cloud_infrastructure'],
            'design_product_interest' => ['ui_ux', 'creation_contenu', 'ecommerce_marketing'],
            'future_market_interest' => ['technologies_emergentes', 'transformation_digitale', 'data_ia'],
            'service_motivation' => ['edtech', 'sante_numerique', 'agritech_green', 'transformation_digitale'],
        ];

        foreach ($answerBoosts as $key => $domains) {
            $score = (int) ($answers[$key] ?? 0);
            if ($score >= 4) {
                $boost($domains, $score * 1.8);
            }
        }

        return $scores;
    }

    private function localCompetences(array $axisScores, array $answers): array
    {
        return [
            'mathematics' => $this->answerPercent($answers, 'math_logic', 68),
            'physics' => str_contains((string) $this->diagnostic->academic_level, 'Phys') ? 78 : 68,
            'informatics' => max($this->answerPercent($answers, 'digital_ease', 70), $this->answerPercent($answers, 'programming_interest', 65), $this->answerPercent($answers, 'data_ai_interest', 65)),
            'french' => $this->answerPercent($answers, 'french_english_readiness', 66),
            'english' => $this->answerPercent($answers, 'french_english_readiness', 66),
            'arabic' => 70,
            'communication' => $this->axisScore($axisScores, 'communication', 70),
            'problem_solving' => (int) round(($this->axisScore($axisScores, 'analytical', 70) + $this->axisScore($axisScores, 'adaptability', 70)) / 2),
            'creativity' => $this->axisScore($axisScores, 'creativity', 70),
            'leadership' => $this->axisScore($axisScores, 'leadership', 68),
        ];
    }

    private function localFutureDomains(array $topDomains, array $domainLabels): array
    {
        return collect($topDomains)->map(fn ($score, $key) => [
            'name' => $domainLabels[$key] ?? $key,
            'compatibility' => (int) round($score),
            'why' => "Ce domaine ressort apres fusion du diagnostic, de la specialite, des centres d'interet et du test personnalise.",
            'ai_impact' => 'L IA augmente la productivite, automatise certaines taches et cree de nouvelles opportunites dans ce domaine.',
            'associated_careers' => $this->careerTitlesForDomain((string) $key),
        ])->values()->all();
    }

    private function localCareers(array $topDomains, array $domainLabels): array
    {
        return collect($topDomains)->take(5)->flatMap(function ($score, $key) use ($domainLabels) {
            return collect($this->careerTitlesForDomain((string) $key))->map(fn ($career) => [
                'career' => $career,
                'domain' => $domainLabels[$key] ?? $key,
                'compatibility' => (int) round($score),
                'education_level' => 'Bac+2 a Bac+5 selon le metier',
                'job_outlook' => 'Perspectives favorables au Maroc avec la transformation digitale et l IA.',
                'missions' => ['Analyser les besoins', 'Concevoir ou ameliorer des solutions numeriques', 'Collaborer avec une equipe projet'],
                'required_skills' => ['Logique', 'Anglais technique', 'Outils numeriques', 'Communication'],
                'ai_impact' => 'L IA devient un assistant de recherche, d automatisation, d analyse et de production.',
            ]);
        })->take(10)->values()->all();
    }

    private function careerTitlesForDomain(string $domain): array
    {
        return match ($domain) {
            'data_ia', 'intelligence_artificielle' => ['Data Analyst', 'Ingenieur IA junior', 'Machine Learning Engineer'],
            'cybersecurite' => ['Analyste cybersecurite', 'Consultant securite SI'],
            'cloud_infrastructure' => ['Ingenieur Cloud junior', 'DevOps junior'],
            'ui_ux' => ['UX/UI Designer', 'Product Designer'],
            'mobile' => ['Developpeur mobile', 'Developpeur Flutter'],
            'edtech' => ['Concepteur EdTech', 'Chef de projet numerique educatif'],
            default => ['Developpeur Full-Stack', 'Analyste fonctionnel'],
        };
    }

    private function localFilieres(array $topDomains, array $domainLabels): array
    {
        return collect($topDomains)->take(5)->map(fn ($score, $key) => [
            'filiere' => $domainLabels[$key] ?? $key,
            'compatibility' => (int) round($score),
        ])->values()->all();
    }

    private function localOrientation(array $topDomains, array $domainLabels): array
    {
        return collect($topDomains)->take(3)->map(fn ($score, $key) => [
            'filiere' => $domainLabels[$key] ?? $key,
            'compatibility' => (int) round($score),
            'justification' => 'Choix recommande selon les scores de personnalite, le diagnostic initial, la specialite et les centres d interet.',
        ])->values()->all();
    }

    private function localSchools(string $topDomainLabel): array
    {
        return [
            $topDomainLabel => [
                ['school' => 'EST / DUT informatique', 'access_level' => 'Apres bac', 'conditions' => 'Selection selon notes et pre-requis', 'strengths' => 'Parcours court et professionnalisant'],
                ['school' => 'BTS Developpement des systemes d information', 'access_level' => 'Apres bac', 'conditions' => 'Dossier scolaire', 'strengths' => 'Bonne base technique'],
                ['school' => 'FST / FS - Licence informatique', 'access_level' => 'Apres bac ou Bac+2', 'conditions' => 'Selon universite et filiere', 'strengths' => 'Parcours universitaire solide'],
                ['school' => 'ENSA / ENSIAS / INPT', 'access_level' => 'Concours ou passerelles', 'conditions' => 'Prepa, Bac+2 ou concours', 'strengths' => 'Parcours ingenieur reconnu'],
            ],
        ];
    }

    private function localEducationPaths(string $topDomainLabel): array
    {
        return [
            'morocco' => [
                'college' => ['Renforcer mathematiques, logique, langues et culture numerique.'],
                'lycee' => ['Privilegier sciences mathematiques, sciences physiques ou filiere technique selon le niveau.'],
                'bts_dut' => ['BTS DSI, DUT/EST informatique, reseaux ou data selon le domaine.'],
                'fp_fs' => ['OFPPT developpement digital, FS/FST informatique, parcours data ou reseaux.'],
                'licences' => ["Licence informatique, data, cybersecurite ou parcours proche de {$topDomainLabel}."],
                'engineering_schools' => ['ENSA, ENSIAS, INPT, EMI ou ecoles privees reconnues selon dossier/concours.'],
                'universities' => ['Universites marocaines avec filieres informatique, mathematiques appliquees ou sciences des donnees.'],
            ],
            'international' => [
                'degrees' => ['Bachelor Computer Science, Data Science, Cybersecurity ou Software Engineering.'],
                'certifications' => ['Google, Cisco, AWS, Microsoft, IBM SkillsBuild selon le domaine choisi.'],
                'platforms' => ['Coursera, edX, OpenClassrooms, freeCodeCamp, Kaggle, Cisco Networking Academy.'],
            ],
        ];
    }

    private function localStrengths(array $axisScores, array $answers): array
    {
        $strengths = [];
        foreach ($axisScores as $axis => $score) {
            if ((float) $score >= 70) {
                $strengths[] = TestPersonnaliseQuestionnaire::axisLabel((string) $axis) . " ({$score}%)";
            }
        }

        if (! empty($answers['preferred_subjects'])) {
            $strengths[] = 'Matieres preferees: ' . $answers['preferred_subjects'];
        }

        return array_slice($strengths ?: ['Motivation a explorer le numerique', 'Potentiel a confirmer par projets'], 0, 5);
    }

    private function localImprovements(array $axisScores): array
    {
        $improvements = [];
        foreach ($axisScores as $axis => $score) {
            if ((float) $score > 0 && (float) $score < 65) {
                $improvements[] = 'Renforcer ' . TestPersonnaliseQuestionnaire::axisLabel((string) $axis) . " ({$score}%).";
            }
        }

        return array_slice($improvements ?: ['Renforcer progressivement l anglais technique et la pratique par projets.'], 0, 4);
    }

    private function localPersonalityProfile(array $axisScores): string
    {
        if (empty($axisScores)) {
            return 'Profil numerique a consolider';
        }

        arsort($axisScores);
        $axis = array_key_first($axisScores);

        return 'Profil oriente ' . TestPersonnaliseQuestionnaire::axisLabel((string) $axis);
    }

    private function localGlobalScore(array $topDomains, array $axisScores): int
    {
        $domainAverage = count($topDomains) > 0 ? array_sum($topDomains) / count($topDomains) : 70;
        $axisAverage = count($axisScores) > 0 ? array_sum($axisScores) / count($axisScores) : 70;

        return (int) max(55, min(95, round(($domainAverage * 0.6) + ($axisAverage * 0.4))));
    }

    private function axisScore(array $axisScores, string $axis, int $default): int
    {
        return (int) round((float) ($axisScores[$axis] ?? $default));
    }

    private function answerPercent(array $answers, string $key, int $default): int
    {
        $value = (int) ($answers[$key] ?? 0);

        return $value > 0 ? (int) round(($value / 5) * 100) : $default;
    }

    private function getDefaultMotivation(): string
    {
        return "Félicitations pour vos efforts ! Ce rapport montre que vous avez un réel potentiel. " .
               "Continuez à croire en vous et à travailler dur. Votre succès est à votre portée. " .
               "N'abandonnez jamais et cherchez toujours à vous améliorer.";
    }

    private function calculateAge(): ?int
    {
        $birthDate = $this->user->studentProfile?->birth_date;

        if (!$birthDate) {
            return null;
        }

        return (int) now()->diffInYears($birthDate);
    }

    private function cacheKey(): string
    {
        return 'ai_orientation_report:v2:' . $this->user->id
            . ':' . $this->diagnostic->id . ':' . $this->diagnostic->updated_at?->timestamp
            . ':' . $this->personality->id . ':' . $this->personality->updated_at?->timestamp;
    }

    private function normalizeModelName(string $model): string
    {
        $normalized = trim($model);
        $normalized = preg_replace('/^models\//i', '', $normalized) ?: $normalized;

        return $normalized !== '' ? strtolower($normalized) : 'gemini-1.5-flash';
    }

    private function getFallbackReport(): array
    {
        // Rapport de secours en cas d'erreur API
        return [
            'global_summary' => [
                'global_score' => 70,
                'dominant_profile' => 'Profil à déterminer',
                'main_strengths' => [],
                'improvement_axes' => [],
                'overall_potential' => 'Bon potentiel',
                'summary_text' => 'Rapport temporairement indisponible. Veuillez réessayer.',
            ],
            'competences_analysis' => [],
            'pedagogical_analysis' => [
                'student_explanation' => '',
                'parents_explanation' => '',
                'teachers_explanation' => '',
                'strengths_reading' => '',
                'improvement_reading' => '',
            ],
            'personality_analysis' => [],
            'ikigai_analysis' => [],
            'future_domains_analysis' => [],
            'strengths_radar' => ['major_strengths' => [], 'skills_to_develop' => []],
            'compatibility_filiales' => [],
            'orientation_recommended' => [],
            'schools_recommended' => [],
            'compatible_careers' => [],
            'education_paths' => [
                'morocco' => [
                    'college' => [],
                    'lycee' => [],
                    'bts_dut' => [],
                    'fp_fs' => [],
                    'licences' => [],
                    'engineering_schools' => [],
                    'universities' => [],
                ],
                'international' => [
                    'degrees' => [],
                    'certifications' => [],
                    'platforms' => [],
                ],
            ],
            'success_predictions' => [],
            'action_plan' => [],
            'motivation_message' => 'Le système est actuellement en maintenance. Veuillez réessayer ultérieurement.',
        ];
    }
}
