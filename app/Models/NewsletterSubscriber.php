<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class NewsletterSubscriber extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'email',
        'first_name',
        'confirmation_token',
        'unsubscribe_token',
        'confirmed_at',
        'unsubscribed_at',
        'source',
        'last_sent_at',
    ];

    protected $casts = [
        'confirmed_at'    => 'datetime',
        'unsubscribed_at' => 'datetime',
        'last_sent_at'    => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (NewsletterSubscriber $sub) {
            if (empty($sub->unsubscribe_token)) {
                $sub->unsubscribe_token = Str::random(64);
            }
            if (empty($sub->confirmation_token)) {
                $sub->confirmation_token = Str::random(64);
            }
        });
    }

    public function isConfirmed(): bool
    {
        return $this->confirmed_at !== null && $this->unsubscribed_at === null;
    }

    public function isPending(): bool
    {
        return $this->confirmed_at === null
            && $this->unsubscribed_at === null
            && $this->created_at !== null
            && $this->created_at->gt(now()->subDays(7));
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotNull('confirmed_at')->whereNull('unsubscribed_at');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['email', 'confirmed_at', 'unsubscribed_at'])
            ->logOnlyDirty()
            ->useLogName('newsletter');
    }
}
