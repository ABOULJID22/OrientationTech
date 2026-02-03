<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Contact extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'user_type', 'user_other', 'message',
        'replied_at', 'replied_by', 'reply_message',
    ];

    protected $casts = [
        'replied_at' => 'datetime',
    ];

    public function messages()
    {
        return $this->hasMany(SupportMessage::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }
}
