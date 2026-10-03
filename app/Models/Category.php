<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['slug', 'name', 'name_plural', 'hint', 'icon', 'sort'];

    public function makes(): HasMany
    {
        return $this->hasMany(Make::class);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
