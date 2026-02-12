<?php

namespace App\Jobs;

use App\Models\Agent;
use App\Models\Language;
use App\Models\Property\Property;
use App\Models\Property\PropertyContact;
use App\Models\Vendor;
use App\Services\AiAssistantService;
use App\Services\WhatsAppCloudApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Schema;

class SendCampaignWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $contactId;

    public string $campaignType;

    public ?int $propertyId;

    public string $connectableType;

    public int $connectableId;

    public function __construct(int $contactId, string $campaignType, ?int $propertyId, string $connectableType, int $connectableId)
    {
        $this->contactId = $contactId;
        $this->campaignType = $campaignType;
        $this->propertyId = $propertyId;
        $this->connectableType = $connectableType;
        $this->connectableId = $connectableId;
    }

    public function handle(AiAssistantService $aiService, WhatsAppCloudApiService $waService): void
    {
        $contact = PropertyContact::find($this->contactId);
        if (! $contact) {
            return;
        }
        if (Schema::hasColumn('property_contacts', 'unsubscribed_at') && $contact->unsubscribed_at !== null) {
            return;
        }
        if (Schema::hasColumn('property_contacts', 'whatsapp_consent') && empty($contact->whatsapp_consent)) {
            return;
        }

        $toWaId = $contact->whatsapp_wa_id ?? preg_replace('/\D/', '', (string) $contact->phone);
        if ($toWaId === '') {
            return;
        }

        $connectable = $this->connectableType === Vendor::class
            ? Vendor::with('socialCredentials')->find($this->connectableId)
            : Agent::with('socialCredentials')->find($this->connectableId);
        if (! $connectable || ! $connectable->socialCredentials) {
            return;
        }
        $creds = $connectable->socialCredentials;
        if (! $creds->hasWhatsAppApi()) {
            return;
        }

        $propertyTitle = '';
        $propertyUrl = '';
        if ($this->propertyId) {
            $property = Property::find($this->propertyId);
            if ($property) {
                $defaultLang = Language::where('is_default', 1)->first();
                $content = $defaultLang ? $property->getContent($defaultLang->id) : $property->propertyContents()->first();
                if ($content) {
                    $propertyTitle = trim($content->title ?? '');
                    $slug = trim($content->slug ?? '');
                    if ($slug !== '') {
                        $propertyUrl = url('/property/' . $slug);
                    }
                }
            }
        }

        $context = [
            'recipient_name' => $contact->name ?: __('Customer'),
            'property_title' => $propertyTitle,
            'property_url' => $propertyUrl,
            'intent' => $contact->intent ?? '',
        ];
        $result = $aiService->generateCampaignEmail($this->campaignType, $context);
        if (! $result['success'] || empty($result['body'] ?? '')) {
            return;
        }

        $body = preg_replace('/\s+/', ' ', (string) $result['body']);
        $body = trim($body);
        if (mb_strlen($body) > 1000) {
            $body = mb_substr($body, 0, 997) . '...';
        }
        if ($body === '') {
            return;
        }

        $waService->sendText($creds, $toWaId, $body);
    }
}
