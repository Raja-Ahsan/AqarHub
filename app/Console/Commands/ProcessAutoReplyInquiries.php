<?php

namespace App\Console\Commands;

use App\Models\Agent;
use App\Models\AgentInfo;
use App\Models\BasicSettings\Basic;
use App\Models\Language;
use App\Models\Property\PropertyContact;
use App\Models\Vendor;
use App\Models\VendorInfo;
use Illuminate\Console\Command;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class ProcessAutoReplyInquiries extends Command
{
    protected $signature = 'inquiries:process-auto-reply';

    protected $description = 'Send automatic reply emails to customers when vendor/agent has not replied within the set time.';

    public function handle(): int
    {
        if (! Schema::hasColumn('property_contacts', 'reply_email_sent')) {
            return self::SUCCESS;
        }

        $contacts = PropertyContact::where('reply_email_sent', 0)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get();

        $info = Basic::where('uniqid', 12345)
            ->select('website_title', 'smtp_status', 'smtp_host', 'smtp_port', 'encryption', 'smtp_username', 'smtp_password', 'from_mail', 'from_name')
            ->first();

        if (! $info) {
            $this->warn('Mail configuration not found. Skipping auto-reply.');

            return self::SUCCESS;
        }

        $this->configureSmtp($info);

        $websiteTitle = $info->website_title ?: config('app.name');
        $fromMail = $info->from_mail ?: config('mail.from.address');
        $fromName = $info->from_name ?: $websiteTitle;
        $defaultLang = Language::where('is_default', 1)->first();
        $languageId = $defaultLang ? $defaultLang->id : null;
        $defaultMessage = __('Thank you for your inquiry. We have received your message and will get back to you as soon as possible.');

        $sent = 0;

        foreach ($contacts as $contact) {
            $hours = null;
            $replyBody = null;
            $senderName = null;
            $senderRole = __('Real Estate');

            if ($contact->vendor_id) {
                $vendor = Vendor::find($contact->vendor_id);
                if (! $vendor || ! $vendor->auto_reply_enabled || ! $vendor->auto_reply_after_hours) {
                    continue;
                }
                $hours = (int) $vendor->auto_reply_after_hours;
                $replyBody = trim($vendor->auto_reply_message ?? '') ?: $defaultMessage;
                $vendorInfo = $languageId
                    ? VendorInfo::where('vendor_id', $vendor->id)->where('language_id', $languageId)->first()
                    : null;
                $senderName = $vendorInfo && ! empty(trim($vendorInfo->name ?? ''))
                    ? $vendorInfo->name
                    : ($vendor->username ?? 'Vendor');
            } elseif ($contact->agent_id) {
                $agent = Agent::find($contact->agent_id);
                if (! $agent || ! $agent->auto_reply_enabled || ! $agent->auto_reply_after_hours) {
                    continue;
                }
                $hours = (int) $agent->auto_reply_after_hours;
                $replyBody = trim($agent->auto_reply_message ?? '') ?: $defaultMessage;
                $agentInfo = $languageId
                    ? AgentInfo::where('agent_id', $agent->id)->where('language_id', $languageId)->first()
                    : null;
                $senderName = $agentInfo
                    ? trim(($agentInfo->first_name ?? '') . ' ' . ($agentInfo->last_name ?? ''))
                    : ($agent->username ?? 'Agent');
                if ($senderName === '') {
                    $senderName = $agent->username ?? 'Agent';
                }
                $senderRole = __('Agent');
            } else {
                continue;
            }

            $deadline = $contact->created_at->addHours($hours);
            if (now()->lt($deadline)) {
                continue;
            }

            $customerName = $contact->name ?: __('Customer');
            $htmlBody = view('emails.inquiry-reply', [
                'customerName' => $customerName,
                'replyBody' => $replyBody,
                'senderName' => $senderName,
                'senderRole' => $senderRole,
                'websiteTitle' => $websiteTitle,
            ])->render();

            try {
                Mail::send([], [], function (Message $message) use ($contact, $websiteTitle, $htmlBody, $fromMail, $fromName) {
                    $message->to($contact->email)
                        ->subject(__('Reply to your property inquiry') . ' - ' . $websiteTitle)
                        ->from($fromMail, $fromName)
                        ->html($htmlBody, 'text/html');
                });
            } catch (\Exception $e) {
                $this->warn("Auto-reply failed for contact id {$contact->id}: " . $e->getMessage());
                continue;
            }

            $contact->reply_email_sent = 1;
            $contact->reply_sent_at = now();
            $contact->save();
            $sent++;
        }

        if ($sent > 0) {
            $this->info("Sent {$sent} auto-reply email(s).");
        }

        return self::SUCCESS;
    }

    private function configureSmtp($info): void
    {
        if ($info->smtp_status != 1) {
            return;
        }
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
}
