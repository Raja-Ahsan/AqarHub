<?php

namespace App\Services;

use App\Models\UserSocialCredentials;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Send messages via WhatsApp Cloud API. Uses credentials from DB only.
 */
class WhatsAppCloudApiService
{
    protected string $baseUrl = 'https://graph.facebook.com/v18.0';

    /**
     * Send a text message. Returns true on success, false otherwise.
     */
    public function sendText(UserSocialCredentials $creds, string $toWaId, string $text): bool
    {
        if (! $creds->hasWhatsAppApi()) {
            return false;
        }

        $phoneNumberId = trim((string) $creds->whatsapp_phone_number_id);
        $token = $creds->getWhatsAppAccessToken();
        if (! $phoneNumberId || ! $token) {
            return false;
        }

        $url = $this->baseUrl . '/' . $phoneNumberId . '/messages';
        $body = [
            'messaging_product' => 'whatsapp',
            'to' => preg_replace('/\D/', '', $toWaId),
            'type' => 'text',
            'text' => ['body' => $text],
        ];

        $response = Http::withToken($token)
            ->post($url, $body);

        if (! $response->successful()) {
            Log::channel('single')->warning('WhatsApp API send failed', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
            return false;
        }

        return true;
    }
}
