<?php

namespace App\Services\Domains;

use App\Models\Domain;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DomainCsvImportService
{
    public function import(string $path): int
    {
        if (! is_file($path)) {
            return 0;
        }

        $handle = fopen($path, 'rb');
        if (! $handle) {
            return 0;
        }

        $headers = fgetcsv($handle);
        $count = 0;

        DB::transaction(function () use ($handle, $headers, &$count): void {
            while (($row = fgetcsv($handle)) !== false) {
                $data = array_combine($headers, $row);
                if (! $data || ! filled($data['nom_domaine'] ?? null)) {
                    continue;
                }

                $slug = $data['slug'] ?: Str::slug($data['nom_domaine']);

                Domain::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => $data['nom_domaine'],
                        'slug' => $slug,
                        'category' => $data['categorie'] ?? null,
                        'short_description' => $data['resume_eleve'] ?? null,
                        'full_description' => $data['description_complete'] ?? null,
                        'why_important' => $data['pourquoi_c_est_important'] ?? null,
                        'student_profile' => $this->split($data['profil_eleve_adapte'] ?? null),
                        'school_subjects' => $this->split($data['matieres_utiles'] ?? null),
                        'technical_skills' => $this->split($data['competences_techniques'] ?? null),
                        'soft_skills' => $this->split($data['competences_personnelles'] ?? null),
                        'tools' => $this->split($data['outils_technologies'] ?? null),
                        'related_jobs' => $this->split($data['metiers_associes'] ?? null),
                        'learning_path' => $this->split($data['parcours_apprentissage'] ?? null),
                        'schools_morocco' => $this->split($data['ecoles_formations_maroc'] ?? null),
                        'certifications' => $this->split($data['certifications_recommandees'] ?? null),
                        'global_demand' => $data['demande_mondiale'] ?? null,
                        'morocco_demand' => $data['demande_maroc'] ?? null,
                        'difficulty_level' => $data['niveau_difficulte'] ?? null,
                        'future_potential' => $data['potentiel_futur'] ?? null,
                        'ai_impact' => $data['impact_ia'] ?? null,
                        'freelance_opportunity' => (int) ($data['opportunite_freelance_1_5'] ?? 0),
                        'remote_opportunity' => (int) ($data['opportunite_remote_1_5'] ?? 0),
                        'math_score' => (int) ($data['score_math_1_5'] ?? 0),
                        'creativity_score' => (int) ($data['score_creativite_1_5'] ?? 0),
                        'communication_score' => (int) ($data['score_communication_1_5'] ?? 0),
                        'problem_solving_score' => (int) ($data['score_resolution_probleme_1_5'] ?? 0),
                        'junior_salary_min' => $this->int($data['salaire_maroc_junior_min_mad'] ?? null),
                        'junior_salary_max' => $this->int($data['salaire_maroc_junior_max_mad'] ?? null),
                        'senior_salary_min' => $this->int($data['salaire_maroc_senior_min_mad'] ?? null),
                        'senior_salary_max' => $this->int($data['salaire_maroc_senior_max_mad'] ?? null),
                        'salary_note' => $data['note_salaire'] ?? null,
                        'advantages' => $this->split($data['avantages'] ?? null),
                        'challenges' => $this->split($data['difficultes_possibles'] ?? null),
                        'start_tips' => $data['conseils_pour_commencer'] ?? null,
                        'practical_projects' => $this->split($data['projets_pratiques'] ?? null),
                        'keywords' => $data['mots_cles_recherche'] ?? null,
                        'display_order' => (int) ($data['ordre_affichage'] ?? 0),
                        'is_active' => mb_strtolower((string) ($data['statut'] ?? 'Publié')) !== 'brouillon',
                    ]
                );

                $count++;
            }
        });

        fclose($handle);

        return $count;
    }

    private function split(?string $value): array
    {
        return collect(explode('|', (string) $value))
            ->map(fn (string $item): string => trim($item))
            ->filter()
            ->values()
            ->all();
    }

    private function int(?string $value): ?int
    {
        if (! filled($value)) {
            return null;
        }

        return (int) preg_replace('/[^0-9]/', '', $value);
    }
}
