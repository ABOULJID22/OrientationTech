<?php

namespace App\Services\Diagnostics;

class AcademicDiagnosticResultService
{
    public function calculate(array $data): array
    {
        $resultCode = $this->determineResultCode($data);
        $resultLabel = $this->getResultLabel($resultCode);
        $resultSummary = $this->generateSummary($data, $resultCode);
        $resultPayload = $this->buildPayload($data, $resultCode);

        return [
            'result_code' => $resultCode,
            'result_label' => $resultLabel,
            'result_summary' => $resultSummary,
            'result_payload' => $resultPayload,
        ];
    }

    private function determineResultCode(array $data): string
    {
        $level = $data['academic_level'] ?? '';
        $cycle = $data['macro_cycle'] ?? '';
        $interestTheme = $data['interest_theme'] ?? '';
        $answers = $data['diagnostic_answers'] ?? [];
        $remark = $this->normalizeText((string) ($data['remark'] ?? ''));

        $profileText = $this->normalizeText(implode(' ', array_filter([
            $answers['preferred_subjects'] ?? '',
            $answers['interests'] ?? '',
            $answers['favorite_activities'] ?? '',
            $answers['future_ambitions'] ?? '',
            $answers['career_goals'] ?? '',
            $answers['motivations'] ?? '',
            $answers['ikigai_love'] ?? '',
            $answers['ikigai_good_at'] ?? '',
            $answers['ikigai_world_needs'] ?? '',
            $answers['ikigai_profession'] ?? '',
        ])));

        if (
            $this->answerScore($answers, 'programming_interest') >= 4 ||
            $this->answerScore($answers, 'data_ai_interest') >= 4 ||
            str_contains($profileText, 'informatique') ||
            str_contains($profileText, 'program') ||
            str_contains($profileText, 'ia') ||
            str_contains($profileText, 'data') ||
            str_contains($profileText, 'developp')
        ) {
            return 'TOP_ENGINEERING';
        }

        if (
            $this->answerScore($answers, 'cyber_network_interest') >= 4 ||
            $this->answerScore($answers, 'computer_ease') >= 4 ||
            $this->answerScore($answers, 'digital_ease') >= 4 ||
            str_contains($profileText, 'cyber') ||
            str_contains($profileText, 'reseau') ||
            str_contains($profileText, 'technolog')
        ) {
            return 'ENGINEERING';
        }

        if ($interestTheme === 'medicine') {
            return 'MEDICINE';
        }

        if ($interestTheme === 'ai') {
            return 'TOP_ENGINEERING';
        }

        if ($interestTheme === 'engineering') {
            return 'ENGINEERING';
        }

        if ($interestTheme === 'business') {
            return 'BUSINESS_MANAGEMENT';
        }

        if ($interestTheme === 'law') {
            return 'HIGHER_EDUCATION';
        }

        if ($interestTheme === 'education') {
            return 'GENERAL_STUDIES';
        }

        if ($interestTheme === 'arts') {
            return 'GENERAL_STUDIES';
        }

        if ($interestTheme === 'agriculture') {
            return 'HEALTH_SCIENCES';
        }

        if (
            str_contains($remark, 'medecine') ||
            str_contains($remark, 'medicin') ||
            str_contains($remark, 'medecin') ||
            str_contains($remark, 'medicine')
        ) {
            return 'MEDICINE';
        }

        if (str_contains($remark, 'ia') || str_contains($remark, 'intelligence artificielle')) {
            return 'TOP_ENGINEERING';
        }

        if ($this->isCollegeCycle($cycle)) {
            return 'GENERAL_GUIDANCE';
        }

        // Sciences Mathématiques → Ingénierie / Data Science / IA
        if (str_contains($level, 'Sciences Mathématiques')) {
            return 'TOP_ENGINEERING';
        }

        // Sciences Physiques → Ingénierie / Tech
        if (str_contains($level, 'Sciences Physiques')) {
            return 'ENGINEERING';
        }

        // Sciences de la Vie → Médecine / Santé / Agriculture
        if (str_contains($level, 'Sciences de la Vie')) {
            return 'MEDICINE';
        }

        // Économie et Gestion → Business / Management / BI
        if (str_contains($level, 'Économie')) {
            return 'BUSINESS_MANAGEMENT';
        }

        // Lettres → Éducation / Communication / Droit
        if (str_contains($level, 'Lettres')) {
            return 'HIGHER_EDUCATION';
        }

        // Technique → Technologie / Industrial
        if (str_contains($level, 'Technique')) {
            return 'TECHNICAL_SPECIALIZATION';
        }

        return 'GENERAL_GUIDANCE';
    }

    private function getResultLabel(string $code): string
    {
        $labels = [
            'TOP_ENGINEERING' => 'Ingénierie Avancée',
            'ENGINEERING' => 'Ingénierie',
            'TECHNICAL_STUDIES' => 'Études Techniques',
            'MEDICINE' => 'Études Médicales',
            'HEALTH_SCIENCES' => 'Sciences de la Santé',
            'BUSINESS_MANAGEMENT' => 'Gestion & Business',
            'COMMERCE' => 'Commerce & Trade',
            'HIGHER_EDUCATION' => 'Études Supérieures',
            'GENERAL_STUDIES' => 'Études Générales',
            'TECHNICAL_SPECIALIZATION' => 'Spécialisation Technique',
            'GENERAL_GUIDANCE' => 'Orientation Générale',
        ];

        return $labels[$code] ?? 'Orientation à Définir';
    }

    private function generateSummary(array $data, string $code): string
    {
        $level = $data['academic_level'] ?? 'Non spécifié';
        $cycle = $data['macro_cycle'] ?? 'Non spécifié';
        $interestTheme = $data['interest_theme'] ?? 'Non renseigné';
        $remark = trim((string) ($data['remark'] ?? ''));
        $remarkPart = $remark !== '' ? " | Remarque: $remark" : '';

        return "Profil: $level | Cycle: $cycle | Intérêt: $interestTheme{$remarkPart} | Orientation: {$this->getResultLabel($code)}";
    }

    private function buildPayload(array $data, string $code): array
    {
        return [
            'orientation_domains' => $this->getOrientationDomains($code),
            'recommended_schools' => $this->getRecommendedSchools($code),
            'skills_match' => $this->getSkillsMatch($data),
            'diagnostic_answers' => $data['diagnostic_answers'] ?? [],
        ];
    }

    private function getOrientationDomains(string $code): array
    {
        $domains = [
            'TOP_ENGINEERING' => ['Ingénierie Informatique', 'Data Science', 'IA', 'Cybersécurité'],
            'ENGINEERING' => ['Ingénierie', 'Technologie', 'Informatique'],
            'TECHNICAL_STUDIES' => ['Technologie', 'Maintenance', 'Industrie'],
            'MEDICINE' => ['Médecine', 'Pharmacie', 'Dentisterie'],
            'HEALTH_SCIENCES' => ['Soins Infirmiers', 'Santé Publique'],
            'BUSINESS_MANAGEMENT' => ['Management', 'Business Intelligence', 'Gestion'],
            'COMMERCE' => ['Commerce', 'Vente', 'Marketing'],
            'HIGHER_EDUCATION' => ['Littérature', 'Droit', 'Communication'],
            'GENERAL_STUDIES' => ['Langues', 'Lettres', 'Études Générales'],
            'TECHNICAL_SPECIALIZATION' => ['Spécialisations Techniques'],
            'GENERAL_GUIDANCE' => ['À explorer davantage'],
        ];

        return $domains[$code] ?? [];
    }

    private function getRecommendedSchools(string $code): array
    {
        // À compléter avec la vraie liste d'écoles/universités
        return [
            'Écoles Recommandées' => ['École A', 'École B'],
            'Universités' => ['Université X', 'Université Y'],
        ]; 
    }

    private function getSkillsMatch(array $data): array
    {
        return [
            'niveau_académique' => $data['academic_level'] ?? 'Non renseigné',
            'remarque' => $data['remark'] ?? 'Aucune remarque',
            'langue_instruction' => $data['biof_language'] ?? 'FR',
            'matieres_preferees' => $data['diagnostic_answers']['preferred_subjects'] ?? '',
            'centres_interet' => $data['diagnostic_answers']['interests'] ?? '',
            'activites_favorites' => $data['diagnostic_answers']['favorite_activities'] ?? '',
            'ambitions_futures' => $data['diagnostic_answers']['future_ambitions'] ?? '',
            'objectifs_professionnels' => $data['diagnostic_answers']['career_goals'] ?? '',
            'motivations' => $data['diagnostic_answers']['motivations'] ?? '',
            'environnement_familial_scolaire' => $data['diagnostic_answers']['family_school_environment'] ?? '',
            'ikigai_aime' => $data['diagnostic_answers']['ikigai_love'] ?? '',
            'ikigai_excelle' => $data['diagnostic_answers']['ikigai_good_at'] ?? '',
            'ikigai_monde_besoin' => $data['diagnostic_answers']['ikigai_world_needs'] ?? '',
            'ikigai_profession' => $data['diagnostic_answers']['ikigai_profession'] ?? '',
            'mathematiques_logique' => $this->answerScore($data['diagnostic_answers'] ?? [], 'math_logic'),
            'aisance_numerique' => max(
                $this->answerScore($data['diagnostic_answers'] ?? [], 'computer_ease'),
                $this->answerScore($data['diagnostic_answers'] ?? [], 'digital_ease')
            ),
            'interet_programmation' => $this->answerScore($data['diagnostic_answers'] ?? [], 'programming_interest'),
            'interet_data_ia' => $this->answerScore($data['diagnostic_answers'] ?? [], 'data_ai_interest'),
            'interet_cyber_reseaux' => $this->answerScore($data['diagnostic_answers'] ?? [], 'cyber_network_interest'),
            'interet_design_produit' => $this->answerScore($data['diagnostic_answers'] ?? [], 'design_product_interest'),
            'autonomie_projet' => $this->answerScore($data['diagnostic_answers'] ?? [], 'autonomy_project'),
            'readiness_fr_en' => $this->answerScore($data['diagnostic_answers'] ?? [], 'french_english_readiness'),
            'motivation_impact' => $this->answerScore($data['diagnostic_answers'] ?? [], 'service_motivation'),
            'interet_marche_futur' => $this->answerScore($data['diagnostic_answers'] ?? [], 'future_market_interest'),
        ];
    }

    private function answerScore(array $answers, string $key): int
    {
        return (int) ($answers[$key] ?? 0);
    }

    private function normalizeText(string $value): string
    {
        return strtr(mb_strtolower($value), [
            'é' => 'e',
            'è' => 'e',
            'ê' => 'e',
            'à' => 'a',
            'ù' => 'u',
            'ç' => 'c',
        ]);
    }

    private function isCollegeCycle(string $cycle): bool
    {
        return str_contains($cycle, 'collège');
    }
}
