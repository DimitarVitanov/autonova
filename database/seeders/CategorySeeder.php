<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['slug' => 'cars', 'name' => 'Car', 'name_plural' => 'Cars', 'hint' => 'Passenger vehicles', 'icon' => 'car'],
            ['slug' => 'motorcycles', 'name' => 'Motorcycle', 'name_plural' => 'Motorcycles', 'hint' => 'Motorcycles & scooters', 'icon' => 'motorcycle'],
            ['slug' => 'vans', 'name' => 'Van', 'name_plural' => 'Vans', 'hint' => 'Up to 3.5 t', 'icon' => 'van'],
            ['slug' => 'trucks', 'name' => 'Truck', 'name_plural' => 'Trucks', 'hint' => 'Commercial trucks', 'icon' => 'truck'],
            ['slug' => 'machinery', 'name' => 'Machinery', 'name_plural' => 'Machinery', 'hint' => 'Agricultural & construction', 'icon' => 'tractor'],
            ['slug' => 'trailers', 'name' => 'Trailer', 'name_plural' => 'Trailers', 'hint' => 'Trailers & campers', 'icon' => 'trailer'],
        ];

        foreach ($categories as $i => $c) {
            Category::updateOrCreate(['slug' => $c['slug']], array_merge($c, ['sort' => $i]));
        }
    }
}
