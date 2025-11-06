<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Opportunity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'category_id', 'title', 'type', 'description',
        'requirements', 'location', 'remote_possible', 'company_name',
        'company_website', 'contract_type', 'salary_range',
        'experience_required', 'contact_email', 'contact_phone',
        'application_url', 'deadline', 'start_date', 'status',
        'is_featured', 'views_count'
    ];

    protected $casts = [
        'remote_possible' => 'boolean',
        'is_featured' => 'boolean',
        'deadline' => 'date',
        'start_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}
