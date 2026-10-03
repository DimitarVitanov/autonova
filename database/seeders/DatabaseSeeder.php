<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            CatalogSeeder::class,
            ModelVersionSeeder::class,
            FeatureSeeder::class,
            UserSeeder::class,
            VehicleSeeder::class,
            EngagementSeeder::class,
            MessagingSeeder::class,
            TranslationSeeder::class,
        ]);
    }
}
