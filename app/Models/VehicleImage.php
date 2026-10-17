<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class VehicleImage extends Model
{
    protected $fillable = ['vehicle_id', 'path', 'thumb_path', 'variants', 'width', 'height', 'sort', 'is_cover'];

    protected $appends = ['url', 'thumb_url', 'srcset'];

    protected function casts(): array
    {
        return [
            'is_cover' => 'boolean',
            'variants' => 'array',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->path);
    }

    public function getThumbUrlAttribute(): string
    {
        return Storage::url($this->thumb_path ?: $this->path);
    }

    /**
     * "url 400w, url 640w, …" over the stored WebP renditions, so the browser
     * downloads the smallest one that is sharp on the visitor's screen.
     */
    public function getSrcsetAttribute(): ?string
    {
        if (empty($this->variants)) {
            return null;
        }

        return collect($this->variants)
            ->sortKeys()
            ->map(fn (string $path, int $width) => Storage::url($path)." {$width}w")
            ->implode(', ');
    }

    /**
     * Every file this photo occupies on the public disk.
     *
     * @return list<string>
     */
    public function storedFiles(): array
    {
        return array_values(array_unique(array_filter([
            $this->path,
            $this->thumb_path,
            ...array_values($this->variants ?? []),
        ])));
    }
}
