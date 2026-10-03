<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Make extends Model
{
    protected $fillable = ['category_id', 'name', 'slug'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function models(): HasMany
    {
        return $this->hasMany(CarModel::class);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }
}
