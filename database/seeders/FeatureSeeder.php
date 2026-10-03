<?php

namespace Database\Seeders;

use App\Models\Feature;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FeatureSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            'Comfort' => [
                'Air conditioning', 'Climate control', 'Heated seats', 'Leather seats',
                'Electric seats', 'Panoramic roof', 'Keyless entry', 'Cruise control',
                'Adaptive cruise control', 'Electric windows', 'Start/stop',
            ],
            'Safety' => [
                'ABS', 'ESP', 'Multiple airbags', 'Lane assist', 'Blind spot monitor',
                'Parking sensors', 'Rear camera', '360° camera', 'Emergency braking', 'Isofix',
            ],
            'Multimedia' => [
                'Navigation', 'Apple CarPlay', 'Android Auto', 'Bluetooth',
                'DAB radio', 'Premium sound', 'Head-up display', 'Wireless charging',
            ],
            'Extras' => [
                'LED headlights', 'Xenon headlights', 'Alloy wheels', 'Tow bar',
                'Roof rails', 'Service book', 'First owner', 'Non-smoker', 'Winter tyres',
            ],
        ];

        $sort = 0;
        foreach ($groups as $group => $names) {
            foreach ($names as $name) {
                Feature::updateOrCreate(
                    ['slug' => Str::slug($name)],
                    ['name' => $name, 'group' => $group, 'sort' => $sort++]
                );
            }
        }
    }
}
