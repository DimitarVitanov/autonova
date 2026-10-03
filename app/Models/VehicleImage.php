<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class VehicleImage extends Model
{
    protected $fillable = ['vehicle_id', 'path', 'thumb_path', 'width', 'height', 'sort', 'is_cover'];

    protected $appends = ['url', 'thumb_url'];

    protected function casts(): array
    {
        return ['is_cover' => 'boolean'];
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
}
