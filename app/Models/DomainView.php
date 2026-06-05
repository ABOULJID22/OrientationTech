<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DomainView extends Model
{
    protected $fillable = ['domain_id', 'user_id', 'ip_hash', 'session_id', 'viewed_at'];

    protected $casts = ['viewed_at' => 'datetime'];

    public function domain(): BelongsTo { return $this->belongsTo(Domain::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
