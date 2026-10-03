<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;

class Translation extends Model
{
    protected $fillable = ['group', 'key', 'en', 'mk'];

    /**
     * All UI strings for the active locale (mk falls back to en), cached.
     *
     * @return array<string, string>
     */
    public static function strings(): array
    {
        return Cache::remember('i18n.strings.'.App::getLocale(), 300, function () {
            $mk = App::getLocale() === 'mk';

            return static::all(['key', 'en', 'mk'])
                ->mapWithKeys(fn ($t) => [
                    $t->key => $mk ? (($t->mk !== null && $t->mk !== '') ? $t->mk : $t->en) : $t->en,
                ])
                ->all();
        });
    }

    /** Server-side twin of the frontend t(): the string for $key, or $fallback. */
    public static function label(string $key, string $fallback): string
    {
        $value = static::strings()[$key] ?? null;

        return $value !== null && $value !== '' ? $value : $fallback;
    }
}
