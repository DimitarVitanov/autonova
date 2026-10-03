<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedSearch extends Model
{
    protected $fillable = ['user_id', 'title', 'filters', 'new_count', 'alerts'];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'alerts' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
