<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = require database_path('data/vehicle_catalog.php');
        $now = now();

        foreach ($catalog as $categorySlug => $makes) {
            $category = Category::where('slug', $categorySlug)->first();
            if (! $category) {
                continue;
            }

            foreach ($makes as $make) {
                $makeId = DB::table('makes')->insertGetId([
                    'category_id' => $category->id,
                    'name' => $make['name'],
                    'slug' => Str::slug($make['name']),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $rows = [];
                foreach ($make['models'] as $model) {
                    $rows[] = [
                        'make_id' => $makeId,
                        'name' => $model,
                        'slug' => Str::slug($model),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                foreach (array_chunk($rows, 200) as $chunk) {
                    DB::table('car_models')->insert($chunk);
                }
            }
        }
    }
}
