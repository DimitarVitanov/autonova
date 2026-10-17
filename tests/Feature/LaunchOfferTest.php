<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LaunchOfferTest extends TestCase
{
    use RefreshDatabase;

    private function assertPricingShowsFree(bool $free, ?string $until = null): void
    {
        $this->get(route('pricing'))->assertInertia(fn (Assert $page) => $page
            ->component('Pricing')
            ->where('launchOffer.active', $free)
            ->where('launchOffer.until', $until));
    }

    public function test_prices_are_regular_until_an_admin_switches_the_offer_on(): void
    {
        $this->assertPricingShowsFree(false);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.settings'))->assertOk();
        $this->actingAs($admin)
            ->patch(route('admin.settings.update'), ['launch_offer_enabled' => true, 'launch_offer_until' => '2026-12-17'])
            ->assertSessionHasNoErrors();

        $this->travelTo('2026-10-17');
        $this->assertPricingShowsFree(true, '2026-12-17');

        $this->withSession(['locale' => 'mk'])->get(route('pricing'))
            ->assertInertia(fn (Assert $page) => $page->where('launchOffer.until_label', '17 декември 2026'));
    }

    public function test_offer_ends_by_itself_after_the_end_date(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->patch(route('admin.settings.update'), [
            'launch_offer_enabled' => true, 'launch_offer_until' => '2026-12-17',
        ]);

        $this->travelTo('2026-12-17 23:00');
        $this->assertPricingShowsFree(true, '2026-12-17');

        $this->travelTo('2026-12-18 00:30');
        $this->assertPricingShowsFree(false, '2026-12-17');
    }

    public function test_switching_the_offer_on_requires_an_end_date(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->patch(route('admin.settings.update'), ['launch_offer_enabled' => true, 'launch_offer_until' => null])
            ->assertSessionHasErrors('launch_offer_until');

        $this->assertPricingShowsFree(false);
    }

    public function test_only_admins_can_change_the_offer(): void
    {
        foreach (['moderator', 'client'] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)->get(route('admin.settings'))->assertForbidden();
            $this->actingAs($user)
                ->patch(route('admin.settings.update'), ['launch_offer_enabled' => true, 'launch_offer_until' => '2026-12-17'])
                ->assertForbidden();
        }

        $this->assertPricingShowsFree(false);
    }
}
