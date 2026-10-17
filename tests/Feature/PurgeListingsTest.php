<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\ImageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PurgeListingsTest extends TestCase
{
    use RefreshDatabase;

    private function listing(User $seller, string $slug): Vehicle
    {
        $category = Category::firstOrCreate(['slug' => 'cars'], ['name' => 'Car', 'name_plural' => 'Cars', 'sort' => 0]);

        return Vehicle::create([
            'user_id' => $seller->id, 'category_id' => $category->id, 'title' => 'Test Car',
            'slug' => $slug, 'price' => 5000, 'year' => 2015, 'mileage_km' => 100000,
            'fuel' => 'petrol', 'transmission' => 'manual', 'condition' => 'used',
            'city' => 'Skopje', 'seller_type' => 'private', 'status' => 'active',
        ]);
    }

    public function test_deletes_every_listing_including_trashed_and_all_photo_files_but_keeps_profiles(): void
    {
        Storage::fake('public');
        $seller = User::factory()->create();

        $active = $this->listing($seller, 'active-car');
        $active->images()->create(app(ImageService::class)->storeListingImage(UploadedFile::fake()->image('car.jpg', 1600, 1200)));
        $this->listing($seller, 'trashed-car')->delete();
        Storage::disk('public')->put('vehicles/orphaned-by-old-rebrand.webp', 'x');

        $this->artisan('listings:purge', ['--force' => true])->assertSuccessful();

        $this->assertSame(0, Vehicle::withTrashed()->count());
        $this->assertDatabaseEmpty('vehicle_images');
        $this->assertSame([], Storage::disk('public')->allFiles());
        $this->assertModelExists($seller);
    }

    public function test_does_not_delete_anything_unless_confirmed(): void
    {
        $this->listing(User::factory()->create(), 'kept-car');

        $this->artisan('listings:purge')
            ->expectsConfirmation('Permanently delete all 1 listings and their photos?', 'no')
            ->assertFailed();

        $this->assertSame(1, Vehicle::count());
    }
}
