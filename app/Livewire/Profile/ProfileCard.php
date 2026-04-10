<?php

namespace App\Livewire\Profile;

use App\Models\Profile;
use Livewire\Component;

class ProfileCard extends Component
{
    public Profile $profile;

    public function render()
    {
        return view('livewire.profile.profile-card');
    }
}
