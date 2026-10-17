<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;

/**
 * The "free for everyone" launch period: while it runs, every dealer package
 * and listing promotion is shown as free. Admins switch it on at
 * /admin/settings with an end date, after which it ends by itself.
 */
class LaunchOffer
{
    private const ENABLED = 'launch_offer.enabled';

    private const UNTIL = 'launch_offer.until';

    private const CACHE_KEY = 'settings.launch_offer';

    /**
     * @return array{enabled: bool, until: ?string}
     */
    public static function settings(): array
    {
        return Cache::remember(self::CACHE_KEY, 300, function () {
            $values = Setting::whereIn('key', [self::ENABLED, self::UNTIL])->pluck('value', 'key');

            return [
                'enabled' => (bool) ($values[self::ENABLED] ?? false),
                'until' => $values[self::UNTIL] ?? null,
            ];
        });
    }

    /**
     * Switched on and the end date (inclusive) has not passed yet.
     */
    public static function isActive(): bool
    {
        ['enabled' => $enabled, 'until' => $until] = self::settings();

        return $enabled && ($until === null || today()->lte(Carbon::parse($until)));
    }

    /**
     * What the frontend needs to show prices as free.
     *
     * @return array{active: bool, until: ?string, until_label: ?string}
     */
    public static function shared(): array
    {
        $until = self::settings()['until'];

        return [
            'active' => self::isActive(),
            'until' => $until,
            // "17 декември 2026" — formatted here because browsers often lack Macedonian dates.
            'until_label' => $until ? Carbon::parse($until)->locale(App::getLocale())->translatedFormat('j F Y') : null,
        ];
    }

    public static function update(bool $enabled, ?string $until): void
    {
        Setting::updateOrCreate(['key' => self::ENABLED], ['value' => $enabled ? '1' : '0']);
        Setting::updateOrCreate(['key' => self::UNTIL], ['value' => $until]);

        Cache::forget(self::CACHE_KEY);
    }
}
