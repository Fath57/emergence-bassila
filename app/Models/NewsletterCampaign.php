<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class NewsletterCampaign extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'subject',
        'content',
        'preview_text',
        'status',
        'created_by',
        'batch_id',
        'recipients_count',
        'sent_count',
        'opens_count',
        'unsubscribes_count',
        'sent_at',
        'finished_at',
    ];

    protected $casts = [
        'sent_at'     => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sends(): HasMany
    {
        return $this->hasMany(NewsletterCampaignSend::class, 'campaign_id');
    }

    public function isEditable(): bool
    {
        return $this->status === 'draft';
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, ['draft', 'failed'], true);
    }

    public function openRate(): float
    {
        return $this->sent_count > 0
            ? round(($this->opens_count / $this->sent_count) * 100, 1)
            : 0.0;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['subject', 'status', 'sent_at'])
            ->logOnlyDirty()
            ->useLogName('newsletter');
    }
}
