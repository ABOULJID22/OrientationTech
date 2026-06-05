<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DomainSuggestion extends Model
{
    protected $fillable = ['domain_id', 'teacher_id', 'suggestion_type', 'content', 'status', 'admin_note'];

    public function domain(): BelongsTo { return $this->belongsTo(Domain::class); }
    public function teacher(): BelongsTo { return $this->belongsTo(User::class, 'teacher_id'); }
}
