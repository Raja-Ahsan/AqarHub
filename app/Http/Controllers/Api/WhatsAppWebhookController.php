<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\BasicSettings\Basic;
use App\Models\Property\PropertyContact;
use App\Models\UserSocialCredentials;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

/**
 * WhatsApp Cloud API webhook. Verify and receive incoming messages.
 * All credentials and verify token are read from DB (no .env).
 */
class WhatsAppWebhookController extends Controller
{
    /**
     * GET: Meta verification. Return hub.challenge if verify_token matches.
     */
    public function verify(Request $request): Response
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        if ($mode !== 'subscribe' || ! $challenge) {
            return response('', 403);
        }

        $expectedToken = Basic::value('whatsapp_webhook_verify_token');
        if ($expectedToken === null || $expectedToken === '' || $token !== $expectedToken) {
            return response('', 403);
        }

        return response($challenge, 200, ['Content-Type' => 'text/plain']);
    }

    /**
     * POST: Incoming webhook payload. Create PropertyContact for text messages.
     */
    public function handle(Request $request): Response
    {
        $body = $request->all();
        if (($body['object'] ?? '') !== 'whatsapp_business_account') {
            return response('', 200);
        }

        foreach ($body['entry'] ?? [] as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                if (($change['field'] ?? '') !== 'messages') {
                    continue;
                }
                $value = $change['value'] ?? [];
                $this->processIncomingValue($value);
            }
        }

        return response('', 200);
    }

    protected function processIncomingValue(array $value): void
    {
        $phoneNumberId = $value['metadata']['phone_number_id'] ?? null;
        if (! $phoneNumberId) {
            return;
        }

        $creds = UserSocialCredentials::where('whatsapp_phone_number_id', $phoneNumberId)->first();
        if (! $creds || ! $creds->hasWhatsAppApi()) {
            Log::channel('single')->warning('WhatsApp webhook: unknown or incomplete phone_number_id', ['phone_number_id' => $phoneNumberId]);
            return;
        }

        $connectable = $creds->connectable;
        if (! $connectable) {
            return;
        }

        $vendorId = 0;
        $agentId = null;
        $type = $connectable->getMorphClass();
        if ($type === \App\Models\Vendor::class) {
            $vendorId = $connectable->id;
        } elseif ($type === \App\Models\Agent::class) {
            $agentId = $connectable->id;
        }
        // Admin: vendor_id=0, agent_id=null

        $contacts = $value['contacts'] ?? [];
        $messages = $value['messages'] ?? [];

        foreach ($messages as $msg) {
            $from = $msg['from'] ?? '';
            $type = $msg['type'] ?? '';
            $text = '';
            if ($type === 'text' && isset($msg['text']['body'])) {
                $text = $msg['text']['body'];
            }
            if ($type === 'button' && isset($msg['button']['text'])) {
                $text = $msg['button']['text'];
            }
            if ($text === '' && $type !== 'text' && $type !== 'button') {
                $text = '[' . $type . ']';
            }

            $name = 'WhatsApp User';
            foreach ($contacts as $c) {
                if (($c['wa_id'] ?? '') === $from) {
                    $name = $c['profile']['name'] ?? $name;
                    break;
                }
            }

            if (Schema::hasTable('whatsapp_webhook_logs')) {
                DB::table('whatsapp_webhook_logs')->insert([
                    'phone_number_id' => $phoneNumberId,
                    'from_wa_id' => $from,
                    'message_type' => $type,
                    'payload' => json_encode(['name' => $name, 'text_preview' => mb_substr($text, 0, 500)]),
                    'processed' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            PropertyContact::create([
                'vendor_id' => $vendorId,
                'agent_id' => $agentId,
                'property_id' => 0,
                'name' => $name,
                'email' => $from . '@whatsapp.local',
                'phone' => $from,
                'message' => $text,
                'source' => 'whatsapp',
                'whatsapp_wa_id' => $from,
            ]);
        }
    }
}
