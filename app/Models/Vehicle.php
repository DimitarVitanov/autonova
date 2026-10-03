<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    /** Reference rate used to show an MKD price alongside EUR. */
    public const MKD_RATE = 61.5;

    protected $fillable = [
        'user_id', 'dealer_id', 'category_id', 'make_id', 'car_model_id',
        'title', 'slug', 'version', 'price', 'vat',
        'year', 'mileage_km', 'fuel', 'transmission', 'engine_cc', 'power_hp',
        'drivetrain', 'body_type', 'doors', 'seats', 'color', 'condition', 'owners',
        'registered_until', 'city', 'description', 'seller_type', 'contact_phone',
        'status', 'reject_reason', 'promotion', 'promoted_until', 'is_featured',
        'views', 'bumped_at', 'published_at',
    ];

    protected $appends = ['price_mkd', 'cover_url'];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'promoted_until' => 'datetime',
            'bumped_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    // ---- Relationships -----------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function make(): BelongsTo
    {
        return $this->belongsTo(Make::class);
    }

    public function carModel(): BelongsTo
    {
        return $this->belongsTo(CarModel::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(VehicleImage::class)->orderBy('sort');
    }

    public function coverImage(): HasOne
    {
        return $this->hasOne(VehicleImage::class)->orderByDesc('is_cover')->orderBy('sort');
    }

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    // ---- Accessors ---------------------------------------------------------

    public function getPriceMkdAttribute(): int
    {
        return (int) round($this->price * self::MKD_RATE);
    }

    public function getCoverUrlAttribute(): ?string
    {
        $image = $this->relationLoaded('coverImage')
            ? $this->coverImage
            : ($this->relationLoaded('images') ? $this->images->first() : null);

        return $image?->url;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // ---- Scopes ------------------------------------------------------------

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopePromoted(Builder $query): Builder
    {
        return $query->whereIn('promotion', ['featured', 'homepage']);
    }

    /**
     * Apply marketplace search filters from a request array.
     */
    public function scopeFilter(Builder $query, array $f): Builder
    {
        $query->when($f['category'] ?? null, fn ($q, $v) =>
            $q->whereHas('category', fn ($c) => $c->where('slug', $v)));

        $query->when($f['make_id'] ?? null, fn ($q, $v) => $q->where('make_id', $v));
        $query->when($f['car_model_id'] ?? null, fn ($q, $v) => $q->where('car_model_id', $v));

        $query->when($f['price_min'] ?? null, fn ($q, $v) => $q->where('price', '>=', (int) $v));
        $query->when($f['price_max'] ?? null, fn ($q, $v) => $q->where('price', '<=', (int) $v));
        $query->when($f['year_min'] ?? null, fn ($q, $v) => $q->where('year', '>=', (int) $v));
        $query->when($f['year_max'] ?? null, fn ($q, $v) => $q->where('year', '<=', (int) $v));
        $query->when($f['mileage_max'] ?? null, fn ($q, $v) => $q->where('mileage_km', '<=', (int) $v));
        $query->when($f['power_min'] ?? null, fn ($q, $v) => $q->where('power_hp', '>=', (int) $v));
        $query->when($f['power_max'] ?? null, fn ($q, $v) => $q->where('power_hp', '<=', (int) $v));

        $query->when($f['fuel'] ?? null, fn ($q, $v) => $q->whereIn('fuel', (array) $v));
        $query->when($f['transmission'] ?? null, fn ($q, $v) => $q->where('transmission', $v));
        $query->when($f['drivetrain'] ?? null, fn ($q, $v) => $q->where('drivetrain', $v));
        $query->when($f['body_type'] ?? null, fn ($q, $v) => $q->where('body_type', $v));
        $query->when($f['condition'] ?? null, fn ($q, $v) => $q->where('condition', $v));
        $query->when(($f['seller_type'] ?? null) && $f['seller_type'] !== 'all',
            fn ($q) => $q->where('seller_type', $f['seller_type']));
        $query->when($f['city'] ?? null, fn ($q, $v) => $q->where('city', $v));

        $query->when($f['features'] ?? null, function ($q, $v) {
            foreach ((array) $v as $slug) {
                $q->whereHas('features', fn ($fq) => $fq->where('slug', $slug));
            }
        });

        $query->when($f['q'] ?? null, function ($q, $term) {
            $q->where(fn ($sub) => $sub
                ->where('title', 'like', "%{$term}%")
                ->orWhere('version', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%"));
        });

        return $query;
    }

    public function scopeSortBy(Builder $query, ?string $sort): Builder
    {
        return match ($sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'mileage_asc' => $query->orderBy('mileage_km'),
            'year_desc' => $query->orderByDesc('year'),
            'power_desc' => $query->orderByDesc('power_hp'),
            default => $query->orderByDesc('is_featured')->orderByDesc('bumped_at')->orderByDesc('published_at'),
        };
    }
}
