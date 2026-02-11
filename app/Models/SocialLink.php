<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SocialLink extends Model
{
    protected $fillable = [
        'connectable_type',
        'connectable_id',
        'facebook_url',
        'linkedin_url',
        'instagram_url',
        'tiktok_url',
        'twitter_url',
    ];

    public function connectable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * All URL keys for validation and form fields.
     */
    public static function urlKeys(): array
    {
        return ['facebook_url', 'linkedin_url', 'instagram_url', 'tiktok_url', 'twitter_url'];
    }
}
