<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiChatMessage extends Model
{
    protected $fillable = ['session_id', 'user_id', 'role', 'content', 'intent'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
