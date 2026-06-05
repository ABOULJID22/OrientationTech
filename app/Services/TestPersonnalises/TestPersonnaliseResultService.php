<?php

namespace App\Services\TestPersonnalises;

use Illuminate\Support\Arr;

class TestPersonnaliseResultService
{
    private const IT_DOMAINS = [
        'development_web' => 'Developpement web et logiciel',
        'data_ia' => 'Data / IA',
        'intelligence_artificielle' => 'Intelligence artificielle',
        'cybersecurite' => 'Cybersecurite',
        'cloud_infrastructure' => 'Cloud / Infrastructure',
        'ui_ux' => 'UI / UX',
        'qa_test' => 'QA / Test logiciel',
        'mobile' => 'Developpement mobile',
        'systemes_reseaux' => 'Systemes / Reseaux',
        'bases_donnees' => 'Bases de donnees',
        'langages_paradigmes' => 'Langages et paradigmes',
        'management_it' => 'Management IT',
        'erp_si' => 'ERP et systemes d information',
        'ecommerce_marketing' => 'E-commerce et marketing digital',
        'fintech_numerique' => 'FinTech et numerique',
        'edtech' => 'EdTech',
        'informatique_scientifique' => 'Informatique scientifique',
        'sante_numerique' => 'Sante numerique / Bioinformatique',
        'agritech_green' => 'Agriculture intelligente / Green IT',
        'robotique_industrie4' => 'Robotique et Industrie 4.0',
        'technologies_emergentes' => 'Technologies emergentes',
        'transformation_digitale' => 'Transformation digitale',
        'creation_contenu' => 'Creation de contenu numerique',
        'geomatique_smart_city' => 'Geomatique et villes intelligentes',
    ];

    public static function domainLabel(string $domain): string
    {
        return self::IT_DOMAINS[$domain] ?? $domain;
    }

    public static function domainLabels(): array
    {
        return self::IT_DOMAINS;
    }

    public function calculate(array $answers): array
    {
        $axisScores = $this->calculateAxisScores($answers);
        $domainScores = $this->calculateDomainScores($answers);
        arsort($domainScores);

        $topDomains = array_slice(array_keys($domainScores), 0, 3);

        return [
            'axis_scores' => $axisScores,
            'domain_scores' => $domainScores,
            'primary_domain' => $topDomains[0] ?? null,
            'secondary_domain' => $topDomains[1] ?? null,
            'result_summary' => $this->buildSummary($axisScores, $domainScores),
            'result_payload' => [
                'top_domains' => $topDomains,
                'top_domain_labels' => array_map(
                    fn (string $key): string => self::IT_DOMAINS[$key] ?? $key,
                    $topDomains,
                ),
                'axis_scores' => $axisScores,
                'domain_scores' => $domainScores,
            ],
        ];
    }

    private function calculateAxisScores(array $answers): array
    {
        $definitions = TestPersonnaliseQuestionnaire::definition();
        $axisScores = [];

        foreach ($definitions['axes'] ?? [] as $axis) {
            $questionIds = array_map(fn (array $question): string => $question['id'], $axis['questions'] ?? []);
            $values = [];

            foreach ($questionIds as $questionId) {
                $value = (int) Arr::get($answers, $questionId, 0);
                if ($value > 0) {
                    $values[] = $value;
                }
            }

            $average = count($values) > 0 ? array_sum($values) / count($values) : 0;
            $axisScores[$axis['key']] = round(($average / 5) * 100, 2);
        }

        return $axisScores;
    }

    private function calculateDomainScores(array $answers): array
    {
        $scores = array_fill_keys(array_keys(self::IT_DOMAINS), 0.0);
        $maxScores = array_fill_keys(array_keys(self::IT_DOMAINS), 0.0);

        $add = function (array $domains, int $score, float $multiplier = 1.0) use (&$scores, &$maxScores): void {
            foreach ($domains as $domain) {
                $scores[$domain] = ($scores[$domain] ?? 0) + ($score * $multiplier);
                $maxScores[$domain] = ($maxScores[$domain] ?? 0) + (5 * $multiplier);
            }
        };

        foreach ($answers as $questionId => $score) {
            $score = (int) $score;

            match ($questionId) {
                'I1' => $add(['development_web', 'ui_ux', 'mobile'], $score, 2),
                'I2' => $add(['systemes_reseaux', 'cloud_infrastructure', 'cybersecurite'], $score, 2),
                'I3' => $add(['data_ia', 'qa_test', 'development_web'], $score, 2),
                'I4' => $add(['cybersecurite', 'cloud_infrastructure'], $score, 2.2),
                'I5' => $add(['qa_test', 'development_web'], $score, 2.2),
                'I6' => $add(['ui_ux', 'development_web'], $score, 2),
                'A1' => $add(['development_web', 'data_ia', 'qa_test'], $score, 1.6),
                'A2' => $add(['qa_test', 'development_web', 'cybersecurite'], $score, 1.8),
                'A3' => $add(['systemes_reseaux', 'cloud_infrastructure', 'qa_test'], $score, 1.4),
                'A4' => $add(['data_ia', 'development_web'], $score, 1.8),
                'A5' => $add(['systemes_reseaux', 'cloud_infrastructure', 'data_ia'], $score, 1.2),
                'A6' => $add(['data_ia', 'qa_test', 'cloud_infrastructure'], $score, 1.5),
                'P1', 'P3', 'P5' => $add(array_keys(self::IT_DOMAINS), $score, 0.2),
                'P2' => $add(['cybersecurite', 'qa_test', 'development_web'], $score, 0.15),
                'P4', 'P6' => $add(array_keys(self::IT_DOMAINS), $score, 0.18),
                'C1', 'C3', 'C4', 'C5', 'C6' => $add(array_keys(self::IT_DOMAINS), $score, 0.15),
                'L1', 'L2', 'L5', 'L6' => $add(['development_web', 'data_ia', 'qa_test'], $score, 0.12),
                'L3', 'L4' => $add(['data_ia', 'cybersecurite', 'cloud_infrastructure'], $score, 0.12),
                'M1' => $add(['development_web', 'mobile', 'ui_ux'], $score, 2.4),
                'M2' => $add(['data_ia', 'development_web'], $score, 2.4),
                'M3' => $add(['cybersecurite', 'systemes_reseaux', 'cloud_infrastructure'], $score, 2.4),
                'M4' => $add(['cloud_infrastructure', 'systemes_reseaux', 'development_web'], $score, 2.2),
                'M5' => $add(['qa_test', 'development_web', 'cybersecurite'], $score, 2.2),
                'M6' => $add(['ui_ux', 'development_web', 'mobile'], $score, 2.2),
                'IK1' => $add(['edtech', 'sante_numerique', 'agritech_green', 'transformation_digitale'], $score, 1.8),
                'IK2' => $add(['edtech', 'sante_numerique', 'transformation_digitale', 'management_it'], $score, 1.6),
                'IK3' => $add(['development_web', 'ui_ux', 'ecommerce_marketing', 'creation_contenu'], $score, 1.8),
                'IK4' => $add(['intelligence_artificielle', 'data_ia', 'technologies_emergentes', 'cloud_infrastructure'], $score, 1.8),
                'IK5' => $add(['edtech', 'management_it', 'ui_ux', 'creation_contenu'], $score, 1.6),
                'IK6' => $add(['transformation_digitale', 'edtech', 'fintech_numerique', 'agritech_green'], $score, 1.8),
                'IK7', 'IK8' => $add(array_keys(self::IT_DOMAINS), $score, 0.12),
                'F1' => $add(['sante_numerique', 'informatique_scientifique', 'data_ia', 'intelligence_artificielle'], $score, 2.2),
                'F2' => $add(['agritech_green', 'robotique_industrie4', 'data_ia', 'geomatique_smart_city'], $score, 2.2),
                'F3' => $add(['fintech_numerique', 'ecommerce_marketing', 'data_ia', 'erp_si'], $score, 2.2),
                'F4' => $add(['robotique_industrie4', 'systemes_reseaux', 'cloud_infrastructure', 'technologies_emergentes'], $score, 2.2),
                'F5' => $add(['creation_contenu', 'ecommerce_marketing', 'ui_ux', 'development_web'], $score, 2.2),
                'F6' => $add(['geomatique_smart_city', 'data_ia', 'cloud_infrastructure', 'transformation_digitale'], $score, 2.2),
                'F7' => $add(['transformation_digitale', 'erp_si', 'management_it', 'cloud_infrastructure'], $score, 2.2),
                'F8' => $add(['technologies_emergentes', 'intelligence_artificielle', 'development_web', 'data_ia'], $score, 2.2),
                'AP1' => $add(['development_web', 'langages_paradigmes', 'cloud_infrastructure'], $score, 1.7),
                'AP2' => $add(['data_ia', 'intelligence_artificielle', 'informatique_scientifique', 'bases_donnees'], $score, 1.8),
                'AP3' => $add(['management_it', 'edtech', 'ui_ux', 'transformation_digitale'], $score, 1.5),
                'AP4' => $add(['development_web', 'mobile', 'ui_ux', 'creation_contenu'], $score, 1.7),
                'AP5' => $add(['management_it', 'cloud_infrastructure', 'cybersecurite', 'data_ia'], $score, 1.5),
                'AP6' => $add(['development_web', 'intelligence_artificielle', 'cybersecurite', 'robotique_industrie4'], $score, 1.7),
                'CR1', 'CR2', 'CR3' => $add(['ui_ux', 'creation_contenu', 'technologies_emergentes'], $score, 1.8),
                'AN1', 'AN2', 'AN3' => $add(['data_ia', 'intelligence_artificielle', 'qa_test', 'bases_donnees'], $score, 1.9),
                'LD1', 'LD2', 'LD3' => $add(['management_it', 'transformation_digitale', 'erp_si', 'ecommerce_marketing'], $score, 1.7),
                'CM1', 'CM2', 'CM3' => $add(['edtech', 'management_it', 'ecommerce_marketing', 'transformation_digitale'], $score, 1.5),
                'AU1', 'AU2', 'AU3' => $add(['cloud_infrastructure', 'cybersecurite', 'langages_paradigmes', 'bases_donnees'], $score, 1.5),
                'TW1', 'TW2', 'TW3' => $add(['management_it', 'erp_si', 'transformation_digitale', 'edtech'], $score, 1.4),
                'AD1', 'AD2', 'AD3' => $add(['technologies_emergentes', 'cloud_infrastructure', 'robotique_industrie4', 'transformation_digitale'], $score, 1.6),
                'ST1', 'ST2', 'ST3' => $add(['cybersecurite', 'qa_test', 'systemes_reseaux', 'cloud_infrastructure'], $score, 1.4),
                'CU1', 'CU2', 'CU3' => $add(['intelligence_artificielle', 'data_ia', 'technologies_emergentes', 'informatique_scientifique'], $score, 1.8),
                default => null,
            };
        }

        foreach ($scores as $domain => $score) {
            $maxScore = $maxScores[$domain] ?? 0;
            $scores[$domain] = $maxScore > 0
                ? round(min(100, ($score / $maxScore) * 100), 2)
                : round(min(100, $score), 2);
        }

        return $scores;
    }

    private function buildSummary(array $axisScores, array $domainScores): string
    {
        $topAxis = array_keys($axisScores, max($axisScores), true)[0] ?? null;
        $topDomain = array_keys($domainScores, max($domainScores), true)[0] ?? null;

        $axisLabel = $topAxis ? TestPersonnaliseQuestionnaire::axisLabel($topAxis) : 'profil global';
        $domainLabel = $topDomain ? (self::IT_DOMAINS[$topDomain] ?? $topDomain) : 'IT';

        return "Le profil de personnalité met surtout en avant {$axisLabel}, avec une correspondance numérique principale vers {$domainLabel}. Ce résultat sera fusionné avec le diagnostic initial et l'Ikigai.";
    }
}
