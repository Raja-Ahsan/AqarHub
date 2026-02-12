<?php

namespace App\Http\Controllers\BackEnd\Property;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Property\PropertyContact;
use App\Services\WhatsAppCloudApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Session;

class PropertyMessageController extends Controller
{
    public function index()
    {
        $messages = PropertyContact::with('property')->where('vendor_id', 0)->latest()->get();
        $whatsappBroadcastAvailable = $this->whatsappBroadcastAvailable();
        $hasWhatsAppApi = $this->whatsappBroadcastAvailable();
        return view('backend.property.message', compact('messages', 'whatsappBroadcastAvailable', 'hasWhatsAppApi'));
    }

    public function destroy(Request $request)
    {
        $message = PropertyContact::where('vendor_id', 0)->find($request->message_id);
        if ($message) {

            $message->delete();
        } else {
            Session::flash('warning', 'Something went wrong!');
            return redirect()->back();
        }
        Session::flash('success', 'Message deleted successfully');
        return redirect()->back();
    }

    /**
     * Show WhatsApp broadcast form (admin only).
     */
    public function broadcastForm()
    {
        if (! $this->whatsappBroadcastAvailable()) {
            Session::flash('warning', __('WhatsApp API is not configured for the main admin. Add credentials in Edit Profile → Social credentials.'));
            return redirect()->route('admin.property_message.index');
        }
        $admin = Admin::with('socialCredentials')->where('role_id', null)->first();
        $contactsCount = PropertyContact::where('vendor_id', 0)
            ->where(function ($q) {
                $q->whereNotNull('phone')->where('phone', '!=', '');
            })
            ->when(Schema::hasColumn('property_contacts', 'unsubscribed_at'), fn ($q) => $q->whereNull('unsubscribed_at'))
            ->when(Schema::hasColumn('property_contacts', 'whatsapp_consent'), fn ($q) => $q->where('whatsapp_consent', 1))
            ->count();
        return view('backend.property.whatsapp-broadcast', compact('contactsCount'));
    }

    /**
     * Send WhatsApp broadcast to admin-scope leads (vendor_id=0) with phone and consent.
     */
    public function broadcastSend(Request $request)
    {
        $request->validate(['message' => 'required|string|max:2000']);
        $admin = Admin::with('socialCredentials')->where('role_id', null)->first();
        if (! $admin || ! $admin->socialCredentials || ! $admin->socialCredentials->hasWhatsAppApi()) {
            Session::flash('warning', __('WhatsApp API is not configured.'));
            return redirect()->route('admin.property_message.index');
        }
        $contacts = PropertyContact::where('vendor_id', 0)
            ->where(function ($q) {
                $q->whereNotNull('phone')->where('phone', '!=', '');
            })
            ->when(Schema::hasColumn('property_contacts', 'unsubscribed_at'), fn ($q) => $q->whereNull('unsubscribed_at'))
            ->when(Schema::hasColumn('property_contacts', 'whatsapp_consent'), fn ($q) => $q->where('whatsapp_consent', 1))
            ->get();
        $creds = $admin->socialCredentials;
        $wa = app(WhatsAppCloudApiService::class);
        $sent = 0;
        $text = $request->message;
        if (mb_strlen($text) > 1000) {
            $text = mb_substr($text, 0, 997) . '...';
        }
        foreach ($contacts as $c) {
            $to = $c->whatsapp_wa_id ?? preg_replace('/\D/', '', (string) $c->phone);
            if ($to !== '' && $wa->sendText($creds, $to, $text)) {
                $sent++;
            }
        }
        Session::flash('success', __('Broadcast sent to :count recipient(s).', ['count' => $sent]));
        return redirect()->route('admin.property_message.index');
    }

    /**
     * Send reply via WhatsApp to a single contact (admin-scope messages; contact must have whatsapp_wa_id).
     */
    public function sendWhatsAppReply(Request $request)
    {
        $request->validate([
            'message_id' => 'required|integer',
            'reply_text' => 'required|string|max:4000',
        ]);

        $contact = PropertyContact::where('vendor_id', 0)->find($request->message_id);
        if (! $contact) {
            return response()->json(['success' => false, 'error' => __('Message not found or access denied.')], 404);
        }

        $waId = $contact->whatsapp_wa_id ?? null;
        if (! $waId) {
            return response()->json(['success' => false, 'error' => __('This contact was not reached via WhatsApp. Use email or broadcast instead.')], 422);
        }

        $admin = Admin::with('socialCredentials')->where('role_id', null)->first();
        if (! $admin || ! $admin->socialCredentials || ! $admin->socialCredentials->hasWhatsAppApi()) {
            return response()->json(['success' => false, 'error' => __('WhatsApp API is not configured. Add credentials in Edit Profile → Social credentials.')], 422);
        }

        $sent = app(WhatsAppCloudApiService::class)->sendText($admin->socialCredentials, $waId, $request->reply_text);
        if (! $sent) {
            return response()->json(['success' => false, 'error' => __('WhatsApp message could not be sent. Check credentials and try again.')], 500);
        }

        return response()->json(['success' => true, 'message' => __('WhatsApp message sent.')]);
    }

    protected function whatsappBroadcastAvailable(): bool
    {
        $admin = Admin::with('socialCredentials')->where('role_id', null)->first();
        return $admin && $admin->socialCredentials && $admin->socialCredentials->hasWhatsAppApi();
    }
}
