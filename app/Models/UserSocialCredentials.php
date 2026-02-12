<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UserSocialCredentials extends Model
{
    protected $table = 'user_social_credentials';

    protected $fillable = [
        'connectable_type',
        'connectable_id',
        'facebook_app_id',
        'facebook_app_secret',
        'linkedin_client_id',
        'linkedin_client_secret',
        'tiktok_client_key',
        'tiktok_client_secret',
        'twitter_client_id',
        'twitter_client_secret',
        'whatsapp_phone_number',
        'whatsapp_channel_link',
        'whatsapp_phone_number_id',
        'whatsapp_business_account_id',
        'whatsapp_access_token',
        'whatsapp_alert_wa_id',
    ];

    protected $casts = [
        'whatsapp_access_token' => 'encrypted',
    ];

    public function connectable(): MorphTo
    {
        return $this->morphTo();
    }

    public function hasFacebook(): bool
    {
        return ! empty($this->facebook_app_id) && ! empty($this->facebook_app_secret);
    }

    public function hasLinkedIn(): bool
    {
        return ! empty($this->linkedin_client_id) && ! empty($this->linkedin_client_secret);
    }

    public function hasTiktok(): bool
    {
        return ! empty($this->tiktok_client_key) && ! empty($this->tiktok_client_secret);
    }

    public function hasTwitter(): bool
    {
        return ! empty($this->twitter_client_id) && ! empty($this->twitter_client_secret);
    }

    public function hasWhatsApp(): bool
    {
        return ! empty(trim((string) $this->whatsapp_phone_number));
    }

    /**
     * WhatsApp number for Click to Chat (E.164 or national format; strip spaces for wa.me link).
     */
    public function getWhatsAppPhoneForLink(): ?string
    {
        $num = trim((string) $this->whatsapp_phone_number);
        if ($num === '') {
            return null;
        }
        return preg_replace('/\D/', '', $num);
    }

    public function getWhatsAppChannelLink(): ?string
    {
        $link = trim((string) $this->whatsapp_channel_link);
        return $link !== '' ? $link : null;
    }

    /** Whether WhatsApp Cloud API credentials are set (for receive/reply). */
    public function hasWhatsAppApi(): bool
    {
        return ! empty(trim((string) $this->whatsapp_phone_number_id))
            && ! empty(trim((string) $this->whatsapp_business_account_id))
            && ! empty($this->whatsapp_access_token);
    }

    /** Decrypted access token for API calls (null if not set). */
    public function getWhatsAppAccessToken(): ?string
    {
        $token = $this->whatsapp_access_token;
        return $token !== null && $token !== '' ? $token : null;
    }

    /** WA ID (phone digits) to receive "new lead" alerts; optional. */
    public function getWhatsAppAlertWaId(): ?string
    {
        $id = trim((string) ($this->whatsapp_alert_wa_id ?? ''));
        return $id !== '' ? preg_replace('/\D/', '', $id) : null;
    }

    public function getFacebookConfig(): array
    {
        $base = rtrim(config('app.url'), '/');
        return [
            'client_id' => $this->facebook_app_id,
            'client_secret' => $this->facebook_app_secret,
            'redirect' => $base . '/auth/social/callback/facebook',
        ];
    }

    public function getLinkedInConfig(): array
    {
        $base = rtrim(config('app.url'), '/');
        return [
            'client_id' => $this->linkedin_client_id,
            'client_secret' => $this->linkedin_client_secret,
            'redirect' => $base . '/auth/social/callback/linkedin',
        ];
    }

    public function getTiktokConfig(): array
    {
        $base = rtrim(config('app.url'), '/');
        return [
            'client_key' => $this->tiktok_client_key,
            'client_secret' => $this->tiktok_client_secret,
            'redirect' => $base . '/auth/social/callback/tiktok',
        ];
    }

    public function getTwitterConfig(): array
    {
        $base = rtrim(config('app.url'), '/');
        return [
            'client_id' => $this->twitter_client_id,
            'client_secret' => $this->twitter_client_secret,
            'redirect' => $base . '/auth/social/callback/twitter',
        ];
    }
}
