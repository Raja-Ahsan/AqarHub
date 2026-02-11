<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
        if (in_array($this->platform, ['facebook', 'instagram'], true) && is_array($this->meta) && ! empty($this->meta['page_access_token'])) {
            return $this->meta['page_access_token'];
        }
        if ($this->platform === 'facebook') {
            return $this->access_token;
        }
        return null;
    }

    /**
     * Refresh TikTok access token if expired. Uses connectable's credentials from DB.
     */
    public function refreshTiktokTokenIfNeeded(): bool
    {
        if ($this->platform !== 'tiktok' || ! $this->refresh_token) {
            return false;
        }
        if (! $this->isExpired()) {
            return true;
        }
        $creds = $this->connectable?->socialCredentials;
        if (! $creds || ! $creds->hasTiktok()) {
            return false;
        }
        $cfg = $creds->getTiktokConfig();
        try {
            $response = Http::asForm()->post('https://open.tiktokapis.com/v2/oauth/token/', [
                'client_key' => $cfg['client_key'],
                'client_secret' => $cfg['client_secret'],
                'grant_type' => 'refresh_token',
                'refresh_token' => $this->refresh_token,
            ]);
            if (! $response->successful()) {
                Log::warning('TikTok token refresh failed', ['connection_id' => $this->id]);
                return false;
            }
            $data = $response->json();
            $this->access_token = $data['access_token'] ?? null;
            $this->refresh_token = $data['refresh_token'] ?? $this->refresh_token;
            $this->expires_at = isset($data['expires_in']) ? now()->addSeconds((int) $data['expires_in']) : null;
            $this->save();
            return true;
        } catch (\Throwable $e) {
            Log::warning('TikTok token refresh exception: ' . $e->getMessage());
            return false;
        }
    }
}
