<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Village extends Model
{
    protected $fillable = ['name', 'arrondissement', 'is_active', 'sort_order'];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    // Requires village_id FK on profiles table (added in add_gender_whatsapp_village_to_profiles migration).
    public function profiles(): HasMany
    {
        return $this->hasMany(Profile::class);
    }
}
