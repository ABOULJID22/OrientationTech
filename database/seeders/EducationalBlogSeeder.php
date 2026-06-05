<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostCommentReport;
use App\Models\PostLike;
use App\Models\PostView;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class EducationalBlogSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            foreach ([User::ROLE_SUPER_ADMIN, User::ROLE_TEACHER, User::ROLE_STUDENT, User::ROLE_USER] as $role) {
                Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
            }

            $admin = $this->user('admin.blog@orientationtech.ma', 'Admin Blog', User::ROLE_SUPER_ADMIN);
            $teachers = [
                $this->user('prof.ai@orientationtech.ma', 'Prof IA et Data', User::ROLE_TEACHER),
                $this->user('prof.cyber@orientationtech.ma', 'Prof Cybersecurite', User::ROLE_TEACHER),
            ];
            $students = [
                $this->user('eleve.yassine@orientationtech.ma', 'Yassine Eleve', User::ROLE_STUDENT),
                $this->user('eleve.salma@orientationtech.ma', 'Salma Eleve', User::ROLE_STUDENT),
                $this->user('eleve.amine@orientationtech.ma', 'Amine Eleve', User::ROLE_STUDENT),
                $this->user('user.parent@orientationtech.ma', 'Parent Test', User::ROLE_USER),
            ];

            $categories = collect([
                ['name' => 'Orientation scolaire', 'description' => 'Conseils pour choisir une filiere et construire un projet.'],
                ['name' => 'Metiers du futur', 'description' => 'Decouverte des metiers numeriques porteurs.'],
                ['name' => 'Intelligence artificielle', 'description' => 'Guides simples autour de l IA, data et automatisation.'],
                ['name' => 'Cybersecurite', 'description' => 'Ressources pour comprendre la securite numerique.'],
                ['name' => 'Parcours de formation', 'description' => 'Formations au Maroc et a l international.'],
                ['name' => 'Motivation et methodes', 'description' => 'Developpement personnel, organisation et confiance.'],
            ])->mapWithKeys(fn (array $row): array => [
                $row['name'] => Category::updateOrCreate(
                    ['slug' => Str::slug($row['name'])],
                    $row + ['slug' => Str::slug($row['name'])]
                ),
            ]);

            $tags = collect([
                'orientation',
                'ikigai',
                'ia',
                'data-science',
                'metiers-du-futur',
                'cybersecurite',
                'developpement-web',
                'maroc',
                'bts',
                'dut',
                'licence',
                'soft-skills',
            ])->mapWithKeys(fn (string $name): array => [
                $name => Tag::updateOrCreate(
                    ['slug' => Str::slug($name)],
                    ['name' => Str::headline($name), 'slug' => Str::slug($name)]
                ),
            ]);

            $postsData = [
                [
                    'title' => 'Comment choisir une filiere informatique apres le college',
                    'category' => 'Orientation scolaire',
                    'author' => $teachers[0],
                    'status' => Post::STATUS_PUBLISHED,
                    'tags' => ['orientation', 'maroc', 'ikigai'],
                    'excerpt' => 'Une methode simple pour relier tes matieres preferees, tes forces et les filieres possibles.',
                    'content' => $this->content(
                        'Choisir une filiere informatique',
                        'Commence par observer tes matieres fortes, tes activites favorites et les problemes que tu veux resoudre. Un eleve attire par les mathematiques et la logique peut explorer la data, le developpement ou la cybersecurite. Un eleve creatif peut se diriger vers UX UI, multimedia ou developpement front-end.',
                        'Au Maroc, les parcours peuvent passer par le tronc commun scientifique, les filieres sciences physiques, sciences mathematiques, BTS, DUT, licence, ecoles d ingenieurs et certifications en ligne.'
                    ),
                ],
                [
                    'title' => 'Les metiers de l intelligence artificielle expliques aux lyceens',
                    'category' => 'Intelligence artificielle',
                    'author' => $teachers[0],
                    'status' => Post::STATUS_PUBLISHED,
                    'tags' => ['ia', 'data-science', 'metiers-du-futur'],
                    'excerpt' => 'Data analyst, ingenieur IA, prompt engineer, machine learning engineer : comprendre les missions.',
                    'content' => $this->content(
                        'Comprendre les metiers IA',
                        'L intelligence artificielle ne se limite pas aux robots. Elle sert a analyser des donnees, automatiser des taches, aider les medecins, proteger les reseaux et personnaliser l apprentissage.',
                        'Pour commencer, il faut renforcer les mathematiques, la logique, Python, les bases de donnees et la curiosite scientifique.'
                    ),
                ],
                [
                    'title' => 'Cybersecurite : pourquoi ce domaine recrute beaucoup',
                    'category' => 'Cybersecurite',
                    'author' => $teachers[1],
                    'status' => Post::STATUS_PUBLISHED,
                    'tags' => ['cybersecurite', 'maroc'],
                    'excerpt' => 'Comprendre les missions, competences et parcours vers la securite informatique.',
                    'content' => $this->content(
                        'La cybersecurite en pratique',
                        'La cybersecurite protege les donnees, les applications et les reseaux contre les attaques. Les profils recherches sont rigoureux, curieux et capables de resoudre des problemes.',
                        'Les eleves peuvent commencer par les reseaux, Linux, la programmation, puis avancer vers les certifications et les laboratoires pratiques.'
                    ),
                ],
                [
                    'title' => 'Plan d action sur 12 mois pour debuter en developpement web',
                    'category' => 'Metiers du futur',
                    'author' => $teachers[0],
                    'status' => Post::STATUS_PUBLISHED,
                    'tags' => ['developpement-web', 'soft-skills'],
                    'excerpt' => 'Un parcours progressif avec HTML, CSS, JavaScript, Laravel et projets concrets.',
                    'content' => $this->content(
                        'Apprendre par projets',
                        'Le developpement web est un excellent domaine pour tester sa motivation. L eleve peut creer une page personnelle, un mini blog, puis une application avec authentification.',
                        'Le plus important est de produire des projets simples, de les ameliorer et de demander un retour.'
                    ),
                ],
                [
                    'title' => 'Parcours au Maroc : BTS, DUT, licence et ecoles d ingenieurs',
                    'category' => 'Parcours de formation',
                    'author' => $teachers[1],
                    'status' => Post::STATUS_PENDING,
                    'tags' => ['bts', 'dut', 'licence', 'maroc'],
                    'excerpt' => 'Article en attente pour tester le workflow de validation Super Admin.',
                    'content' => $this->content(
                        'Comparer les parcours',
                        'Chaque parcours a ses avantages. Le BTS et le DUT donnent une base pratique. La licence permet de continuer vers master. Les ecoles d ingenieurs renforcent la dimension scientifique et projet.',
                        'Le choix depend du niveau, du rythme de travail, du projet et des ressources disponibles.'
                    ),
                ],
                [
                    'title' => 'Developper sa confiance avant un choix d orientation',
                    'category' => 'Motivation et methodes',
                    'author' => $teachers[1],
                    'status' => Post::STATUS_DRAFT,
                    'tags' => ['soft-skills', 'orientation'],
                    'excerpt' => 'Brouillon enseignant pour tester la modification avant validation.',
                    'content' => $this->content(
                        'Confiance et orientation',
                        'Un bon choix d orientation se construit progressivement. Il faut accepter de tester, de se tromper et de corriger son chemin.',
                        'Les mini-projets, les discussions avec enseignants et les stages courts aident a transformer une idee en decision.'
                    ),
                ],
                [
                    'title' => 'Article refuse exemple : titre trop vague',
                    'category' => 'Orientation scolaire',
                    'author' => $teachers[0],
                    'status' => Post::STATUS_REJECTED,
                    'tags' => ['orientation'],
                    'excerpt' => 'Article de test refuse par moderation.',
                    'content' => $this->content(
                        'Article a ameliorer',
                        'Ce contenu est volontairement court pour tester le statut refuse et le motif de refus.',
                        'Le professeur doit preciser les objectifs pedagogiques avant une nouvelle soumission.'
                    ),
                    'rejection_reason' => 'Le contenu doit etre plus detaille et mieux relie aux besoins des eleves marocains.',
                ],
            ];

            $posts = collect($postsData)->map(function (array $row) use ($admin, $categories, $tags): Post {
                $post = Post::updateOrCreate(
                    ['slug' => Str::slug($row['title'])],
                    [
                        'author_id' => $row['author']->id,
                        'category_id' => $categories[$row['category']]->id,
                        'title' => $row['title'],
                        'slug' => Str::slug($row['title']),
                        'excerpt' => $row['excerpt'],
                        'content' => $row['content'],
                        'cover_image' => null,
                        'featured_image' => null,
                        'status' => $row['status'],
                        'is_featured' => $row['status'] === Post::STATUS_PUBLISHED,
                        'views_count' => 0,
                        'likes_count' => 0,
                        'comments_count' => 0,
                        'published_at' => $row['status'] === Post::STATUS_PUBLISHED ? now()->subDays(rand(2, 20)) : null,
                        'approved_by' => $row['status'] === Post::STATUS_PUBLISHED ? $admin->id : null,
                        'approved_at' => $row['status'] === Post::STATUS_PUBLISHED ? now()->subDays(1) : null,
                        'rejected_at' => $row['status'] === Post::STATUS_REJECTED ? now()->subDay() : null,
                        'rejection_reason' => $row['rejection_reason'] ?? null,
                        'seo_title' => $row['title'],
                        'seo_description' => $row['excerpt'],
                    ]
                );

                $post->tags()->sync(collect($row['tags'])->map(fn (string $tag): int => $tags[$tag]->id)->all());

                return $post;
            });

            $this->seedInteractions($posts->where('status', Post::STATUS_PUBLISHED)->values(), $students);
        });
    }

    private function user(string $email, string $name, string $role): User
    {
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => bcrypt('password123'),
                'user_type' => $role === User::ROLE_TEACHER
                    ? User::ROLE_TEACHER
                    : ($role === User::ROLE_STUDENT ? User::ROLE_STUDENT : null),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $user->syncRoles([$role]);

        return $user;
    }

    private function seedInteractions($posts, array $users): void
    {
        $postIds = $posts->pluck('id')->all();

        PostCommentReport::whereHas('comment', fn ($query) => $query->whereIn('post_id', $postIds))->delete();
        PostComment::withoutEvents(fn () => PostComment::whereIn('post_id', $postIds)->forceDelete());
        PostLike::whereIn('post_id', $postIds)->delete();
        PostView::whereIn('post_id', $postIds)->delete();

        foreach ($posts as $index => $post) {
            foreach ($users as $viewIndex => $user) {
                PostView::create([
                    'post_id' => $post->id,
                    'user_id' => $user->id,
                    'session_id' => 'seed-session-' . $post->id . '-' . $user->id,
                    'ip_hash' => hash('sha256', '127.0.0.' . ($viewIndex + 1)),
                    'user_agent_hash' => hash('sha256', 'seed-agent'),
                    'viewed_at' => now()->subDays($index)->subMinutes($viewIndex * 17),
                ]);
            }

            foreach (array_slice($users, 0, ($index % count($users)) + 1) as $user) {
                PostLike::firstOrCreate([
                    'post_id' => $post->id,
                    'user_id' => $user->id,
                ]);
            }

            $firstComment = PostComment::create([
                'post_id' => $post->id,
                'user_id' => $users[0]->id,
                'content' => 'Merci pour cet article. Il m aide a mieux comprendre les choix possibles apres le lycee.',
                'status' => PostComment::STATUS_VISIBLE,
            ]);

            PostComment::create([
                'post_id' => $post->id,
                'user_id' => $users[1]->id,
                'parent_id' => $firstComment->id,
                'content' => 'Je suis d accord. Le passage sur les projets concrets est tres utile.',
                'status' => PostComment::STATUS_VISIBLE,
            ]);

            $secondComment = PostComment::create([
                'post_id' => $post->id,
                'user_id' => $users[2]->id,
                'content' => 'Est-ce que ce domaine demande beaucoup de mathematiques ?',
                'status' => PostComment::STATUS_VISIBLE,
            ]);

            if ($index === 1) {
                PostCommentReport::updateOrCreate(
                    [
                        'post_comment_id' => $secondComment->id,
                        'reporter_id' => $users[3]->id,
                    ],
                    [
                        'reason' => 'Question a verifier',
                        'details' => 'Signalement de test pour verifier le workflow admin.',
                        'status' => PostCommentReport::STATUS_PENDING,
                    ]
                );
            }

            $post->forceFill([
                'views_count' => $post->views()->count(),
                'likes_count' => $post->likes()->count(),
                'comments_count' => $post->comments()->visible()->count(),
            ])->saveQuietly();
        }
    }

    private function content(string $heading, string $paragraphOne, string $paragraphTwo): string
    {
        return <<<HTML
<h2>{$heading}</h2>
<p>{$paragraphOne}</p>
<p>{$paragraphTwo}</p>
<ul>
<li>Identifier ses forces et ses centres d interet.</li>
<li>Comparer les domaines avec des criteres clairs.</li>
<li>Tester par de petits projets avant de choisir.</li>
</ul>
HTML;
    }
}
