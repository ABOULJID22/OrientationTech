<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DomainLike extends Model
{
    protected $fillable = ['domain_id', 'user_id'];

    public function domain(): BelongsTo { return $this->belongsTo(Domain::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
