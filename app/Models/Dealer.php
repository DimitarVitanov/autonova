<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Dealer extends Model
{
    protected $fillable = [
        'user_id', 'name', 'slug', 'city', 'address', 'founded_year', 'verified',
        'phone', 'website', 'hours', 'about', 'logo_path', 'cover_path',
        'package', 'package_until', 'promo_credits', 'rating',
    ];

    protected $appends = ['logo_url', 'cover_url'];

    protected function casts(): array
    {
        return [
            'verified' => 'boolean',
            'package_until' => 'date',
            'rating' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path ? Storage::url($this->logo_path) : null;
    }

    public function getCoverUrlAttribute(): ?string
    {
        return $this->cover_path ? Storage::url($this->cover_path) : null;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
