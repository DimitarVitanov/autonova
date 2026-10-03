<?php

namespace App\Http\Controllers;

use App\Http\Resources\VehicleCardResource;
use App\Models\Category;
use App\Models\Dealer;
use App\Models\Make;
use App\Models\Vehicle;
use App\Support\MarketplaceOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(Request $request): Response
    {
        $counts = Vehicle::active()
            ->select('category_id', DB::raw('count(*) as total'))
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        $categories = Category::orderBy('sort')->get()->map(fn ($c) => [
            'id' => $c->id,
            'slug' => $c->slug,
            'name' => $c->name,
            'name_plural' => $c->name_plural,
            'hint' => $c->hint,
            'icon' => $c->icon,
            'count' => (int) ($counts[$c->id] ?? 0),
        ]);

        $hero = Vehicle::active()->promoted()
            ->with(['make', 'carModel', 'category', 'dealer', 'coverImage'])
            ->withCount('images')
            ->inRandomOrder()
            ->first();

        $featured = Vehicle::active()
            ->where('is_featured', true)
            ->when($hero, fn ($q) => $q->where('id', '!=', $hero->id))
            ->with(['make', 'carModel', 'category', 'dealer', 'coverImage'])
            ->withCount('images')
            ->latest('published_at')
            ->limit(8)
            ->get();

        $newest = Vehicle::active()
            ->with(['make', 'carModel', 'category', 'dealer', 'coverImage'])
            ->latest('published_at')
            ->limit(8)
            ->get();

        $dealers = Dealer::where('verified', true)
            ->withCount(['vehicles' => fn ($q) => $q->where('status', 'active')])
            ->orderByDesc('vehicles_count')
            ->limit(4)
            ->get(['id', 'name', 'slug', 'city', 'package', 'verified']);

        // Popular car brands (logo shortcuts, AutoScout-style) → filtered search.
        $carCategoryId = Category::where('slug', 'cars')->value('id');
        $brandOrder = [
            ['name' => 'Volkswagen', 'logo' => 'volkswagen'],
            ['name' => 'BMW', 'logo' => 'bmw'],
            ['name' => 'Audi', 'logo' => 'audi'],
            ['name' => 'Mercedes-Benz', 'logo' => 'mercedes'],
            ['name' => 'Opel', 'logo' => 'opel'],
            ['name' => 'Ford', 'logo' => 'ford'],
            ['name' => 'Renault', 'logo' => 'renault'],
            ['name' => 'Peugeot', 'logo' => 'peugeot'],
            ['name' => 'Skoda', 'logo' => 'skoda'],
            ['name' => 'Toyota', 'logo' => 'toyota'],
            ['name' => 'Porsche', 'logo' => 'porsche'],
            ['name' => 'Tesla', 'logo' => 'tesla'],
        ];
        $brandIds = Make::where('category_id', $carCategoryId)
            ->whereIn('name', array_column($brandOrder, 'name'))
            ->pluck('id', 'name');
        $brands = collect($brandOrder)
            ->filter(fn ($b) => isset($brandIds[$b['name']]))
            ->map(fn ($b) => ['id' => $brandIds[$b['name']], 'name' => $b['name'], 'logo' => $b['logo']])
            ->values();

        return Inertia::render('Home', [
            'categories' => $categories,
            'hero' => $hero ? (new VehicleCardResource($hero))->resolve() : null,
            'featured' => VehicleCardResource::collection($featured),
            'newest' => VehicleCardResource::collection($newest),
            'dealers' => $dealers,
            'brands' => $brands,
            'stats' => [
                'vehicles' => Vehicle::active()->count(),
                'dealers' => Dealer::count(),
                'new_today' => Vehicle::active()->whereDate('published_at', today())->count(),
                'results' => Vehicle::active()->count(),
            ],
            'options' => [
                'makes' => MarketplaceOptions::makes('cars'),
                'cities' => config('marketplace.cities'),
            ],
            'favoriteIds' => $request->user()?->favorites()->pluck('vehicle_id')->all() ?? [],
        ]);
    }
}
