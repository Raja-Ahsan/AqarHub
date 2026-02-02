<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;

class CheckoutController extends Controller
{
    /**
     * Redirect after offline payment success (membership).
     */
    public function offlineSuccess()
    {
        return redirect()->route('success.page');
    }

    /**
     * Redirect after trial payment success (membership).
     */
    public function trialSuccess()
    {
        return redirect()->route('success.page');
    }
}
