<?php

namespace App\Livewire\Admin;

use App\Models\BlogComment;
use App\Models\BlogPost;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use App\Models\Profile;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Dashboard extends Component
{
    #[Layout('layouts.admin')]
    public function render()
    {
        $this->authorize('admin.access');

        $stats = [
            'members'         => User::count(),
            'members_month'   => User::whereMonth('created_at', now()->month)
                                     ->whereYear('created_at', now()->year)
                                     ->count(),
            'profiles'        => Profile::count(),
            'profiles_verified' => Profile::where('is_verified', true)->count(),
            'profiles_pending' => Profile::where('is_verified', false)->count(),
            'posts_published' => BlogPost::published()->count(),
            'posts_month'     => BlogPost::published()
                                         ->whereMonth('published_at', now()->month)
                                         ->whereYear('published_at', now()->year)
                                         ->count(),
            'comments_pending'     => BlogComment::whereNull('moderated_at')->count(),
            'newsletter_active'    => NewsletterSubscriber::active()->count(),
            'newsletter_pending'   => NewsletterSubscriber::whereNull('confirmed_at')->whereNull('unsubscribed_at')->count(),
            'newsletter_campaigns' => NewsletterCampaign::where('status', 'sent')->count(),
        ];

        return view('livewire.admin.dashboard', compact('stats'));
    }
}
