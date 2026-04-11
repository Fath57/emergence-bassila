<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Setting extends Model
{
    use LogsActivity;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
        'sort_order',
        'updated_by',
    ];

    /**
     * Runtime cast of `value` based on the `type` column.
     *
     * Extending to new types (int, json, text) only requires adding a match
     * branch here — no schema migration is needed since the `type` column is
     * varchar(20), not an enum.
     */
    public function getCastedValueAttribute(): mixed
    {
        return match ($this->type) {
            'bool'   => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'string' => (string) $this->value,
            default  => $this->value,
        };
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['value'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $event) => "setting.{$event}")
            ->useLogName('settings');
    }

    protected static function booted(): void
    {
        $invalidate = function () {
            Cache::forget('settings.all');
            if (app()->bound('settings.memo')) {
                app()->forgetInstance('settings.memo');
            }
        };

        static::saved($invalidate);
        static::deleted($invalidate);
    }
}
