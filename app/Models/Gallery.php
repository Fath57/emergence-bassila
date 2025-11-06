<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    use HasFactory;

    protected $table = 'gallery';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'image_path',
        'category',
        'is_approved',
        'likes',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'likes' => 'integer',
    ];

    /**
     * Get the user that uploaded the image.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include approved images.
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }
}
