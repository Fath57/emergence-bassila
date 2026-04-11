<?php

namespace App\Http\Controllers;

use App\Models\NewsletterCampaignSend;
use Illuminate\Http\Response;

class NewsletterTrackingController extends Controller
{
    /**
     * 1×1 GIF tracking pixel — records open and returns a transparent GIF.
     * Route: GET /newsletter/pixel/{token}.gif
     */
    public function pixel(string $token): Response
    {
        $send = NewsletterCampaignSend::where('open_token', $token)->first();

        if ($send && $send->opened_at === null) {
            $send->update(['opened_at' => now()]);
            $send->campaign->increment('opens_count');
        }

        // Minimal 1×1 transparent GIF (43 bytes)
        $gif = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');

        return response($gif, 200, [
            'Content-Type'  => 'image/gif',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'        => 'no-cache',
        ]);
    }
}
