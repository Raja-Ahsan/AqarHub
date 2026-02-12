<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\Property\PropertyContact;
use App\Models\Vendor;

/**
 * Notify vendor or agent of a new lead via WhatsApp (optional alert number in DB).
 */
class WhatsAppNewLeadNotificationService
{
    public function __construct(
        protected WhatsAppCloudApiService $whatsAppApi
    ) {}

    /**
     * If the contact is assigned to a vendor or agent who has WhatsApp API + alert number set, send them a notification.
     */
    public function notifyIfConfigured(PropertyContact $contact): void
    {
        $creds = null;
        if ($contact->vendor_id && $contact->vendor_id > 0) {
            $vendor = Vendor::with('socialCredentials')->find($contact->vendor_id);
            $creds = $vendor?->socialCredentials;
        } elseif ($contact->agent_id) {
            $agent = Agent::with('socialCredentials')->find($contact->agent_id);
            $creds = $agent?->socialCredentials;
        }
        if (! $creds || ! $creds->hasWhatsAppApi()) {
            return;
        }
        $alertWaId = $creds->getWhatsAppAlertWaId();
        if (! $alertWaId) {
            return;
        }

        $propertyTitle = '';
        if ($contact->property_id) {
            $property = $contact->property;
            if ($property) {
                $content = $property->propertyContent ?? $property->propertyContents()->first();
                $propertyTitle = $content ? (string) trim($content->title ?? '') : '';
            }
        }
        $message = __('New lead') . ': '
            . ($contact->name ?: __('Customer')) . ', '
            . ($contact->phone ?: '') . '. '
            . ($propertyTitle !== '' ? __('Property') . ': ' . $propertyTitle : '');
        if (mb_strlen($message) > 1000) {
            $message = mb_substr($message, 0, 997) . '...';
        }

        $this->whatsAppApi->sendText($creds, $alertWaId, $message);
    }
}
