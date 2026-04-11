<?php

namespace App\Http\Controllers;

use App\Models\Profile;

class ProfileController extends Controller
{
    /**
     * Display the profile page.
     */
    public function show(Profile $profile)
    {
        $profile->loadMissing(['skills', 'sector', 'user']);

        abort_if(! $profile->user || ! $profile->user->is_active, 404);

        return view('profile.show', compact('profile'));
    }
}
