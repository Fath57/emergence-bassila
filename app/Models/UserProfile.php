<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone',
        'quartier_origine',
        'ville_actuelle',
        'pays_actuel',
        'bio',
        'profession',
        'competences',
        'formation',
        'experience',
        'domaine_expertise',
        'linkedin',
        'facebook',
        'twitter',
        'photo',
        'visible_annuaire',
        'disponible_opportunites',
    ];

    protected $casts = [
        'visible_annuaire' => 'boolean',
        'disponible_opportunites' => 'boolean',
    ];

    /**
     * Get the user that owns the profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
