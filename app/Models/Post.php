<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'author_id',
        'category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'cover_image',
        'featured_image',
        'status',
        'is_featured',
        'views_count',
        'likes_count',
        'favorites_count',
        'comments_count',
        'published_at',
        'approved_by',
        'approved_at',
        'rejected_at',
        'rejection_reason',
        'seo_title',
        'seo_description',
    ];
 
    protected $casts = [
        'published_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'featured' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_ARCHIVED = 'archived';

    public static function statusOptions(): array
    {
        return [
            self::STATUS_DRAFT => 'Brouillon',
            self::STATUS_PENDING => 'En attente',
            self::STATUS_SCHEDULED => 'Programme',
            self::STATUS_PUBLISHED => 'Publie',
            self::STATUS_REJECTED => 'Refuse',
            self::STATUS_ARCHIVED => 'Archive',
        ];
    }

    public function author(): BelongsTo {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category(): BelongsTo {
        return $this->belongsTo(Category::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function views(): HasMany
    {
        return $this->hasMany(PostView::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(PostLike::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(PostFavorite::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(PostComment::class);
    }

    public function visibleComments(): HasMany
    {
        return $this->comments()->visible();
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', self::STATUS_PUBLISHED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function isLikedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->likes()
            ->where('user_id', $user->id)
            ->exists();
    }

    public function isFavoritedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->favorites()
            ->where('user_id', $user->id)
            ->exists();
    }

    public function translations(): HasMany
    {
        return $this->hasMany(PostTranslation::class);
    }

    public function translation(?string $locale = null): ?PostTranslation
    {
        $loc = $locale ?: app()->getLocale();
        $fallback = config('app.fallback_locale');
        $loaded = $this->relationLoaded('translations') ? $this->translations : $this->translations()->get();
        // Prefer exact locale
        $match = $loaded->firstWhere('locale', $loc);
        if ($match) {
            return $match;
        }

        // Then prefer configured fallback locale
        if ($fallback) {
            $fb = $loaded->firstWhere('locale', $fallback);
            if ($fb) {
                return $fb;
            }
        }

        // If no translation exists for current or fallback locale,
        // return the first available translation (any locale) so the
        // front-end can display content created in another language
        // instead of falling back to the un-translated attribute value.
        return $loaded->first() ?: null;
    }

    // MODIFICATION: Désactiver les accesseurs dans le contexte Filament
    public function getTitleAttribute($value): ?string
    {
        if (app()->runningInConsole() || request()->is('admin/*')) {
            return $value;
        }
        // If a translation exists (including any available translation), prefer it
        return $this->translation()?->title ?? $value;
    }

    public function getContentAttribute($value): ?string
    {
        if (app()->runningInConsole() || request()->is('admin/*')) {
            return $value;
        }
        return $this->translation()?->content ?? $value;
    }

    public function getSlugAttribute($value): ?string
    {
        if (app()->runningInConsole() || request()->is('admin/*')) {
            return $value;
        }
        return $this->translation()?->slug ?? $value;
    }

    // Pour les routes front-end uniquement
    public function getRouteKeyName(): string {
        // Si on est dans l'admin Filament, utiliser l'ID
        if (request()->is('admin/*')) {
            return 'id';
        }
        return 'slug';
    }

    // Route binding pour le front-end avec slug traduit
    public function resolveRouteBinding($value, $field = null)
    {
        // Dans l'admin, résolution simple par ID
        if (request()->is('admin/*')) {
            return static::query()->where('id', $value)->first();
        }

        // Sur le front-end, résolution par slug traduit
        // We'll attempt to match the slug on the main table first,
        // then try to find any translation with this slug regardless of locale.
        return static::query()
            ->where($field ?? 'slug', $value)
            ->orWhereHas('translations', function ($q) use ($value) {
                $q->where('slug', $value);
            })
            ->first();
    }
    
    /**
     * Tags pivot relation
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    protected static function booted(): void
    {
        static::saving(function (self $post): void {
            if ($post->status === self::STATUS_PUBLISHED) {
                $post->published_at ??= now();
                $post->approved_at ??= now();
                $post->approved_by ??= auth()->id();
            }

            if ($post->status === self::STATUS_REJECTED) {
                $post->rejected_at ??= now();
            }
        });
    }
}
