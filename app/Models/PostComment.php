<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PostComment extends Model
{
    use SoftDeletes;

    public const STATUS_VISIBLE = 'visible';
    public const STATUS_PENDING = 'pending';
    public const STATUS_HIDDEN = 'hidden';
    public const STATUS_DELETED = 'deleted';

    protected $fillable = [
        'post_id',
        'user_id',
        'parent_id',
        'content',
        'status',
        'hidden_by',
        'hidden_at',
    ];

    protected $casts = [
        'hidden_at' => 'datetime',
    ];

    public static function statusOptions(): array
    {
        return [
            self::STATUS_VISIBLE => 'Visible',
            self::STATUS_PENDING => 'En attente',
            self::STATUS_HIDDEN => 'Masque',
            self::STATUS_DELETED => 'Supprime',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->visible()->oldest();
    }

    public function reports(): HasMany
    {
        return $this->hasMany(PostCommentReport::class);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_VISIBLE);
    }

    protected static function booted(): void
    {
        static::created(function (self $comment): void {
            if ($comment->status === self::STATUS_VISIBLE) {
                $comment->post()->increment('comments_count');
            }
        });

        static::updated(function (self $comment): void {
            if (! $comment->wasChanged('status')) {
                return;
            }

            if ($comment->getOriginal('status') !== self::STATUS_VISIBLE && $comment->status === self::STATUS_VISIBLE) {
                $comment->post()->increment('comments_count');
            }

            if ($comment->getOriginal('status') === self::STATUS_VISIBLE && $comment->status !== self::STATUS_VISIBLE) {
                $comment->post()->decrement('comments_count');
            }
        });

        static::deleted(function (self $comment): void {
            if ($comment->status === self::STATUS_VISIBLE) {
                $comment->post()->decrement('comments_count');
            }
        });
    }
}
