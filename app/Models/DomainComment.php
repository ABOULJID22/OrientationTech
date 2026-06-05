<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DomainComment extends Model
{
    use SoftDeletes;

    public const STATUS_VISIBLE = 'visible';
    public const STATUS_PENDING = 'pending';
    public const STATUS_HIDDEN = 'hidden';
    public const STATUS_REPORTED = 'reported';
    public const STATUS_DELETED = 'deleted';

    protected $fillable = ['domain_id', 'user_id', 'parent_id', 'content', 'status'];

    public static function statusOptions(): array
    {
        return [
            self::STATUS_VISIBLE => 'Visible',
            self::STATUS_PENDING => 'En attente',
            self::STATUS_HIDDEN => 'Masque',
            self::STATUS_REPORTED => 'Signale',
            self::STATUS_DELETED => 'Supprime',
        ];
    }

    public function domain(): BelongsTo { return $this->belongsTo(Domain::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function parent(): BelongsTo { return $this->belongsTo(self::class, 'parent_id'); }
    public function replies(): HasMany { return $this->hasMany(self::class, 'parent_id')->visible()->oldest(); }
    public function reports(): HasMany { return $this->hasMany(DomainCommentReport::class, 'comment_id'); }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_VISIBLE);
    }

    protected static function booted(): void
    {
        static::created(fn (self $comment) => $comment->status === self::STATUS_VISIBLE ? $comment->domain()->increment('comments_count') : null);
        static::deleted(fn (self $comment) => $comment->status === self::STATUS_VISIBLE ? $comment->domain()->decrement('comments_count') : null);
    }
}
