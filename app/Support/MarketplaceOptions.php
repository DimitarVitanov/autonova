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

    /**
     * Makes of a category with their models nested, for the combined
     * make & model picker.
     *
     * @return array<int, array{id: int, name: string, models: array<int, array{id: int, name: string}>}>
     */
    public static function catalog(?string $categorySlug): array
    {
        if (! $categorySlug) {
            return [];
        }

        return Cache::remember("options.catalog.{$categorySlug}", 300, function () use ($categorySlug) {
            return Make::whereRelation('category', 'slug', $categorySlug)
                ->with(['models' => fn ($query) => $query->orderBy('name')->select(['id', 'make_id', 'name'])])
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Make $make) => [
                    'id' => $make->id,
                    'name' => $make->name,
                    'models' => $make->models->map(fn ($model) => ['id' => $model->id, 'name' => $model->name])->all(),
                ])
                ->all();
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
