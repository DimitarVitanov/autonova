<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CarModel extends Model
{
    protected $table = 'car_models';

    protected $fillable = ['make_id', 'name', 'slug'];

    public function make(): BelongsTo
    {
        return $this->belongsTo(Make::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ModelVersion::class)->orderBy('sort');
    }
}
