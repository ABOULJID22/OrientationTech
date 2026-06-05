<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicDiagnostic extends Model
{
    protected $fillable = [
        'user_id',
        'macro_cycle',
        'academic_level',
        'interest_theme',
        'track_branch',
        'institution_type',
        'specialty_family',
        'specialty_label',
        'biof_language',
        'remark',
        'diagnostic_answers',
        'status',
        'result_code',
        'result_label',
        'result_summary',
        'result_payload',
        'submitted_at',
    ];

    protected $casts = [
        'diagnostic_answers' => 'array',
        'result_payload' => 'array',
        'submitted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }
}
