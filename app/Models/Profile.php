<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'bio',
        'avatar_url',
        'city',
        'country',
        'country_id',
        'job_title',
        'company',
        'sector_id',
        'education_start_year',
        'education_end_year',
        'linkedin_url',
        'portfolio_url',
        'phone',
        'show_phone',
        'email_contact',
        'show_email_contact',
        'is_verified',
        'verified_at',
    ];

    // full_name is a Postgres STORED generated column (first_name || ' ' || last_name);
    // Eloquent loads it like any other attribute — do not put it in $fillable.

    protected $casts = [
        'is_verified'        => 'boolean',
        'verified_at'        => 'datetime',
        'show_phone'         => 'boolean',
        'show_email_contact' => 'boolean',
    ];

    // Relations

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    public function countryRelation(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'profile_skills');
    }

    // Scopes

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('is_verified', true);
    }

    public function scopeInSector(Builder $query, ?int $sectorId): Builder
    {
        return $query->when($sectorId, fn (Builder $q) => $q->where('sector_id', $sectorId));
    }

    public function scopeInCountry(Builder $query, ?string $country): Builder
    {
        return $query->when($country, fn (Builder $q) => $q->where('country', $country));
    }

    public function scopeWithEducationYears(Builder $query, ?int $from, ?int $to): Builder
    {
        return $query
            ->when($from, fn (Builder $q) => $q->where('education_end_year', '>=', $from))
            ->when($to,   fn (Builder $q) => $q->where('education_end_year', '<=', $to));
    }

    public function scopeWithSkills(Builder $query, array $skillIds): Builder
    {
        return $query->when(
            $skillIds,
            fn (Builder $q) => $q->whereHas(
                'skills',
                fn (Builder $sq) => $sq->whereIn('skill_id', $skillIds)
            )
        );
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when(
            $search,
            fn (Builder $q) => $q->whereRaw(
                "to_tsvector('simple', coalesce(full_name,'') || ' ' || coalesce(bio,'') || ' ' || coalesce(job_title,'') || ' ' || coalesce(company,'')) @@ plainto_tsquery('simple', ?)",
                [$search]
            )
        );
    }
}
