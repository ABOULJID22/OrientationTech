<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

class Domain extends Model
{
    use HasTranslations, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'category',
        'short_description',
        'full_description',
        'why_important',
        'student_profile',
        'school_subjects',
        'technical_skills',
        'soft_skills',
        'tools',
        'related_jobs',
        'learning_path',
        'schools_morocco',
        'certifications',
        'global_demand',
        'morocco_demand',
        'difficulty_level',
        'future_potential',
        'ai_impact',
        'freelance_opportunity',
        'remote_opportunity',
        'math_score',
        'creativity_score',
        'communication_score',
        'problem_solving_score',
        'junior_salary_min',
        'junior_salary_max',
        'senior_salary_min',
        'senior_salary_max',
        'currency',
        'salary_note',
        'advantages',
        'challenges',
        'start_tips',
        'practical_projects',
        'keywords',
        'views_count',
        'likes_count',
        'comments_count',
        'ratings_count',
        'rating_average',
        'is_active',
        'is_featured',
        'display_order',
    ];

    protected $casts = [
        'student_profile' => 'array',
        'school_subjects' => 'array',
        'technical_skills' => 'array',
        'soft_skills' => 'array',
        'tools' => 'array',
        'related_jobs' => 'array',
        'learning_path' => 'array',
        'schools_morocco' => 'array',
        'certifications' => 'array',
        'advantages' => 'array',
        'challenges' => 'array',
        'practical_projects' => 'array',
        'rating_average' => 'float',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public array $translatable = [
        'name',
        'slug',
        'short_description',
        'full_description',
        'why_important',
        'salary_note',
        'start_tips',
        'keywords',
    ];

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

    public function views(): HasMany { return $this->hasMany(DomainView::class); }
    public function likes(): HasMany { return $this->hasMany(DomainLike::class); }
    public function favorites(): HasMany { return $this->hasMany(DomainFavorite::class); }
    public function ratings(): HasMany { return $this->hasMany(DomainRating::class); }
    public function comments(): HasMany { return $this->hasMany(DomainComment::class); }
    public function suggestions(): HasMany { return $this->hasMany(DomainSuggestion::class); }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function isLikedBy(?User $user): bool
    {
        return (bool) $user && $this->likes()->where('user_id', $user->id)->exists();
    }

    public function isFavoritedBy(?User $user): bool
    {
        return (bool) $user && $this->favorites()->where('user_id', $user->id)->exists();
    }

    public function ratingBy(?User $user): ?int
    {
        return $user ? $this->ratings()->where('user_id', $user->id)->value('rating') : null;
    }

    public function salaryRangeLabel(): string
    {
        if (! $this->junior_salary_min && ! $this->junior_salary_max) {
            return 'Non precise';
        }

        return number_format((int) $this->junior_salary_min) . ' - ' . number_format((int) $this->junior_salary_max) . ' ' . $this->currency;
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
        static::saving(function (self $domain): void {
            if (! filled($domain->slug) && filled($domain->name)) {
                $domain->slug = Str::slug($domain->name);
            }
        });
    }
}
