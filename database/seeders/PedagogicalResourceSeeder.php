<?php

namespace Database\Seeders;

use App\Models\ResourceContent;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class PedagogicalResourceSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            Role::firstOrCreate(['name' => User::ROLE_TEACHER, 'guard_name' => 'web']);

            $teacher = User::updateOrCreate(
                ['email' => 'ressources.prof@orientationtech.ma'],
                [
                    'name' => 'Prof Ressources Orientation',
                    'password' => bcrypt('password123'),
                    'user_type' => User::ROLE_TEACHER,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
            $teacher->syncRoles([User::ROLE_TEACHER]);

            $resources = [
                [
                    'type' => ResourceContent::TYPE_GUIDE,
                    'title' => 'Guide pratique pour decouvrir son Ikigai numerique',
                    'summary' => 'Une fiche simple pour relier passions, forces, besoins du monde et metiers possibles.',
                    'domain_key' => 'Orientation',
                    'career_name' => null,
                    'is_featured' => true,
                ],
                [
                    'type' => ResourceContent::TYPE_PDF,
                    'title' => 'Fiche domaine : Intelligence Artificielle',
                    'summary' => 'Competences, matieres utiles, metiers associes et parcours de formation.',
                    'domain_key' => 'Intelligence Artificielle',
                    'career_name' => 'Ingenieur IA',
                    'is_featured' => true,
                ],
                [
                    'type' => ResourceContent::TYPE_VIDEO,
                    'title' => 'Video : comprendre la cybersecurite en 10 minutes',
                    'summary' => 'Introduction claire aux missions cyber, aux risques et aux premieres competences.',
                    'domain_key' => 'Cybersecurite',
                    'career_name' => 'Analyste SOC',
                    'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                    'is_featured' => false,
                ],
                [
                    'type' => ResourceContent::TYPE_CAREER,
                    'title' => 'Metier : Data Analyst',
                    'summary' => 'Role, journee type, outils, competences et evolution professionnelle.',
                    'domain_key' => 'Data Science',
                    'career_name' => 'Data Analyst',
                    'is_featured' => true,
                ],
                [
                    'type' => ResourceContent::TYPE_DOMAIN,
                    'title' => 'Domaine : Developpement Web',
                    'summary' => 'Frontend, backend, bases de donnees, Laravel et projets pour debuter.',
                    'domain_key' => 'Developpement Web',
                    'career_name' => 'Developpeur Web',
                    'is_featured' => false,
                ],
                [
                    'type' => ResourceContent::TYPE_NEWS,
                    'title' => 'Actualite : pourquoi les competences IA deviennent essentielles',
                    'summary' => 'Un court contenu pour comprendre les tendances du marche numerique.',
                    'domain_key' => 'Actualite numerique',
                    'career_name' => null,
                    'is_featured' => false,
                ],
            ];

            foreach ($resources as $index => $row) {
                ResourceContent::updateOrCreate(
                    ['slug' => Str::slug($row['title'])],
                    [
                        'teacher_id' => $teacher->id,
                        'type' => $row['type'],
                        'title' => $row['title'],
                        'slug' => Str::slug($row['title']),
                        'summary' => $row['summary'],
                        'content' => $this->content($row['title'], $row['summary']),
                        'cover_image' => null,
                        'file_path' => null,
                        'video_url' => $row['video_url'] ?? null,
                        'domain_key' => $row['domain_key'],
                        'career_name' => $row['career_name'],
                        'status' => ResourceContent::STATUS_PUBLISHED,
                        'is_featured' => $row['is_featured'],
                        'published_at' => now()->subDays($index + 1),
                    ]
                );
            }
        });
    }

    private function content(string $title, string $summary): string
    {
        return <<<HTML
<h2>{$title}</h2>
<p>{$summary}</p>
<p>Cette ressource aide l eleve a comprendre les competences, les parcours et les opportunites liees aux metiers du numerique.</p>
<ul>
<li>Competences a developper progressivement.</li>
<li>Exemples de projets simples pour tester son interet.</li>
<li>Pistes de formation au Maroc et a l international.</li>
</ul>
HTML;
    }
}
