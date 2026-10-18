<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminListingFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_listing_table_reports_which_cars_are_featured_and_the_button_toggles_it(): void
    {
        $moderator = User::factory()->create(['role' => 'moderator']);
        $category = Category::create(['slug' => 'cars', 'name' => 'Car', 'name_plural' => 'Cars', 'sort' => 0]);
        $car = Vehicle::create([
            'user_id' => User::factory()->create()->id, 'category_id' => $category->id, 'title' => 'Golf',
            'slug' => 'golf', 'price' => 9000, 'year' => 2016, 'mileage_km' => 120000, 'fuel' => 'diesel',
            'transmission' => 'manual', 'condition' => 'used', 'city' => 'Skopje', 'seller_type' => 'private',
            'status' => 'active',
        ]);

        $featuredFlag = fn (bool $expected) => $this->actingAs($moderator)
            ->get(route('admin.listings', ['status' => 'all']))
            ->assertInertia(fn (Assert $page) => $page
                ->where('vehicles.data.0.is_featured', $expected)
                ->where('vehicles.data.0.seller_active', true));

        $featuredFlag(false);

        $this->actingAs($moderator)->post(route('admin.listings.feature', $car));
        $featuredFlag(true);

        $this->actingAs($moderator)->post(route('admin.listings.feature', $car));
        $featuredFlag(false);
    }
}
