<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SocialConnection extends Model
{
    protected $fillable = [
        'connectable_type',
        'connectable_id',
        'platform',
        'access_token',
        'refresh_token',
        'expires_at',
        'platform_user_id',
        'platform_username',
        'meta',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'meta' => 'array',
    ];

    public function connectable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isExpired(): bool
    {
        if (! $this->expires_at) {
            return false;
        }
        return $this->expires_at->isPast();
    }

    public function getTokenForPosting(): ?string
    {
        if ($this->platform === 'facebook' && is_array($this->meta) && ! empty($this->meta['page_access_token'])) {
            return $this->meta['page_access_token'];
        }
        return $this->access_token;
    }
}
