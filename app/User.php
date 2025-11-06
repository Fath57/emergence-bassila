<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
        'first_name',
        'last_name',
        'phone',
        'birth_date',
        'gender',
        'profile_photo',
        'cover_photo',
        'village_origin',
        'quartier',
        'current_city',
        'current_country',
        'current_address',
        'current_profession',
        'current_company',
        'professional_status',
        'bio',
        'skills_summary',
        'linkedin_url',
        'facebook_url',
        'twitter_url',
        'open_to_opportunities',
        'interests',
        'status',
        'role',
        'profile_visible',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'birth_date' => 'date',
        'approved_at' => 'datetime',
        'password' => 'hashed',
        'open_to_opportunities' => 'boolean',
        'profile_visible' => 'boolean',
    ];

    /**
     * Relations
     */

    // Compétences de l'utilisateur (Many to Many)
    public function skills()
    {
        return $this->belongsToMany(Skill::class)
                    ->withPivot('level', 'years_experience')
                    ->withTimestamps();
    }

    // Expériences professionnelles
    public function experiences()
    {
        return $this->hasMany(Experience::class);
    }

    // Formations
    public function educations()
    {
        return $this->hasMany(Education::class);
    }

    // Opportunités postées
    public function opportunities()
    {
        return $this->hasMany(Opportunity::class);
    }

    // Actualités/Articles postés
    public function news()
    {
        return $this->hasMany(News::class);
    }

    // Messages envoyés
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    // Messages reçus
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'recipient_id');
    }

    /**
     * Accesseurs
     */

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getIsActiveAttribute()
    {
        return $this->status === 'active';
    }

    public function getIsAdminAttribute()
    {
        return $this->role === 'admin';
    }

    /**
     * Scopes
     */

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeVisible($query)
    {
        return $query->where('profile_visible', true);
    }

    public function scopeByLocation($query, $country = null, $city = null)
    {
        if ($country) {
            $query->where('current_country', $country);
        }
        if ($city) {
            $query->where('current_city', $city);
        }
        return $query;
    }
}
