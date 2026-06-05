<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DomainCommentReport extends Model
{
    protected $fillable = ['comment_id', 'user_id', 'reason', 'details', 'status'];

    public function comment(): BelongsTo { return $this->belongsTo(DomainComment::class, 'comment_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
