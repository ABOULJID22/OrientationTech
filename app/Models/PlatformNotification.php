<?php

namespace App\Models;

use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class PlatformNotification extends Model
{
    use SoftDeletes;

    public const TYPE_INFO = 'info';
    public const TYPE_SUCCESS = 'success';
    public const TYPE_WARNING = 'warning';
    public const TYPE_DANGER = 'danger';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SENT = 'sent';

    protected $fillable = [
        'created_by',
        'title',
        'body',
        'type',
        'feature_key',
        'target_roles',
        'action_label',
        'action_url',
        'status',
        'sent_count',
        'sent_at',
    ];

    protected $casts = [
        'target_roles' => 'array',
        'sent_at' => 'datetime',
    ];

    public static function typeOptions(): array
    {
        return [
            self::TYPE_INFO => 'Information',
            self::TYPE_SUCCESS => 'Succes',
            self::TYPE_WARNING => 'Attention',
            self::TYPE_DANGER => 'Urgent',
        ];
    }

    public static function featureOptions(): array
    {
        return [
            'general' => 'General',
            'diagnostic' => 'Test diagnostique',
            'personnalite' => 'Test de personnalite',
            'rapport' => 'Rapport orientation',
            'domaines' => 'Explorer les domaines',
            'blog' => 'Blog educatif',
            'ressources' => 'Ressources pedagogiques',
            'support' => 'Support',
            'compte' => 'Compte utilisateur',
            'systeme' => 'Systeme',
        ];
    }

    public static function roleOptions(): array
    {
        return [
            User::ROLE_STUDENT => 'Eleves',
            User::ROLE_TEACHER => 'Enseignants',
            User::ROLE_SUPER_ADMIN => 'Administration',
            User::ROLE_USER => 'Utilisateurs simples',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_DRAFT => 'Brouillon',
            self::STATUS_SENT => 'Envoyee',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeSent(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SENT);
    }

    public function recipients(): Collection
    {
        $roles = collect($this->target_roles)
            ->filter()
            ->values()
            ->all();

        if ($roles === []) {
            return collect();
        }

        return User::role($roles)
            ->where('is_active', true)
            ->get();
    }

    public function sendToRecipients(): int
    {
        $recipients = $this->recipients();

        if ($recipients->isEmpty()) {
            $this->forceFill([
                'status' => self::STATUS_SENT,
                'sent_count' => 0,
                'sent_at' => now(),
            ])->save();

            return 0;
        }

        $notification = FilamentNotification::make()
            ->title($this->title)
            ->body($this->body);

        match ($this->type) {
            self::TYPE_SUCCESS => $notification->success(),
            self::TYPE_WARNING => $notification->warning(),
            self::TYPE_DANGER => $notification->danger(),
            default => $notification->info(),
        };

        if (filled($this->action_url)) {
            $notification->actions([
                Action::make('open')
                    ->label($this->action_label ?: 'Ouvrir')
                    ->url($this->action_url, true),
            ]);
        }

        $notification->sendToDatabase($recipients, false);

        $this->forceFill([
            'status' => self::STATUS_SENT,
            'sent_count' => $recipients->count(),
            'sent_at' => now(),
        ])->save();

        return $recipients->count();
    }

    protected static function booted(): void
    {
        static::creating(function (self $notification): void {
            $notification->created_by ??= auth()->id();
        });
    }
}
