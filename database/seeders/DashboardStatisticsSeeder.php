<?php

namespace Database\Seeders;

use App\Models\AcademicDiagnostic;
use App\Models\StudentProfile;
use App\Models\TestPersonnalise;
use App\Models\User;
use App\Services\Diagnostics\AcademicDiagnosticResultService;
use App\Services\TestPersonnalises\TestPersonnaliseResultService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DashboardStatisticsSeeder extends Seeder
{
    private const PASSWORD = 'password123';

    public function run(): void
    {
        Role::firstOrCreate(['name' => User::ROLE_STUDENT, 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => User::ROLE_TEACHER, 'guard_name' => 'web']);

        $this->seedTeachers();
        $this->seedStudents();

        $this->command?->info('Dashboard statistics demo data seeded successfully.');
        $this->command?->info('Demo student password: ' . self::PASSWORD);
    }

    private function seedTeachers(): void
    {
        $teachers = [
            ['name' => 'Nadia El Amrani', 'email' => 'demo.teacher.casablanca@orientationtech.ma', 'city' => 'Casablanca'],
            ['name' => 'Youssef Bennani', 'email' => 'demo.teacher.rabat@orientationtech.ma', 'city' => 'Rabat'],
            ['name' => 'Imane Tazi', 'email' => 'demo.teacher.fes@orientationtech.ma', 'city' => 'Fes'],
            ['name' => 'Omar Ait Lahcen', 'email' => 'demo.teacher.marrakech@orientationtech.ma', 'city' => 'Marrakech'],
        ];

        foreach ($teachers as $index => $teacherData) {
            $teacher = User::updateOrCreate(
                ['email' => $teacherData['email']],
                [
                    'name' => $teacherData['name'],
                    'first_name' => explode(' ', $teacherData['name'])[0],
                    'last_name' => trim(str_replace(explode(' ', $teacherData['name'])[0], '', $teacherData['name'])),
                    'password' => Hash::make(self::PASSWORD),
                    'city' => $teacherData['city'],
                    'country' => 'Maroc',
                    'job_title' => 'Conseiller orientation',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
            );

            $teacher->assignRole(User::ROLE_TEACHER);
            $this->setTimestamps($teacher, now()->subMonths(5 - $index)->subDays($index * 3));
        }
    }

    private function seedStudents(): void
    {
        $diagnosticService = app(AcademicDiagnosticResultService::class);
        $personalityService = app(TestPersonnaliseResultService::class);

        foreach ($this->studentScenarios() as $index => $scenario) {
            $createdAt = now()->subDays(170 - ($index * 7));
            $submittedAt = (clone $createdAt)->addDays(3 + ($index % 10));

            $user = User::updateOrCreate(
                ['email' => $scenario['email']],
                [
                    'name' => $scenario['name'],
                    'first_name' => explode(' ', $scenario['name'])[0],
                    'last_name' => trim(str_replace(explode(' ', $scenario['name'])[0], '', $scenario['name'])),
                    'password' => Hash::make(self::PASSWORD),
                    'city' => $scenario['city'],
                    'country' => 'Maroc',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
            );

            $user->assignRole(User::ROLE_STUDENT);
            $this->setTimestamps($user, $createdAt);

            StudentProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'education_level' => $scenario['education_level'],
                    'bac_type' => 'marocain',
                    'bac_field' => $scenario['bac_field'],
                    'school_name' => $scenario['school_name'],
                    'school_type' => $scenario['school_type'],
                    'preferred_school_types' => $scenario['preferred_school_types'],
                    'interested_services' => ['orientation_session', 'notifications', 'school_registration'],
                    'birth_date' => Carbon::parse($scenario['birth_date']),
                    'gender' => $scenario['gender'],
                    'city' => $scenario['city'],
                    'consent_contact' => true,
                    'is_complete' => $scenario['profile_complete'],
                ],
            );

            if (! $scenario['has_diagnostic']) {
                continue;
            }

            $diagnosticInput = [
                'user_id' => $user->id,
                'macro_cycle' => $scenario['macro_cycle'],
                'academic_level' => $scenario['education_level'],
                'interest_theme' => $scenario['interest_theme'],
                'track_branch' => $scenario['track_branch'],
                'institution_type' => $scenario['school_type'],
                'specialty_family' => $scenario['specialty_family'],
                'specialty_label' => $scenario['bac_field'],
                'biof_language' => $scenario['biof_language'],
                'remark' => $scenario['remark'],
                'diagnostic_answers' => $this->diagnosticAnswers($scenario),
            ];

            $diagnosticResult = $diagnosticService->calculate($diagnosticInput);

            AcademicDiagnostic::updateOrCreate(
                ['user_id' => $user->id],
                array_merge($diagnosticInput, $diagnosticResult, [
                    'status' => 'completed',
                    'submitted_at' => $submittedAt,
                ]),
            );

            if (! $scenario['has_personality']) {
                continue;
            }

            $answers = $this->personalityAnswers($scenario['profile']);
            $personalityResult = $personalityService->calculate($answers);

            TestPersonnalise::updateOrCreate(
                ['user_id' => $user->id],
                array_merge($personalityResult, [
                    'test_name' => 'TestPersonnalise',
                    'version' => '1.0',
                    'target_level' => $scenario['education_level'],
                    'status' => 'completed',
                    'answers' => $answers,
                    'submitted_at' => (clone $submittedAt)->addDays(2),
                ]),
            );
        }
    }

    private function setTimestamps(User $user, Carbon $createdAt): void
    {
        $user->forceFill([
            'created_at' => $createdAt,
            'updated_at' => (clone $createdAt)->addDays(2),
            'last_login_at' => now()->subDays(rand(0, 20)),
        ])->saveQuietly();
    }

    private function diagnosticAnswers(array $scenario): array
    {
        return [
            'preferred_subjects' => $scenario['subjects'],
            'interests' => $scenario['interests'],
            'favorite_activities' => $scenario['activities'],
            'future_ambitions' => $scenario['ambition'],
            'career_goals' => $scenario['career_goal'],
            'motivations' => $scenario['motivation'],
            'family_school_environment' => $scenario['environment'],
            'ikigai_love' => $scenario['ikigai_love'],
            'ikigai_good_at' => $scenario['ikigai_good_at'],
            'ikigai_world_needs' => $scenario['ikigai_world_needs'],
            'ikigai_profession' => $scenario['ikigai_profession'],
            'math_logic' => $scenario['math_logic'],
            'computer_ease' => $scenario['computer_ease'],
            'digital_ease' => $scenario['digital_ease'],
            'programming_interest' => $scenario['programming_interest'],
            'data_ai_interest' => $scenario['data_ai_interest'],
            'cyber_network_interest' => $scenario['cyber_network_interest'],
            'design_product_interest' => $scenario['design_product_interest'],
            'autonomy_project' => $scenario['autonomy_project'],
            'french_english_readiness' => $scenario['french_english_readiness'],
            'service_motivation' => $scenario['service_motivation'],
            'future_market_interest' => $scenario['future_market_interest'],
        ];
    }

    private function personalityAnswers(string $profile): array
    {
        $profiles = [
            'ai_data' => [
                'I1' => 3, 'I2' => 3, 'I3' => 5, 'I4' => 3, 'I5' => 4, 'I6' => 3,
                'A1' => 5, 'A2' => 4, 'A3' => 4, 'A4' => 5, 'A5' => 5, 'A6' => 5,
                'P1' => 4, 'P2' => 5, 'P3' => 4, 'P4' => 5, 'P5' => 4, 'P6' => 4,
                'C1' => 4, 'C3' => 3, 'C4' => 4, 'C5' => 4, 'C6' => 5,
                'L1' => 4, 'L2' => 4, 'L3' => 5, 'L4' => 5, 'L5' => 4, 'L6' => 4,
                'M2' => 5, 'IK4' => 5, 'F8' => 5, 'AP2' => 5, 'AN1' => 5, 'AN2' => 5, 'AN3' => 5, 'CU1' => 5, 'CU2' => 5, 'CU3' => 5,
            ],
            'cyber_network' => [
                'I1' => 3, 'I2' => 5, 'I3' => 4, 'I4' => 5, 'I5' => 4, 'I6' => 2,
                'A1' => 4, 'A2' => 5, 'A3' => 5, 'A4' => 4, 'A5' => 5, 'A6' => 4,
                'P1' => 4, 'P2' => 5, 'P3' => 4, 'P4' => 4, 'P5' => 3, 'P6' => 5,
                'C1' => 3, 'C3' => 3, 'C4' => 4, 'C5' => 4, 'C6' => 4,
                'L3' => 5, 'L4' => 5, 'M3' => 5, 'M4' => 4, 'IK4' => 4, 'F4' => 5, 'AP5' => 5, 'AU1' => 5, 'AU2' => 5, 'AU3' => 5, 'ST1' => 5, 'ST2' => 5, 'ST3' => 5,
            ],
            'web_mobile' => [
                'I1' => 5, 'I2' => 3, 'I3' => 4, 'I4' => 3, 'I5' => 4, 'I6' => 5,
                'A1' => 5, 'A2' => 4, 'A3' => 3, 'A4' => 4, 'A5' => 3, 'A6' => 4,
                'P1' => 4, 'P2' => 4, 'P3' => 5, 'P4' => 4, 'P5' => 4, 'P6' => 3,
                'C1' => 4, 'C3' => 4, 'C4' => 5, 'C5' => 4, 'C6' => 4,
                'L1' => 5, 'L2' => 4, 'L5' => 5, 'L6' => 4, 'M1' => 5, 'M6' => 5, 'IK3' => 5, 'F5' => 4, 'AP1' => 5, 'AP4' => 5,
            ],
            'ui_content' => [
                'I1' => 4, 'I2' => 2, 'I3' => 3, 'I4' => 2, 'I5' => 3, 'I6' => 5,
                'A1' => 3, 'A2' => 3, 'A3' => 2, 'A4' => 3, 'A5' => 3, 'A6' => 3,
                'P1' => 5, 'P2' => 3, 'P3' => 5, 'P4' => 4, 'P5' => 5, 'P6' => 4,
                'C1' => 5, 'C3' => 5, 'C4' => 5, 'C5' => 4, 'C6' => 4,
                'L1' => 4, 'L2' => 4, 'L5' => 5, 'L6' => 5, 'M6' => 5, 'IK3' => 5, 'IK5' => 5, 'F5' => 5, 'AP3' => 4, 'AP4' => 5, 'CR1' => 5, 'CR2' => 5, 'CR3' => 5,
            ],
            'business_edtech' => [
                'I1' => 3, 'I2' => 2, 'I3' => 3, 'I4' => 2, 'I5' => 3, 'I6' => 4,
                'A1' => 3, 'A2' => 3, 'A3' => 3, 'A4' => 4, 'A5' => 3, 'A6' => 4,
                'P1' => 4, 'P2' => 4, 'P3' => 5, 'P4' => 4, 'P5' => 5, 'P6' => 4,
                'C1' => 5, 'C3' => 5, 'C4' => 5, 'C5' => 5, 'C6' => 5,
                'L1' => 4, 'L2' => 4, 'L5' => 4, 'L6' => 5, 'IK1' => 5, 'IK2' => 5, 'IK5' => 5, 'IK6' => 5, 'F3' => 4, 'F7' => 5, 'AP3' => 5, 'LD1' => 5, 'LD2' => 5, 'LD3' => 5, 'CM1' => 5, 'CM2' => 5, 'CM3' => 5,
            ],
            'health_agritech' => [
                'I1' => 2, 'I2' => 3, 'I3' => 4, 'I4' => 3, 'I5' => 3, 'I6' => 3,
                'A1' => 4, 'A2' => 4, 'A3' => 4, 'A4' => 5, 'A5' => 4, 'A6' => 5,
                'P1' => 4, 'P2' => 5, 'P3' => 4, 'P4' => 5, 'P5' => 4, 'P6' => 4,
                'C1' => 4, 'C3' => 4, 'C4' => 4, 'C5' => 4, 'C6' => 5,
                'L3' => 4, 'L4' => 4, 'IK1' => 5, 'IK2' => 5, 'IK6' => 5, 'F1' => 5, 'F2' => 5, 'F6' => 4, 'AP2' => 4, 'CU1' => 4, 'CU2' => 4, 'CU3' => 4,
            ],
        ];

        $answers = array_fill_keys($this->questionIds(), 3);

        foreach ($profiles[$profile] ?? [] as $question => $score) {
            $answers[$question] = $score;
        }

        return $answers;
    }

    private function questionIds(): array
    {
        return [
            'I1', 'I2', 'I3', 'I4', 'I5', 'I6',
            'A1', 'A2', 'A3', 'A4', 'A5', 'A6',
            'P1', 'P2', 'P3', 'P4', 'P5', 'P6',
            'C1', 'C3', 'C4', 'C5', 'C6',
            'L1', 'L2', 'L3', 'L4', 'L5', 'L6',
            'M1', 'M2', 'M3', 'M4', 'M5', 'M6',
            'IK1', 'IK2', 'IK3', 'IK4', 'IK5', 'IK6', 'IK7', 'IK8',
            'F1', 'F2', 'F3', 'F4', 'F5', 'F6', 'F7', 'F8',
            'AP1', 'AP2', 'AP3', 'AP4', 'AP5', 'AP6',
            'CR1', 'CR2', 'CR3',
            'AN1', 'AN2', 'AN3',
            'LD1', 'LD2', 'LD3',
            'CM1', 'CM2', 'CM3',
            'AU1', 'AU2', 'AU3',
            'TW1', 'TW2', 'TW3',
            'AD1', 'AD2', 'AD3',
            'ST1', 'ST2', 'ST3',
            'CU1', 'CU2', 'CU3',
        ];
    }

    private function studentScenarios(): array
    {
        $schools = [
            ['Lycée Mohammed V', 'Casablanca', 'public'],
            ['Lycée Descartes', 'Rabat', 'private'],
            ['Lycée Moulay Idriss', 'Fes', 'public'],
            ['Lycée Ibn Tofail', 'Marrakech', 'public'],
            ['Lycée Abdelkrim El Khattabi', 'Tanger', 'public'],
            ['Lycée Ibn Rochd', 'Agadir', 'private'],
        ];

        $profiles = [
            ['profile' => 'ai_data', 'theme' => 'ai', 'field' => 'Sciences Mathématiques', 'subjects' => 'Mathématiques, physique, informatique', 'interest' => 'intelligence artificielle, data, robotique', 'goal' => 'Devenir ingénieur IA ou data scientist'],
            ['profile' => 'cyber_network', 'theme' => 'engineering', 'field' => 'Sciences Physiques', 'subjects' => 'Physique, mathématiques, technologie', 'interest' => 'cybersécurité, réseaux, cloud', 'goal' => 'Protéger les systèmes numériques des entreprises'],
            ['profile' => 'web_mobile', 'theme' => 'engineering', 'field' => 'Sciences Physiques', 'subjects' => 'Informatique, mathématiques, français', 'interest' => 'développement web, mobile, applications', 'goal' => 'Créer des applications utiles pour les élèves marocains'],
            ['profile' => 'ui_content', 'theme' => 'arts', 'field' => 'Lettres modernes', 'subjects' => 'Français, anglais, arts appliqués', 'interest' => 'design, contenu digital, expérience utilisateur', 'goal' => 'Concevoir des interfaces simples et inclusives'],
            ['profile' => 'business_edtech', 'theme' => 'business', 'field' => 'Sciences Économiques', 'subjects' => 'Économie, gestion, anglais', 'interest' => 'entrepreneuriat, edtech, marketing digital', 'goal' => 'Lancer un projet numérique éducatif'],
            ['profile' => 'health_agritech', 'theme' => 'agriculture', 'field' => 'Sciences de la Vie et de la Terre', 'subjects' => 'SVT, mathématiques, chimie', 'interest' => 'santé numérique, agriculture intelligente, environnement', 'goal' => 'Utiliser la technologie pour améliorer la santé ou l’agriculture'],
        ];

        $names = [
            'Aya El Mansouri', 'Adam Berrada', 'Salma Idrissi', 'Yassine Alaoui', 'Nour El Fassi', 'Mehdi Amrani',
            'Lina Tazi', 'Hamza Bennani', 'Meryem Chafik', 'Anas Zahraoui', 'Hiba El Ghazali', 'Oussama Rami',
            'Rania Ait Said', 'Ilyas Naciri', 'Sara Kadiri', 'Khalil Bennis', 'Maha Slaoui', 'Amine Lahlou',
            'Jihane Moutawakil', 'Younes Fadili', 'Imane Najjar', 'Omar Lazrak', 'Malak Oubaha', 'Reda Saidi',
            'Sofia El Alami', 'Bilal Gharbi', 'Ines Rahmani', 'Taha Lamrani', 'Dina Ziani', 'Karim Boussaid',
        ];

        $scenarios = [];

        foreach ($names as $index => $name) {
            $profile = $profiles[$index % count($profiles)];
            $school = $schools[$index % count($schools)];
            $hasDiagnostic = $index % 10 !== 8;
            $hasPersonality = $hasDiagnostic && $index % 10 !== 9;

            $scenarios[] = [
                'name' => $name,
                'email' => 'demo.student.' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) . '@orientationtech.ma',
                'city' => $school[1],
                'school_name' => $school[0],
                'school_type' => $school[2],
                'education_level' => $index % 3 === 0 ? 'Tronc Commun Scientifique' : ($index % 3 === 1 ? '1ère année Bac' : '2ème année Bac'),
                'bac_field' => $profile['field'],
                'birth_date' => now()->subYears(16 + ($index % 4))->subMonths($index % 12)->toDateString(),
                'gender' => $index % 2 === 0 ? 'feminine' : 'masculine',
                'preferred_school_types' => $index % 2 === 0 ? ['public', 'semi-public'] : ['public', 'private'],
                'profile_complete' => $index % 12 !== 7,
                'has_diagnostic' => $hasDiagnostic,
                'has_personality' => $hasPersonality,
                'macro_cycle' => 'lycée',
                'interest_theme' => $profile['theme'],
                'track_branch' => $profile['field'],
                'specialty_family' => 'Informatique et métiers du futur',
                'biof_language' => $index % 2 === 0 ? 'FR' : 'FR/EN',
                'remark' => 'Profil de démonstration généré pour enrichir les statistiques du dashboard.',
                'subjects' => $profile['subjects'],
                'interests' => $profile['interest'],
                'activities' => $this->activitiesFor($profile['profile']),
                'ambition' => 'Réussir son orientation et choisir une voie adaptée à son potentiel.',
                'career_goal' => $profile['goal'],
                'motivation' => 'Apprendre par projet, progresser et contribuer au développement numérique du Maroc.',
                'environment' => 'Famille encourageante, besoin de conseils clairs sur les parcours après le bac.',
                'ikigai_love' => $profile['interest'],
                'ikigai_good_at' => $profile['subjects'],
                'ikigai_world_needs' => 'Solutions numériques utiles, éducation, sécurité, santé, environnement et innovation.',
                'ikigai_profession' => $profile['goal'],
                'math_logic' => $this->scoreFor($profile['profile'], 'math'),
                'computer_ease' => $this->scoreFor($profile['profile'], 'digital'),
                'digital_ease' => $this->scoreFor($profile['profile'], 'digital'),
                'programming_interest' => $this->scoreFor($profile['profile'], 'programming'),
                'data_ai_interest' => $this->scoreFor($profile['profile'], 'data'),
                'cyber_network_interest' => $this->scoreFor($profile['profile'], 'cyber'),
                'design_product_interest' => $this->scoreFor($profile['profile'], 'design'),
                'autonomy_project' => 3 + ($index % 3),
                'french_english_readiness' => 3 + ($index % 3),
                'service_motivation' => 4,
                'future_market_interest' => 4 + ($index % 2),
                'profile' => $profile['profile'],
            ];
        }

        return $scenarios;
    }

    private function activitiesFor(string $profile): string
    {
        return match ($profile) {
            'ai_data' => 'Résoudre des problèmes, analyser des données, suivre des vidéos sur l’IA',
            'cyber_network' => 'Configurer des réseaux, comprendre la sécurité, tester des environnements numériques',
            'web_mobile' => 'Créer des sites, imaginer des applications, apprendre HTML CSS JavaScript',
            'ui_content' => 'Dessiner des interfaces, créer du contenu, améliorer l’expérience utilisateur',
            'business_edtech' => 'Organiser des projets, communiquer, imaginer des services éducatifs',
            'health_agritech' => 'Observer les sciences, chercher des solutions santé et environnement',
            default => 'Apprendre, expérimenter et réaliser des projets',
        };
    }

    private function scoreFor(string $profile, string $skill): int
    {
        $scores = [
            'ai_data' => ['math' => 5, 'digital' => 5, 'programming' => 4, 'data' => 5, 'cyber' => 3, 'design' => 3],
            'cyber_network' => ['math' => 4, 'digital' => 5, 'programming' => 4, 'data' => 3, 'cyber' => 5, 'design' => 2],
            'web_mobile' => ['math' => 4, 'digital' => 5, 'programming' => 5, 'data' => 3, 'cyber' => 3, 'design' => 4],
            'ui_content' => ['math' => 3, 'digital' => 4, 'programming' => 3, 'data' => 2, 'cyber' => 2, 'design' => 5],
            'business_edtech' => ['math' => 3, 'digital' => 4, 'programming' => 3, 'data' => 3, 'cyber' => 2, 'design' => 4],
            'health_agritech' => ['math' => 4, 'digital' => 4, 'programming' => 3, 'data' => 4, 'cyber' => 2, 'design' => 3],
        ];

        return $scores[$profile][$skill] ?? 3;
    }
}
