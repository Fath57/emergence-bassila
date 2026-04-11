<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class NewsletterCampaignSend extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'campaign_id',
        'subscriber_id',
        'open_token',
        'sent_at',
        'opened_at',
        'error',
    ];

    protected $casts = [
        'sent_at'   => 'datetime',
        'opened_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (NewsletterCampaignSend $send) {
            if (empty($send->open_token)) {
                $send->open_token = Str::random(32);
            }
        });
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(NewsletterCampaign::class, 'campaign_id');
    }

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(NewsletterSubscriber::class, 'subscriber_id');
    }
}
