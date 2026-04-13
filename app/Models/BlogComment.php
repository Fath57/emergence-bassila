<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogComment extends Model
{
    use HasFactory;

    // Only created_at, no updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'blog_post_id',
        'user_id',
        'content',
        'moderated_at',
        'author_display_name',
    ];

    protected $casts = [
        'moderated_at' => 'datetime',
    ];

    // Relations

    public function post(): BelongsTo
    {
        return $this->belongsTo(BlogPost::class, 'blog_post_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes

    public function scopeApproved(Builder $query): Builder
    {
        return $query->whereNotNull('moderated_at');
    }

    // Accessors

    public function getDisplayAuthorNameAttribute(): string
    {
        if ($this->user) {
            return trim($this->user->first_name.' '.$this->user->last_name) ?: $this->user->email;
        }

        return $this->author_display_name ?? 'Membre supprimé';
    }
}
