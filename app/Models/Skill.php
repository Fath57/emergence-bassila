<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Skill extends Model
{
    public $timestamps = false;

    protected $fillable = ['name', 'category', 'sort_order'];

    public function profiles(): BelongsToMany
    {
        return $this->belongsToMany(Profile::class, 'profile_skills');
    }
}
