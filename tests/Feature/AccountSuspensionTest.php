<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Dealer;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AccountSuspensionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $salonOwner;

    private Dealer $salon;

    private Vehicle $car;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->salonOwner = User::factory()->create(['role' => 'client', 'account_type' => 'dealer']);
        $this->salon = $this->salonOwner->dealer()->create(['name' => 'Delux Auto', 'slug' => 'delux-auto', 'verified' => true]);

        $category = Category::create(['slug' => 'cars', 'name' => 'Car', 'name_plural' => 'Cars', 'sort' => 0]);
        $this->car = Vehicle::create([
            'user_id' => $this->salonOwner->id, 'dealer_id' => $this->salon->id, 'category_id' => $category->id,
            'title' => 'Audi A4', 'slug' => 'audi-a4', 'price' => 15000, 'year' => 2018, 'mileage_km' => 90000,
            'fuel' => 'diesel', 'transmission' => 'automatic', 'condition' => 'used', 'city' => 'Strumica',
            'seller_type' => 'dealer', 'status' => 'active', 'promotion' => 'featured', 'is_featured' => true,
        ]);
    }

    private function suspendSalonOwner(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.users.toggle', $this->salonOwner))
            ->assertSessionHas('success');

        $this->assertFalse($this->salonOwner->fresh()->is_active);
    }

    private function assertSalonVisible(bool $visible): void
    {
        $count = $visible ? 1 : 0;
        $buyer = User::factory()->create();
        $buyer->favorites()->create(['vehicle_id' => $this->car->id]);

        $this->actingAs($buyer)->get(route('vehicles.index'))
            ->assertInertia(fn (Assert $page) => $page->has('vehicles.data', $count));
        $this->actingAs($buyer)->get(route('home'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('hero', fn ($hero) => $visible ? $hero['slug'] === 'audi-a4' : $hero === null)
                ->has('newest.data', $count)
                ->has('dealers', $count)
                ->where('stats.dealers', $count));
        $this->actingAs($buyer)->get(route('dealers.index'))
            ->assertInertia(fn (Assert $page) => $page->has('dealers.data', $count));
        $this->actingAs($buyer)->get(route('saved'))
            ->assertInertia(fn (Assert $page) => $page->has('vehicles.data', $count));

        $this->actingAs($buyer)->get(route('vehicles.show', $this->car))->assertStatus($visible ? 200 : 404);
        $this->actingAs($buyer)->get(route('dealers.show', $this->salon))->assertStatus($visible ? 200 : 404);
    }

    public function test_suspending_a_seller_hides_their_cars_and_salon_everywhere_until_reactivated(): void
    {
        $this->assertSalonVisible(true);

        $this->suspendSalonOwner();
        $this->assertSalonVisible(false);

        $this->actingAs($this->admin)->post(route('admin.users.toggle', $this->salonOwner));
        $this->assertSalonVisible(true);
    }

    public function test_staff_can_still_open_a_suspended_sellers_car_and_salon(): void
    {
        $this->suspendSalonOwner();

        $this->actingAs($this->admin)->get(route('vehicles.show', $this->car))->assertOk();
        $this->actingAs($this->admin)->get(route('dealers.show', $this->salon))->assertOk();
    }

    public function test_nobody_can_message_a_suspended_seller_about_their_car(): void
    {
        $this->suspendSalonOwner();

        $this->actingAs(User::factory()->create())
            ->post(route('conversations.start', $this->car), ['body' => 'Still available?'])
            ->assertNotFound();
    }

    public function test_suspended_user_who_is_signed_in_is_signed_out_on_the_next_request(): void
    {
        $this->actingAs($this->salonOwner)->get(route('dashboard'))->assertOk();

        $this->salonOwner->update(['is_active' => false]);

        $this->actingAs($this->salonOwner)->get(route('dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_cannot_suspend_their_own_account(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.users.toggle', $this->admin))
            ->assertSessionHas('error');

        $this->assertTrue($this->admin->fresh()->is_active);
    }
}
