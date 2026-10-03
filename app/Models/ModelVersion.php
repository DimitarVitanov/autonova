<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModelVersion extends Model
{
    protected $fillable = ['car_model_id', 'name', 'sort'];

    public function carModel(): BelongsTo
    {
        return $this->belongsTo(CarModel::class);
    }
}
