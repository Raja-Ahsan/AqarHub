<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\BasicSettings\Basic;
use App\Models\Language;
use App\Models\Property\PropertyContact;
use App\Models\Property\Property;
use App\Http\Helpers\VendorPermissionHelper;
use App\Models\VendorInfo;
use App\Services\WhatsAppCloudApiService;
use Auth;
use Config;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;

class PropertyMessageController extends Controller
{
    public function index(Request $request)
    {
        $query = PropertyContact::with('property')->where('vendor_id', Auth::guard('vendor')->user()->id);

        if (Schema::hasColumn('property_contacts', 'intent') && $request->filled('intent')) {
            $query->where('intent', $request->intent);
        }
        if (Schema::hasColumn('property_contacts', 'lead_score')) {
            $query->orderByRaw('CASE WHEN lead_score IS NULL THEN 0 ELSE lead_score END DESC')->latest();
        } else {
            $query->latest();
        }

        $messages = $query->get();
        $vendor = Auth::guard('vendor')->user();
        $creds = $vendor->socialCredentials;
        $hasWhatsAppApi = $creds && $creds->hasWhatsAppApi();
        $intentCounts = [];
        if (Schema::hasColumn('property_contacts', 'intent')) {
            $intentCounts = PropertyContact::where('vendor_id', Auth::guard('vendor')->user()->id)
                ->selectRaw('intent, count(*) as cnt')->whereNotNull('intent')->where('intent', '!=', '')
                ->groupBy('intent')->pluck('cnt', 'intent')->toArray();
        }
        $showReplySentColumn = Schema::hasColumn('property_contacts', 'reply_email_sent');
        $pkg = VendorPermissionHelper::currentPackagePermission((int) Auth::guard('vendor')->id());
        $showCampaignUi = config('ai.enabled', false) && $pkg && ($pkg->has_ai_features ?? false);
        $vendorProperties = [];
        if ($showCampaignUi) {
            $defaultLang = Language::where('is_default', 1)->first();
            $vendorProperties = Property::where('vendor_id', Auth::guard('vendor')->id())
                ->with(['propertyContents' => function ($q) use ($defaultLang) {
                    if ($defaultLang) {
                        $q->where('language_id', $defaultLang->id);
                    }
                }])
                ->orderByDesc('created_at')
                ->limit(100)
                ->get();
        }
        return view('vendors.property.message', compact('messages', 'intentCounts', 'showReplySentColumn', 'showCampaignUi', 'vendorProperties', 'hasWhatsAppApi'));
    }

    public function destroy(Request $request)
    {
        $message = PropertyContact::where('vendor_id', Auth::guard('vendor')->user()->id)->find($request->message_id);
        if ($message) {

            $message->delete();
        } else {
            Session::flash('warning', 'Something went wrong!');
            return redirect()->back();
        }
        Session::flash('success', 'Message deleted successfully');
        return redirect()->back();
    }

    public function sendReply(Request $request)
    {
        $request->validate([
            'message_id' => 'required|integer',
            'reply_text' => 'required|string|max:10000',
        ]);

        $contact = PropertyContact::where('vendor_id', Auth::guard('vendor')->user()->id)->find($request->message_id);
        if (! $contact) {
            return response()->json(['success' => false, 'error' => __('Message not found or access denied.')], 404);
        }

        if (empty(trim($contact->email))) {
            return response()->json(['success' => false, 'error' => __('Customer email is missing.')], 422);
        }

        $defaultLang = Language::where('is_default', 1)->first();
        $languageId = $defaultLang ? $defaultLang->id : null;
        $vendorInfo = $languageId
            ? VendorInfo::where('vendor_id', $contact->vendor_id)->where('language_id', $languageId)->first()
            : null;
        $senderName = $vendorInfo && ! empty(trim($vendorInfo->name ?? ''))
            ? $vendorInfo->name
            : (Auth::guard('vendor')->user()->username ?? 'Vendor');

        $info = Basic::where('uniqid', 12345)
            ->select('website_title', 'smtp_status', 'smtp_host', 'smtp_port', 'encryption', 'smtp_username', 'smtp_password', 'from_mail', 'from_name')
            ->first();
        if (! $info) {
            return response()->json(['success' => false, 'error' => __('Mail configuration not found.')], 500);
        }
        $websiteTitle = $info->website_title ?: config('app.name');

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

        $htmlBody = view('emails.inquiry-reply', [
            'customerName' => $contact->name ?: __('Customer'),
            'replyBody' => $request->reply_text,
            'senderName' => $senderName,
            'senderRole' => __('Real Estate'),
            'websiteTitle' => $websiteTitle,
        ])->render();

        $fromMail = $info->from_mail ?: config('mail.from.address');
        $fromName = $info->from_name ?: $websiteTitle;
        try {
            Mail::send([], [], function (Message $message) use ($contact, $websiteTitle, $htmlBody, $fromMail, $fromName) {
                $message->to($contact->email)
                    ->subject(__('Reply to your property inquiry') . ' - ' . $websiteTitle)
                    ->from($fromMail, $fromName)
                    ->html($htmlBody, 'text/html');
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => __('Email could not be sent. Please try again.'),
            ], 500);
        }

        if (Schema::hasColumn('property_contacts', 'reply_email_sent')) {
            $contact->reply_email_sent = 1;
        }
        if (Schema::hasColumn('property_contacts', 'reply_sent_at')) {
            $contact->reply_sent_at = now();
        }
        $contact->save();

        return response()->json(['success' => true, 'message' => __('Email sent successfully.')]);
    }

    /**
     * Send reply via WhatsApp (contact must have whatsapp_wa_id; vendor must have API credentials in DB).
     */
    public function sendWhatsAppReply(Request $request)
    {
        $request->validate([
            'message_id' => 'required|integer',
            'reply_text' => 'required|string|max:4000',
        ]);

        $contact = PropertyContact::where('vendor_id', Auth::guard('vendor')->user()->id)->find($request->message_id);
        if (! $contact) {
            return response()->json(['success' => false, 'error' => __('Message not found or access denied.')], 404);
        }

        $waId = $contact->whatsapp_wa_id ?? null;
        if (! $waId) {
            return response()->json(['success' => false, 'error' => __('This contact was not reached via WhatsApp. Use email reply instead.')], 422);
        }

        $creds = Auth::guard('vendor')->user()->socialCredentials;
        if (! $creds || ! $creds->hasWhatsAppApi()) {
            return response()->json(['success' => false, 'error' => __('WhatsApp API is not configured. Add credentials in Edit Profile → Social credentials.')], 422);
        }

        $sent = app(WhatsAppCloudApiService::class)->sendText($creds, $waId, $request->reply_text);
        if (! $sent) {
            return response()->json(['success' => false, 'error' => __('WhatsApp message could not be sent. Check credentials and try again.')], 500);
        }

        return response()->json(['success' => true, 'message' => __('WhatsApp message sent.')]);
    }
}
