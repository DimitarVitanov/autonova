<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Make;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ListingCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_uploaded_images_are_normalised_to_equal_size(): void
    {
        Storage::fake('public');

        $category = Category::create(['slug' => 'cars', 'name' => 'Car', 'name_plural' => 'Cars', 'sort' => 0]);
        $make = Make::create(['category_id' => $category->id, 'name' => 'BMW', 'slug' => 'bmw']);
        $user = User::factory()->create(['role' => 'client', 'account_type' => 'private']);

        // Two very different aspect ratios / sizes:
        $portrait = UploadedFile::fake()->image('portrait.jpg', 900, 1600);
        $panorama = UploadedFile::fake()->image('panorama.jpg', 2400, 800);

        $response = $this->actingAs($user)->post(route('vehicles.store'), [
            'category_id' => $category->id,
            'make_id' => $make->id,
            'price' => 12000,
            'vat' => 'with_vat',
            'year' => 2019,
            'mileage_km' => 90000,
            'fuel' => 'diesel',
            'transmission' => 'automatic',
            'condition' => 'used',
            'city' => 'Skopje',
            'images' => [$portrait, $panorama],
        ]);

        $response->assertRedirect(route('dashboard'));

        $vehicle = Vehicle::with('images')->first();
        $this->assertNotNull($vehicle, 'Vehicle was created');
        $this->assertSame('pending', $vehicle->status, 'New listing awaits moderation');
        $this->assertCount(2, $vehicle->images);

        foreach ($vehicle->images as $img) {
            Storage::disk('public')->assertExists($img->path);
            Storage::disk('public')->assertExists($img->thumb_path);

            // The stored file itself must be exactly 1600x1200, regardless of upload size.
            [$w, $h, $type] = getimagesizefromstring(Storage::disk('public')->get($img->path));
            $this->assertSame(IMAGETYPE_WEBP, $type, 'Photos are stored as WebP');
            $this->assertStringEndsWith('.webp', $img->path);
            $this->assertStringEndsWith('.webp', $img->thumb_path);
            $this->assertSame(1600, $w);
            $this->assertSame(1200, $h);

            [$tw, $th] = getimagesizefromstring(Storage::disk('public')->get($img->thumb_path));
            $this->assertSame(800, $tw);
            $this->assertSame(600, $th);
        }

        $this->assertTrue((bool) $vehicle->images->firstWhere('is_cover', true)->is_cover);
    }

    /** @return array<string, mixed> */
    private function listingPayload(array $images): array
    {
        $category = Category::create(['slug' => 'cars', 'name' => 'Car', 'name_plural' => 'Cars', 'sort' => 0]);

        return [
            'category_id' => $category->id, 'price' => 12000, 'vat' => 'with_vat', 'year' => 2019,
            'mileage_km' => 90000, 'fuel' => 'diesel', 'transmission' => 'automatic',
            'condition' => 'used', 'city' => 'Skopje', 'images' => $images,
        ];
    }

    public function test_every_kind_of_profile_can_add_a_listing(): void
    {
        Storage::fake('public');

        $dealerUser = User::factory()->create(['role' => 'client', 'account_type' => 'dealer']);
        $dealer = $dealerUser->dealer()->create(['name' => 'Test Dealer', 'slug' => 'test-dealer', 'package' => 'start']);

        $users = [
            'private' => User::factory()->create(['role' => 'client', 'account_type' => 'private']),
            'dealer' => $dealerUser,
            'moderator' => User::factory()->create(['role' => 'moderator']),
            'admin' => User::factory()->create(['role' => 'admin']),
        ];

        $payload = $this->listingPayload([]);

        foreach ($users as $kind => $user) {
            $this->actingAs($user)->get(route('vehicles.create'))->assertOk();

            $this->actingAs($user)
                ->post(route('vehicles.store'), ['images' => [UploadedFile::fake()->image('car.png', 2000, 1500)]] + $payload)
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('dashboard'));

            $vehicle = Vehicle::where('user_id', $user->id)->with('images')->first();
            $this->assertNotNull($vehicle, "{$kind} profile created a listing");
            $this->assertCount(1, $vehicle->images);
            $this->assertSame($kind === 'dealer' ? 'dealer' : 'private', $vehicle->seller_type);
            $this->assertSame($kind === 'dealer' ? $dealer->id : null, $vehicle->dealer_id);
        }
    }

    public function test_photos_outside_the_dimension_limits_are_rejected(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'client']);
        $payload = $this->listingPayload([]);

        foreach ([[200, 150], [639, 2000], [2000, 479], [8193, 700]] as [$w, $h]) {
            $this->actingAs($user)
                ->post(route('vehicles.store'), ['images' => [UploadedFile::fake()->image('photo.jpg', $w, $h)]] + $payload)
                ->assertSessionHasErrors('images.0');
        }

        $this->assertSame(0, Vehicle::count());
    }

    public function test_version_options_combine_catalogue_and_published_listings(): void
    {
        $category = Category::create(['slug' => 'cars', 'name' => 'Car', 'name_plural' => 'Cars', 'sort' => 0]);
        $make = Make::create(['category_id' => $category->id, 'name' => 'Volkswagen', 'slug' => 'volkswagen']);
        $golf = $make->models()->create(['name' => 'Golf', 'slug' => 'golf']);
        $polo = $make->models()->create(['name' => 'Polo', 'slug' => 'polo']);
        $golf->versions()->createMany([['name' => '1.6 TDI', 'sort' => 1], ['name' => '1.4 TSI', 'sort' => 0]]);
        $polo->versions()->create(['name' => '1.0 MPI']);

        $listing = fn (string $version, string $status) => Vehicle::create([
            'user_id' => User::factory()->create()->id, 'category_id' => $category->id, 'make_id' => $make->id,
            'car_model_id' => $golf->id, 'title' => 'VW Golf', 'slug' => 'golf-' . uniqid(), 'version' => $version,
            'price' => 5000, 'year' => 2015, 'mileage_km' => 100000, 'fuel' => 'petrol', 'transmission' => 'manual',
            'condition' => 'used', 'city' => 'Skopje', 'seller_type' => 'private', 'status' => $status,
        ]);
        $listing('GTI Clubsport', 'active');
        $listing('1.6 tdi', 'active');      // already in the catalogue
        $listing('Typo version', 'pending'); // not published yet

        $this->getJson(route('api.versions', ['car_model_id' => $golf->id]))
            ->assertOk()
            ->assertExactJson(['1.4 TSI', '1.6 TDI', 'GTI Clubsport']);

        $this->getJson(route('api.versions'))->assertOk()->assertExactJson([]);
    }

    public function test_guests_cannot_create_listings(): void
    {
        $this->post(route('vehicles.store'), [])->assertRedirect(route('login'));
    }

    public function test_moderator_can_approve_a_pending_listing(): void
    {
        $category = Category::create(['slug' => 'cars', 'name' => 'Car', 'name_plural' => 'Cars', 'sort' => 0]);
        $seller = User::factory()->create(['role' => 'client']);
        $moderator = User::factory()->create(['role' => 'moderator']);

        $vehicle = Vehicle::create([
            'user_id' => $seller->id, 'category_id' => $category->id, 'title' => 'Test Car',
            'slug' => 'test-car-abc', 'price' => 5000, 'year' => 2015, 'mileage_km' => 100000,
            'fuel' => 'petrol', 'transmission' => 'manual', 'condition' => 'used',
            'city' => 'Skopje', 'seller_type' => 'private', 'status' => 'pending',
        ]);

        $this->actingAs($moderator)
            ->post(route('admin.listings.approve', $vehicle->slug))
            ->assertRedirect();

        $this->assertSame('active', $vehicle->fresh()->status);
    }

    public function test_client_cannot_reach_admin(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $this->actingAs($client)->get(route('admin.dashboard'))->assertForbidden();
    }
}
