<?php

namespace App\Models;

use App\Mail\VerifyEmailMail;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['first_name', 'last_name', 'email', 'password', 'is_active', 'google_id', 'avatar'])]
#[Hidden(['password', 'remember_token', 'google_id'])]
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
     * Override Laravel's default verification notification (English, markdown
     * wrapper) with our branded French mailable.
     */
    public function sendEmailVerificationNotification(): void
    {
        Mail::to($this->email)->queue(new VerifyEmailMail($this));
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

    public function deletionRequest(): HasOne
    {
        return $this->hasOne(AccountDeletionRequest::class);
    }

    public function hasPendingDeletion(): bool
    {
        return $this->deletionRequest()
            ->whereIn('status', ['requested', 'confirmed'])
            ->exists();
    }
}
