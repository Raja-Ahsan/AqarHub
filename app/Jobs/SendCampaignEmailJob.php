<?php

namespace App\Jobs;

use App\Models\BasicSettings\Basic;
use App\Models\Language;
use App\Models\Property\PropertyContact;
use App\Models\Property\Property;
use App\Services\AiAssistantService;
use Config;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Schema;

class SendCampaignEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $contactId;

    public string $campaignType;

    public ?int $propertyId;

    public string $senderName;

    public string $websiteTitle;

    public function __construct(int $contactId, string $campaignType, ?int $propertyId, string $senderName, string $websiteTitle)
    {
        $this->contactId = $contactId;
        $this->campaignType = $campaignType;
        $this->propertyId = $propertyId;
        $this->senderName = $senderName;
        $this->websiteTitle = $websiteTitle;
    }

    public function handle(AiAssistantService $aiService): void
    {
        $contact = PropertyContact::find($this->contactId);
        if (! $contact) {
            return;
        }
        if (Schema::hasColumn('property_contacts', 'unsubscribed_at') && $contact->unsubscribed_at !== null) {
            return;
        }
        if (empty(trim($contact->email ?? ''))) {
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
        if (! $result['success'] || empty($result['subject'] ?? '') || empty($result['body'] ?? '')) {
            return;
        }

        $unsubscribeUrl = '';
        if (Schema::hasColumn('property_contacts', 'unsubscribe_token') && ! empty($contact->unsubscribe_token)) {
            $unsubscribeUrl = route('unsubscribe.campaign', ['token' => $contact->unsubscribe_token]);
        }

        $info = Basic::where('uniqid', 12345)
            ->select('smtp_status', 'smtp_host', 'smtp_port', 'encryption', 'smtp_username', 'smtp_password', 'from_mail', 'from_name')
            ->first();
        if (! $info) {
            return;
        }
        if ($info->smtp_status == 1) {
            $smtpHost = trim((string) $info->smtp_host);
            if (strtolower($smtpHost) === 'smpt.gmail.com') {
                $smtpHost = 'smtp.gmail.com';
            }
            Config::set('mail.mailers.smtp', [
                'transport' => 'smtp',
                'host' => $smtpHost,
                'port' => $info->smtp_port,
                'encryption' => $info->encryption,
                'username' => $info->smtp_username,
                'password' => $info->smtp_password,
                'timeout' => null,
                'auth_mode' => null,
            ]);
        }

        $htmlBody = view('emails.campaign-update', [
            'customerName' => $contact->name ?: __('Customer'),
            'body' => $result['body'],
            'senderName' => $this->senderName,
            'senderRole' => __('Real Estate'),
            'websiteTitle' => $this->websiteTitle,
            'unsubscribeUrl' => $unsubscribeUrl,
        ])->render();

        $fromMail = $info->from_mail ?: config('mail.from.address');
        $fromName = $info->from_name ?: $this->websiteTitle;
        Mail::send([], [], function (Message $message) use ($contact, $result, $htmlBody, $fromMail, $fromName) {
            $message->to($contact->email)
                ->subject($result['subject'])
                ->from($fromMail, $fromName)
                ->html($htmlBody, 'text/html');
        });
    }
}
