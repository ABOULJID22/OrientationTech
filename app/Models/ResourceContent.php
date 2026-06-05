<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

class ResourceContent extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    public const TYPE_PDF = 'pdf';
    public const TYPE_VIDEO = 'video';
    public const TYPE_CAREER = 'metier';
    public const TYPE_DOMAIN = 'domaine';
    public const TYPE_GUIDE = 'guide';
    public const TYPE_NEWS = 'actualite';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'teacher_id',
        'type',
        'title',
        'slug',
        'summary',
        'content',
        'cover_image',
        'file_path',
        'video_url',
        'domain_key',
        'career_name',
        'status',
        'is_featured',
        'views_count',
        'likes_count',
        'favorites_count',
        'published_at',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
    ];

    public array $translatable = [
        'title',
        'slug',
        'summary',
        'content',
    ];

    public static function typeOptions(): array
    {
        return [
            self::TYPE_PDF => 'PDF',
            self::TYPE_VIDEO => 'Video',
            self::TYPE_CAREER => 'Metier',
            self::TYPE_DOMAIN => 'Domaine',
            self::TYPE_GUIDE => 'Guide',
            self::TYPE_NEWS => 'Actualite',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_DRAFT => 'Brouillon',
            self::STATUS_PUBLISHED => 'Publie',
            self::STATUS_ARCHIVED => 'Archive',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(ResourceContentLike::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(ResourceContentFavorite::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', self::STATUS_PUBLISHED)
            ->where(function (Builder $query): void {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getRouteKey(): mixed
    {
        return $this->translatedValue('slug') ?: $this->getKey();
    }

    public function resolveRouteBinding($value, $field = null)
    {
        if (request()->is('admin/*')) {
            return static::query()->where('id', $value)->first();
        }

        $locales = array_keys(config('orientationtech.supported_locales', ['fr' => 'Francais', 'en' => 'English']));

        return static::query()
            ->where(function (Builder $query) use ($value, $locales): void {
                foreach ($locales as $locale) {
                    $query->orWhere("slug->{$locale}", $value);
                }
            })
            ->first();
    }

    public function getTypeLabelAttribute(): string
    {
        return self::typeOptions()[$this->type] ?? Str::headline((string) $this->type);
    }

    public function getCoverUrlAttribute(): string
    {
        return $this->cover_image
            ? Storage::disk('public')->url($this->cover_image)
            : asset('images/img1.jpg');
    }

    public function getFileUrlAttribute(): ?string
    {
        return $this->file_path ? Storage::disk('public')->url($this->file_path) : null;
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

    private function translatedValue(string $attribute): ?string
    {
        $translations = $this->getTranslations($attribute);

        return $translations[app()->getLocale()]
            ?? $translations['fr']
            ?? $translations[config('app.fallback_locale')]
            ?? collect($translations)->filter()->first()
            ?? null;
    }

    protected static function booted(): void
    {
        static::saving(function (self $resource): void {
            if (! filled($resource->slug) && filled($resource->title)) {
                $resource->slug = Str::slug($resource->title);
            }

            if (! filled($resource->published_at) && $resource->status === self::STATUS_PUBLISHED) {
                $resource->published_at = now();
            }
        });
    }
}
