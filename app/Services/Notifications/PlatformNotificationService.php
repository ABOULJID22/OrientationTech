<?php

namespace App\Services\Notifications;

use App\Filament\Pages\MesResultatsDePersonnalites;
use App\Filament\Pages\RapportOrientationComplet;
use App\Filament\Resources\AcademicDiagnostics\AcademicDiagnosticResource;
use App\Filament\Resources\Contacts\ContactResource;
use App\Filament\Resources\Domains\DomainResource;
use App\Filament\Resources\PostCommentReports\PostCommentReportResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\ResourceContents\ResourceContentResource;
use App\Filament\Resources\Support\SupportMessageResource;
use App\Filament\Resources\TestPersonnalises\TestPersonnaliseResource;
use App\Models\AcademicDiagnostic;
use App\Models\Contact;
use App\Models\DomainComment;
use App\Models\DomainCommentReport;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostCommentReport;
use App\Models\ResourceContent;
use App\Models\TestPersonnalise;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class PlatformNotificationService
{
    public function sendToUsers(
        User|Collection|EloquentCollection|array|null $users,
        string $title,
        string $body,
        string $type = 'info',
        ?string $url = null,
        string $actionLabel = 'Ouvrir',
        ?string $icon = null,
    ): int {
        $recipients = $this->normalizeUsers($users);

        if ($recipients->isEmpty()) {
            return 0;
        }

        try {
            $notification = FilamentNotification::make()
                ->title($title)
                ->body($body)
                ->icon($icon ?? $this->iconForType($type));

            match ($type) {
                'success' => $notification->success(),
                'warning' => $notification->warning(),
                'danger' => $notification->danger(),
                default => $notification->info(),
            };

            if (filled($url)) {
                $notification->actions([
                    Action::make('open')
                        ->label($actionLabel)
                        ->url($url, true),
                ]);
            }

            $notification->sendToDatabase($recipients, false);

            return $recipients->count();
        } catch (\Throwable $e) {
            try {
                Log::warning('Platform database notification failed: '.$e->getMessage(), [
                    'title' => $title,
                    'type' => $type,
                    'recipient_ids' => $recipients->pluck('id')->all(),
                ]);
            } catch (\Throwable) {
                // Logging must never break a user workflow.
            }

            return 0;
        }
    }

    public function notifyUserRegistered(User $user): void
    {
        if ($user->isTeacher()) {
            $this->sendToUsers(
                $user,
                'Inscription recue',
                'Votre compte enseignant est en attente de validation par l administration.',
                'warning',
                url('/login'),
                'Suivre mon compte',
                'heroicon-o-user-plus',
            );

            $this->sendToUsers(
                $this->admins(),
                'Nouvel enseignant a valider',
                "{$user->name} vient de demander un compte enseignant.",
                'warning',
                $this->safeResourceUrl(\App\Filament\Resources\Users\UserResource::class, 'index'),
                'Voir les utilisateurs',
                'heroicon-o-user-plus',
            );

            return;
        }

        $this->sendToUsers(
            $user,
            'Bienvenue sur OrientationTech',
            'Commence par configurer ton profil, puis passe le test diagnostique et le test de personnalite.',
            'success',
            $this->safeUrl(fn () => route('student-profile.show')),
            'Completer mon profil',
            'heroicon-o-sparkles',
        );

        $this->sendToUsers(
            $this->admins(),
            'Nouvel eleve inscrit',
            "{$user->name} a rejoint la plateforme OrientationTech.",
            'info',
            $this->safeResourceUrl(\App\Filament\Resources\Users\UserResource::class, 'index'),
            'Voir les utilisateurs',
            'heroicon-o-user-group',
        );
    }

    public function notifyDiagnosticCompleted(AcademicDiagnostic $diagnostic): void
    {
        $user = $diagnostic->user;

        if (! $user) {
            return;
        }

        $this->sendToUsers(
            $user,
            'Resultat diagnostique disponible',
            'Ton premier profil scolaire et tes centres d interet ont ete analyses.',
            'success',
            $this->safeResourceUrl(AcademicDiagnosticResource::class, 'view', ['record' => $diagnostic]),
            'Voir mon resultat',
            'heroicon-o-academic-cap',
        );

        $this->notifyReportReadyIfComplete($user);
    }

    public function notifyPersonalityCompleted(TestPersonnalise $test): void
    {
        $user = $test->user;

        if (! $user) {
            return;
        }

        $this->sendToUsers(
            $user,
            'Resultat de personnalite disponible',
            'Ton profil psychometrique est pret avec des pistes de domaines adaptees.',
            'success',
            $this->safePageUrl(MesResultatsDePersonnalites::class),
            'Voir mes resultats',
            'heroicon-o-chart-bar-square',
        );

        $this->notifyReportReadyIfComplete($user);
    }

    public function notifyReportReadyIfComplete(User $user): void
    {
        $hasDiagnostic = AcademicDiagnostic::query()
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->exists();

        $hasPersonality = TestPersonnalise::query()
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->exists();

        if (! ($hasDiagnostic && $hasPersonality)) {
            return;
        }

        $this->sendToUsers(
            $user,
            'Rapport d orientation pret',
            'Ton rapport complet peut maintenant etre consulte et exporte en PDF.',
            'info',
            $this->safePageUrl(RapportOrientationComplet::class),
            'Ouvrir le rapport',
            'heroicon-o-document-chart-bar',
        );
    }

    public function notifyReportPdfGenerated(User $user): void
    {
        $this->sendToUsers(
            $user,
            'Rapport PDF genere',
            'Ton rapport OrientationTech a ete prepare avec signature et synthese professionnelle.',
            'success',
            $this->safePageUrl(RapportOrientationComplet::class),
            'Retour au rapport',
            'heroicon-o-document-arrow-down',
        );
    }

    public function notifyPostCreated(Post $post): void
    {
        $post->loadMissing('author');

        if ($post->status === Post::STATUS_PENDING) {
            $this->sendToUsers(
                $this->admins(),
                'Article en attente de validation',
                "{$post->author?->name} a propose un article : {$post->title}.",
                'warning',
                $this->safeResourceUrl(PostResource::class, 'edit', ['record' => $post]),
                'Verifier l article',
                'heroicon-o-newspaper',
            );

            return;
        }

        if ($post->status === Post::STATUS_PUBLISHED) {
            $this->notifyPostPublished($post);
        }
    }

    public function notifyPostStatusChanged(Post $post, ?string $oldStatus = null): void
    {
        if ($oldStatus === $post->status) {
            return;
        }

        if ($post->status === Post::STATUS_PENDING) {
            $this->notifyPostCreated($post);
            return;
        }

        if ($post->status === Post::STATUS_PUBLISHED) {
            $this->notifyPostPublished($post);
            return;
        }

        if ($post->status === Post::STATUS_REJECTED && $post->author) {
            $reason = filled($post->rejection_reason) ? " Motif : {$post->rejection_reason}" : '';

            $this->sendToUsers(
                $post->author,
                'Article refuse',
                "Votre article {$post->title} doit etre corrige avant publication.{$reason}",
                'danger',
                $this->safeResourceUrl(PostResource::class, 'edit', ['record' => $post]),
                'Corriger l article',
                'heroicon-o-x-circle',
            );
        }
    }

    public function notifyPostInteraction(Post $post, User $actor, string $interaction, ?PostComment $comment = null): void
    {
        $post->loadMissing('author');
        $author = $post->author;

        if ($author && $author->id !== $actor->id) {
            $label = match ($interaction) {
                'like' => 'a aime votre article',
                'favorite' => 'a sauvegarde votre article',
                'comment' => 'a commente votre article',
                'reply' => 'a repondu sous votre article',
                default => 'a interagi avec votre article',
            };

            $this->sendToUsers(
                $author,
                'Nouvelle interaction sur votre article',
                "{$actor->name} {$label} : {$post->title}.",
                'info',
                $this->frontPostUrl($post),
                'Voir l article',
                'heroicon-o-chat-bubble-left-right',
            );
        }

        if ($comment?->parent?->user && $comment->parent->user->id !== $actor->id && $comment->parent->user->id !== $author?->id) {
            $this->sendToUsers(
                $comment->parent->user,
                'Nouvelle reponse a votre commentaire',
                "{$actor->name} vous a repondu sur l article {$post->title}.",
                'info',
                $this->frontPostUrl($post),
                'Lire la reponse',
                'heroicon-o-chat-bubble-bottom-center-text',
            );
        }
    }

    public function notifyPostCommentReported(PostCommentReport $report): void
    {
        $report->loadMissing('comment.post', 'reporter');
        $post = $report->comment?->post;

        $this->sendToUsers(
            $this->admins(),
            'Commentaire signale',
            "{$report->reporter?->name} a signale un commentaire sur {$post?->title}.",
            'danger',
            $this->safeResourceUrl(PostCommentReportResource::class, 'index'),
            'Voir les signalements',
            'heroicon-o-shield-exclamation',
        );
    }

    public function notifyDomainCommentCreated(DomainComment $comment): void
    {
        $comment->loadMissing('domain', 'user', 'parent.user');
        $domain = $comment->domain;
        $actor = $comment->user;

        if (! $domain || ! $actor) {
            return;
        }

        $this->sendToUsers(
            $this->admins()->merge($this->teachers()),
            'Nouvelle question sur un domaine',
            "{$actor->name} a ajoute un commentaire sur {$domain->name}.",
            'info',
            $this->frontDomainUrl($domain),
            'Voir le domaine',
            'heroicon-o-academic-cap',
        );

        if ($comment->parent?->user && $comment->parent->user->id !== $actor->id) {
            $this->sendToUsers(
                $comment->parent->user,
                'Reponse a votre commentaire',
                "{$actor->name} vous a repondu sur le domaine {$domain->name}.",
                'info',
                $this->frontDomainUrl($domain),
                'Lire la reponse',
                'heroicon-o-chat-bubble-left-right',
            );
        }
    }

    public function notifyDomainCommentReported(DomainCommentReport $report): void
    {
        $report->loadMissing('comment.domain', 'user');
        $domain = $report->comment?->domain;

        $this->sendToUsers(
            $this->admins(),
            'Signalement sur un domaine',
            "{$report->user?->name} a signale un commentaire sur {$domain?->name}.",
            'danger',
            $this->safeResourceUrl(DomainResource::class, 'index'),
            'Gerer les domaines',
            'heroicon-o-shield-exclamation',
        );
    }

    public function notifyResourceCreated(ResourceContent $resource): void
    {
        if ($resource->status === ResourceContent::STATUS_PUBLISHED) {
            $this->notifyResourcePublished($resource);
            return;
        }

        $this->sendToUsers(
            $this->admins(),
            'Nouvelle ressource pedagogique',
            "{$resource->teacher?->name} a cree une ressource : {$resource->title}.",
            'info',
            $this->safeResourceUrl(ResourceContentResource::class, 'edit', ['record' => $resource]),
            'Voir la ressource',
            'heroicon-o-folder-open',
        );
    }

    public function notifyResourceStatusChanged(ResourceContent $resource, ?string $oldStatus = null): void
    {
        if ($oldStatus === $resource->status) {
            return;
        }

        if ($resource->status === ResourceContent::STATUS_PUBLISHED) {
            $this->notifyResourcePublished($resource);
        }
    }

    public function notifyResourceInteraction(ResourceContent $resource, User $actor, string $interaction): void
    {
        $resource->loadMissing('teacher');
        $teacher = $resource->teacher;

        if (! $teacher || $teacher->id === $actor->id) {
            return;
        }

        $label = $interaction === 'favorite'
            ? 'a sauvegarde votre ressource'
            : 'a aime votre ressource';

        $this->sendToUsers(
            $teacher,
            'Interaction sur votre ressource',
            "{$actor->name} {$label} : {$resource->title}.",
            'info',
            $this->frontResourceUrl($resource),
            'Voir la ressource',
            'heroicon-o-heart',
        );
    }

    public function notifyContactMessage(Contact $contact): void
    {
        $this->sendToUsers(
            $this->admins()->merge($this->teachers()),
            'Nouveau message de contact',
            "{$contact->name} ({$contact->email}) a envoye une demande.",
            'warning',
            $this->safeResourceUrl(ContactResource::class, 'index') ?? $this->safeResourceUrl(SupportMessageResource::class, 'index'),
            'Voir le message',
            'heroicon-o-envelope',
        );
    }

    public function notifySupportReply(Contact $contact): void
    {
        $user = $contact->user;

        if (! $user) {
            return;
        }

        $this->sendToUsers(
            $user,
            'Reponse du support',
            'L equipe OrientationTech a repondu a votre message de support.',
            'success',
            url('/admin/client-support'),
            'Voir la reponse',
            'heroicon-o-lifebuoy',
        );
    }

    private function notifyPostPublished(Post $post): void
    {
        $post->loadMissing('author');

        $this->sendToUsers(
            $post->author,
            'Article publie',
            "Votre article {$post->title} est maintenant visible pour les eleves.",
            'success',
            $this->frontPostUrl($post),
            'Voir l article',
            'heroicon-o-check-circle',
        );

        $this->sendToUsers(
            $this->students(),
            'Nouvel article disponible',
            "Un nouvel article d orientation vient d etre publie : {$post->title}.",
            'info',
            $this->frontPostUrl($post),
            'Lire l article',
            'heroicon-o-newspaper',
        );
    }

    private function notifyResourcePublished(ResourceContent $resource): void
    {
        $resource->loadMissing('teacher');

        $this->sendToUsers(
            $resource->teacher,
            'Ressource publiee',
            "Votre ressource {$resource->title} est maintenant disponible.",
            'success',
            $this->frontResourceUrl($resource),
            'Voir la ressource',
            'heroicon-o-check-circle',
        );

        $this->sendToUsers(
            $this->students(),
            'Nouvelle ressource pedagogique',
            "Une ressource utile vient d etre ajoutee : {$resource->title}.",
            'info',
            $this->frontResourceUrl($resource),
            'Consulter',
            'heroicon-o-folder-open',
        );
    }

    private function admins(): Collection
    {
        return User::role(User::ROLE_SUPER_ADMIN)
            ->where('is_active', true)
            ->get();
    }

    private function teachers(): Collection
    {
        return User::role(User::ROLE_TEACHER)
            ->where('is_active', true)
            ->get();
    }

    private function students(): Collection
    {
        return User::role(User::ROLE_STUDENT)
            ->where('is_active', true)
            ->get();
    }

    private function normalizeUsers(User|Collection|EloquentCollection|array|null $users): Collection
    {
        if (! $users) {
            return collect();
        }

        if ($users instanceof User) {
            return collect([$users]);
        }

        return collect($users)
            ->filter(fn ($user): bool => $user instanceof User && filled($user->id))
            ->unique('id')
            ->values();
    }

    private function iconForType(string $type): string
    {
        return match ($type) {
            'success' => 'heroicon-o-check-circle',
            'warning' => 'heroicon-o-exclamation-triangle',
            'danger' => 'heroicon-o-shield-exclamation',
            default => 'heroicon-o-information-circle',
        };
    }

    private function safeResourceUrl(string $resourceClass, string $page = 'index', array $parameters = []): ?string
    {
        return $this->safeUrl(fn () => $resourceClass::getUrl($page, $parameters));
    }

    private function safePageUrl(string $pageClass): ?string
    {
        return $this->safeUrl(fn () => $pageClass::getUrl());
    }

    private function frontPostUrl(Post $post): ?string
    {
        return $this->safeUrl(fn () => route('pages.blog.show', ['post' => $post->slug]));
    }

    private function frontDomainUrl($domain): ?string
    {
        return $this->safeUrl(fn () => route('domains.show', ['domain' => $domain->slug]));
    }

    private function frontResourceUrl(ResourceContent $resource): ?string
    {
        return $this->safeUrl(fn () => route('pages.resources.show', ['resourceContent' => $resource->slug]));
    }

    private function safeUrl(callable $resolver): ?string
    {
        try {
            return $resolver();
        } catch (\Throwable) {
            return null;
        }
    }
}
