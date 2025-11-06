<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'category', 'description'];

    // Utilisateurs ayant cette compétence
    public function users()
    {
        return $this->belongsToMany(User::class)
                    ->withPivot('level', 'years_experience')
                    ->withTimestamps();
    }
}
