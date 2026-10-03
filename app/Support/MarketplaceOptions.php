<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Make;
use App\Models\ModelVersion;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Cache;

/**
 * Builds the option lists (makes, models, fuels, cities, …) the marketplace UI
 * needs for its filters and the add-listing wizard, kept in one place so the
 * search page, dealer page and wizard stay consistent.
 */
class MarketplaceOptions
{
    public static function config(): array
    {
        return [
            'fuels' => self::pairs(config('marketplace.fuels')),
            'transmissions' => self::pairs(config('marketplace.transmissions')),
            'drivetrains' => self::pairs(config('marketplace.drivetrains')),
            'vat' => self::pairs(config('marketplace.vat')),
            'conditions' => self::pairs(config('marketplace.conditions')),
            'seller_types' => self::pairs(config('marketplace.seller_types')),
            'colors' => config('marketplace.colors'),
            'cities' => config('marketplace.cities'),
            'sorts' => self::pairs(config('marketplace.sorts')),
            'body_types' => config('marketplace.body_types'),
            'years' => range((int) date('Y') + 1, 1990),
        ];
    }

    /** Makes for a category, [{id,name}]. */
    public static function makes(?string $categorySlug): array
    {
        if (! $categorySlug) {
            return [];
        }

        return Cache::remember("options.makes.{$categorySlug}", 300, function () use ($categorySlug) {
            $category = Category::where('slug', $categorySlug)->first();
            if (! $category) {
                return [];
            }

            return Make::where('category_id', $category->id)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->toArray();
        });
    }

    /** Models for a make, [{id,name}]. */
    public static function models(?int $makeId): array
    {
        if (! $makeId) {
            return [];
        }

        return \App\Models\CarModel::where('make_id', $makeId)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();
    }

    /**
     * Version / trim names for a model: the curated catalogue first, then any
     * other version sellers have already used on a published listing.
     *
     * @return array<int, string>
     */
    public static function versions(?int $modelId): array
    {
        if (! $modelId) {
            return [];
        }

        $curated = ModelVersion::where('car_model_id', $modelId)->orderBy('sort')->pluck('name');

        $used = Vehicle::where('car_model_id', $modelId)
            ->whereIn('status', ['active', 'sold'])
            ->whereNotNull('version')->where('version', '!=', '')
            ->distinct()->orderBy('version')->pluck('version');

        return $curated->concat($used)->unique(fn ($v) => mb_strtolower($v))->values()->all();
    }

    /** All makes grouped by category id, for the add-listing wizard. */
    public static function makesByCategory(): array
    {
        return Cache::remember('options.makesByCategory', 300, function () {
            return Make::orderBy('name')
                ->get(['id', 'name', 'category_id'])
                ->groupBy('category_id')
                ->map(fn ($makes) => $makes->map(fn ($m) => ['id' => $m->id, 'name' => $m->name])->values())
                ->toArray();
        });
    }

    private static function pairs(array $map): array
    {
        $out = [];
        foreach ($map as $value => $label) {
            $out[] = ['value' => $value, 'label' => $label];
        }
        return $out;
    }
}
