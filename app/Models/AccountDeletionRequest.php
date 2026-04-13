<?php

namespace App\Models;

use Database\Factories\AccountDeletionRequestFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use LogicException;

class AccountDeletionRequest extends Model
{
    /** @use HasFactory<AccountDeletionRequestFactory> */
    use HasFactory;

    public const GRACE_DAYS = 30;
    public const TOKEN_TTL_HOURS = 24;

    protected $fillable = [
        'user_id',
        'status',
        'confirmation_token',
        'requested_at',
        'confirmed_at',
        'scheduled_purge_at',
        'cancelled_at',
        'cancelled_by',
        'cancel_reason',
        'admin_notes',
        'purged_at',
    ];

    protected $casts = [
        'requested_at'       => 'datetime',
        'confirmed_at'       => 'datetime',
        'scheduled_purge_at' => 'datetime',
        'cancelled_at'       => 'datetime',
        'purged_at'          => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public static function startFor(User $user): self
    {
        return self::create([
            'user_id'            => $user->id,
            'status'             => 'requested',
            'confirmation_token' => Str::random(64),
            'requested_at'       => now(),
        ]);
    }

    public function confirm(): void
    {
        if ($this->status !== 'requested') {
            throw new LogicException("Cannot confirm from status {$this->status}");
        }

        $this->update([
            'status'             => 'confirmed',
            'confirmed_at'       => now(),
            'confirmation_token' => null,
            'scheduled_purge_at' => now()->addDays(self::GRACE_DAYS),
        ]);
    }

    public function cancel(User $by, ?string $reason = null): void
    {
        if (! in_array($this->status, ['requested', 'confirmed'], true)) {
            throw new LogicException("Cannot cancel from status {$this->status}");
        }

        $this->update([
            'status'        => 'cancelled',
            'cancelled_at'  => now(),
            'cancelled_by'  => $by->id,
            'cancel_reason' => $reason,
        ]);
    }

    public function markPurged(): void
    {
        $this->update([
            'status'             => 'purged',
            'purged_at'          => now(),
            'confirmation_token' => null,
        ]);
    }

    public function isTokenExpired(): bool
    {
        return $this->requested_at->diffInHours(now()) >= self::TOKEN_TTL_HOURS;
    }

    public function scopeRequested(Builder $q): Builder
    {
        return $q->where('status', 'requested');
    }

    public function scopeConfirmed(Builder $q): Builder
    {
        return $q->where('status', 'confirmed');
    }

    public function scopeDueForPurge(Builder $q): Builder
    {
        return $q->where('status', 'confirmed')
            ->whereNotNull('scheduled_purge_at')
            ->where('scheduled_purge_at', '<=', now())
            ->whereNull('cancelled_at');
    }
}
