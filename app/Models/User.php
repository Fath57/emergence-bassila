<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['first_name', 'last_name', 'email', 'password', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, LogsActivity;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    /**
     * Check whether a user is the last remaining active admin.
     *
     * Used by the anti-lockout guard before role changes and deactivations.
     * Returns false if the target is not an admin or is already inactive —
     * only returns true when demoting or deactivating them would leave
     * zero active admins in the system.
     */
    public static function isLastActiveAdmin(User $target): bool
    {
        if (! $target->hasRole('admin') || ! $target->is_active) {
            return false;
        }

        return User::role('admin')
            ->where('is_active', true)
            ->where('id', '!=', $target->id)
            ->doesntExist();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['email', 'is_active'])
            ->logOnlyDirty()
            ->useLogName('users');
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function blogPosts(): HasMany
    {
        return $this->hasMany(BlogPost::class);
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(ContactMessage::class, 'from_user_id');
    }

    public function receivedMessages(): HasMany
    {
        return $this->hasMany(ContactMessage::class, 'to_user_id');
    }
}
