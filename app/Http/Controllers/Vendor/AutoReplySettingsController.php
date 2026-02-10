<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AutoReplySettingsController extends Controller
{
    public function index()
    {
        $vendor = Vendor::where('id', Auth::guard('vendor')->user()->id)->first();
        return view('vendors.auto-reply-settings.index', compact('vendor'));
    }

    public function update(Request $request)
    {
        $rules = [
            'auto_reply_enabled' => 'nullable|in:0,1',
            'auto_reply_after_hours' => 'nullable|integer|min:1|max:168',
            'auto_reply_message' => 'nullable|string|max:10000',
        ];
        if ($request->boolean('auto_reply_enabled')) {
            $rules['auto_reply_after_hours'] = 'required|integer|min:1|max:168';
        }
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $vendor = Vendor::where('id', Auth::guard('vendor')->user()->id)->first();
        if (! $vendor) {
            return redirect()->back()->with('warning', __('Access denied.'));
        }

        $vendor->auto_reply_enabled = $request->boolean('auto_reply_enabled') ? 1 : 0;
        $vendor->auto_reply_after_hours = $request->filled('auto_reply_after_hours')
            ? (int) $request->auto_reply_after_hours
            : null;
        $vendor->auto_reply_message = $request->auto_reply_message ? trim($request->auto_reply_message) : null;
        $vendor->save();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => __('Auto-reply settings saved successfully.')]);
        }
        return redirect()->back()->with('success', __('Auto-reply settings saved successfully.'));
    }
}
