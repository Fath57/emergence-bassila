<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'is_approved',
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
        'password' => 'hashed',
        'is_admin' => 'boolean',
        'is_approved' => 'boolean',
    ];

    /**
     * Get the profile for the user.
     */
    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Get the skills for the user.
     */
    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'skill_user')
                    ->withPivot('level')
                    ->withTimestamps();
    }

    /**
     * Get the news created by the user.
     */
    public function news()
    {
        return $this->hasMany(News::class, 'author_id');
    }

    /**
     * Get the gallery items uploaded by the user.
     */
    public function galleryItems()
    {
        return $this->hasMany(Gallery::class);
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin()
    {
        return $this->is_admin;
    }

    /**
     * Check if user is approved.
     */
    public function isApproved()
    {
        return $this->is_approved;
    }

    /**
     * Scope a query to only include approved users.
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }
}
