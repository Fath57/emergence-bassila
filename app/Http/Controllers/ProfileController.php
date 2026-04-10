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
        $profile->loadMissing(['skills', 'sector']);

        return view('profile.show', compact('profile'));
    }
}
