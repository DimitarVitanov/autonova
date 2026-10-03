<?php

namespace Database\Seeders;

use App\Models\Favorite;
use App\Models\SavedSearch;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class EngagementSeeder extends Seeder
{
    public function run(): void
    {
        $marko = User::where('email', 'marko@autonova.test')->first();
        if (! $marko) {
            return;
        }

        Vehicle::active()->inRandomOrder()->limit(4)->get()->each(function ($vehicle) use ($marko) {
            Favorite::firstOrCreate(['user_id' => $marko->id, 'vehicle_id' => $vehicle->id]);
        });

        $searches = [
            [
                'title' => 'Passat estate under €15,000',
                'filters' => ['category' => 'cars', 'q' => 'Passat', 'year_min' => 2015, 'fuel' => ['diesel'], 'price_max' => 15000, 'city' => 'Skopje'],
                'new_count' => 7,
            ],
            [
                'title' => 'Vans up to 3.5 t',
                'filters' => ['category' => 'vans', 'mileage_max' => 250000],
                'new_count' => 3,
            ],
            [
                'title' => 'Tractors over 90 hp',
                'filters' => ['category' => 'machinery', 'power_min' => 90, 'year_min' => 1998],
                'new_count' => 1,
            ],
        ];

        foreach ($searches as $s) {
            SavedSearch::updateOrCreate(
                ['user_id' => $marko->id, 'title' => $s['title']],
                ['filters' => $s['filters'], 'new_count' => $s['new_count'], 'alerts' => true]
            );
        }
    }
}
