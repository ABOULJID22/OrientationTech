<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResourceContentFavorite extends Model
{
    protected $fillable = [
        'resource_content_id',
        'user_id',
    ];

    public function resourceContent(): BelongsTo
    {
        return $this->belongsTo(ResourceContent::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
