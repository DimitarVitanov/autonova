<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One site-wide key/value switch, edited by admins.
 */
class Setting extends Model
{
    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['key', 'value'];
}
