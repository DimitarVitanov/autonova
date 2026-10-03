<?php

namespace Database\Seeders;

use App\Models\CarModel;
use App\Models\Category;
use App\Models\Make;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the version / trim options offered per model in the sell form, from
 * database/data/versions/*.php ([category slug => [make => [model => [versions]]]]).
 *
 * Idempotent: existing versions are kept, only missing ones are added.
 */
class ModelVersionSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $added = 0;

        foreach (glob(database_path('data/versions/*.php')) as $file) {
            foreach (require $file as $categorySlug => $makes) {
                $categoryId = Category::where('slug', $categorySlug)->value('id');

                foreach ($makes as $makeName => $models) {
                    $makeId = Make::where('category_id', $categoryId)->where('name', $makeName)->value('id');
                    if (! $makeId) {
                        $this->command?->warn("Unknown make: {$categorySlug} / {$makeName}");

                        continue;
                    }

                    foreach ($models as $modelName => $versions) {
                        $modelName = (string) $modelName; // PHP casts keys like '208' to int
                        $modelId = CarModel::where('make_id', $makeId)->where('name', $modelName)->value('id');
                        if (! $modelId) {
                            $this->command?->warn("Unknown model: {$makeName} {$modelName}");

                            continue;
                        }

                        $rows = [];
                        foreach (array_values(array_unique($versions)) as $sort => $name) {
                            $rows[] = [
                                'car_model_id' => $modelId, 'name' => $name, 'sort' => $sort,
                                'created_at' => $now, 'updated_at' => $now,
                            ];
                        }
                        $added += DB::table('model_versions')->insertOrIgnore($rows);
                    }
                }
            }
        }

        $this->command?->info("{$added} versions added.");
    }
}
