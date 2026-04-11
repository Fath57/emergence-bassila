<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsletterController extends Controller
{
    public function confirm(string $token): View
    {
        $sub = NewsletterSubscriber::where('confirmation_token', $token)->first();

        if (! $sub) {
            return view('newsletter.confirm-error');
        }

        if ($sub->confirmed_at === null) {
            $sub->update([
                'confirmed_at'       => now(),
                'confirmation_token' => null,
            ]);
        }

        return view('newsletter.confirmed');
    }

    public function unsubscribe(Request $request, string $token): View
    {
        $sub = NewsletterSubscriber::where('unsubscribe_token', $token)->first();

        if ($sub && $sub->unsubscribed_at === null) {
            $sub->update(['unsubscribed_at' => now()]);
        }

        return view('newsletter.unsubscribed');
    }
}
