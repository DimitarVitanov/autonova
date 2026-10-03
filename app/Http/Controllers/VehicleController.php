<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;
use App\Http\Resources\VehicleCardResource;
use App\Http\Resources\VehicleResource;
use App\Models\Category;
use App\Models\Feature;
use App\Models\Vehicle;
use App\Services\ImageService;
use App\Support\MarketplaceOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class VehicleController extends Controller
{
    public function __construct(private ImageService $images)
    {
    }

    /** Search results page. */
    public function index(Request $request): Response
    {
        $filters = $this->filters($request);
        $sort = $request->input('sort', 'newest');

        $query = Vehicle::query()
            ->active()
            ->filter($filters)
            ->sortBy($sort)
            ->with(['make', 'carModel', 'category', 'dealer', 'coverImage'])
            ->withCount('images');

        $vehicles = $query->paginate(24)->withQueryString();

        $category = $filters['category'] ?? null;

        return Inertia::render('Vehicles/Index', [
            'vehicles' => VehicleCardResource::collection($vehicles),
            'filters' => $filters + ['sort' => $sort, 'view' => $request->input('view', 'grid')],
            'options' => [
                ...MarketplaceOptions::config(),
                'makes' => MarketplaceOptions::makes($category),
                'models' => MarketplaceOptions::models($filters['make_id'] ?? null),
                'categories' => Category::orderBy('sort')->get(['id', 'slug', 'name', 'name_plural']),
                'features' => Feature::orderBy('sort')->get(['id', 'name', 'slug', 'group']),
            ],
            'resultCount' => $vehicles->total(),
            'favoriteIds' => $this->favoriteIds($request),
        ]);
    }

    /** Vehicle detail page. */
    public function show(Request $request, Vehicle $vehicle): Response
    {
        abort_if($vehicle->status !== 'active' && ! $this->canManage($request, $vehicle), 404);

        $vehicle->increment('views');
        $vehicle->load(['make', 'carModel', 'category', 'dealer', 'user', 'images', 'features']);

        $related = Vehicle::active()
            ->where('category_id', $vehicle->category_id)
            ->where('id', '!=', $vehicle->id)
            ->whereBetween('price', [$vehicle->price * 0.6, $vehicle->price * 1.4])
            ->with(['make', 'carModel', 'category', 'coverImage'])
            ->limit(4)
            ->get();

        return Inertia::render('Vehicles/Show', [
            'vehicle' => (new VehicleResource($vehicle))->resolve(),
            'related' => VehicleCardResource::collection($related),
            'favoriteIds' => $this->favoriteIds($request),
            'canManage' => $this->canManage($request, $vehicle),
        ]);
    }

    /** Add-listing wizard. */
    public function create(Request $request): Response
    {
        return Inertia::render('Vehicles/Create', [
            'categories' => Category::orderBy('sort')->get(['id', 'slug', 'name', 'name_plural', 'hint', 'icon']),
            'makesByCategory' => MarketplaceOptions::makesByCategory(),
            'features' => Feature::orderBy('sort')->get(['id', 'name', 'slug', 'group']),
            'options' => MarketplaceOptions::config(),
            'promotions' => config('marketplace.promotions'),
            'defaults' => [
                'city' => $request->user()->city,
                'contact_phone' => $request->user()->phone,
            ],
        ]);
    }

    public function store(StoreVehicleRequest $request)
    {
        $data = $request->validated();
        $user = $request->user();

        $make = ! empty($data['make_id']) ? \App\Models\Make::find($data['make_id']) : null;
        $model = ! empty($data['car_model_id']) ? \App\Models\CarModel::find($data['car_model_id']) : null;
        $title = trim(($make?->name ?? 'Vehicle') . ' ' . ($model?->name ?? ''));

        $vehicle = Vehicle::create([
            'user_id' => $user->id,
            'dealer_id' => $user->isDealer() ? $user->dealer?->id : null,
            'category_id' => $data['category_id'],
            'make_id' => $data['make_id'] ?? null,
            'car_model_id' => $data['car_model_id'] ?? null,
            'title' => $title,
            'slug' => Str::slug($title) . '-' . Str::lower(Str::random(6)),
            'version' => $data['version'] ?? null,
            'price' => $data['price'],
            'vat' => $data['vat'],
            'year' => $data['year'],
            'mileage_km' => $data['mileage_km'],
            'fuel' => $data['fuel'],
            'transmission' => $data['transmission'],
            'engine_cc' => $data['engine_cc'] ?? null,
            'power_hp' => $data['power_hp'] ?? null,
            'drivetrain' => $data['drivetrain'] ?? null,
            'body_type' => $data['body_type'] ?? null,
            'doors' => $data['doors'] ?? null,
            'seats' => $data['seats'] ?? null,
            'color' => $data['color'] ?? null,
            'condition' => $data['condition'],
            'owners' => $data['owners'] ?? null,
            'registered_until' => $data['registered_until'] ?? null,
            'city' => $data['city'],
            'description' => $data['description'] ?? null,
            'seller_type' => $user->isDealer() ? 'dealer' : 'private',
            'contact_phone' => $data['contact_phone'] ?? $user->phone,
            'status' => 'pending',
            'promotion' => $data['promotion'] ?? 'none',
            'promoted_until' => in_array($data['promotion'] ?? 'none', ['featured']) ? now()->addDays(7) : null,
            'is_featured' => ($data['promotion'] ?? 'none') === 'featured',
            'bumped_at' => now(),
        ]);

        if (! empty($data['features'])) {
            $vehicle->features()->sync($data['features']);
        }

        foreach ($request->file('images', []) as $index => $file) {
            $stored = $this->images->storeListingImage($file, 'vehicles/' . now()->format('Y/m'));
            $vehicle->images()->create([
                'path' => $stored['path'],
                'thumb_path' => $stored['thumb_path'],
                'width' => $stored['width'],
                'height' => $stored['height'],
                'sort' => $index,
                'is_cover' => $index === 0,
            ]);
        }

        return redirect()->route('dashboard')
            ->with('success', __('Your listing has been submitted and is awaiting review.'));
    }

    public function edit(Request $request, Vehicle $vehicle): Response
    {
        abort_unless($this->canManage($request, $vehicle), 403);
        $vehicle->load(['images', 'features', 'make', 'carModel', 'category']);

        return Inertia::render('Vehicles/Edit', [
            'vehicle' => (new VehicleResource($vehicle))->resolve() + [
                'make_id' => $vehicle->make_id,
                'car_model_id' => $vehicle->car_model_id,
                'category_id' => $vehicle->category_id,
                'feature_ids' => $vehicle->features->pluck('id'),
                'raw' => $vehicle->only([
                    'price', 'vat', 'year', 'mileage_km', 'fuel', 'transmission', 'engine_cc',
                    'power_hp', 'drivetrain', 'body_type', 'doors', 'seats', 'color', 'condition',
                    'owners', 'registered_until', 'city', 'contact_phone', 'description', 'version',
                ]),
            ],
            'makesByCategory' => MarketplaceOptions::makesByCategory(),
            'models' => MarketplaceOptions::models($vehicle->make_id),
            'categories' => Category::orderBy('sort')->get(['id', 'slug', 'name', 'name_plural']),
            'features' => Feature::orderBy('sort')->get(['id', 'name', 'slug', 'group']),
            'options' => MarketplaceOptions::config(),
        ]);
    }

    public function update(UpdateVehicleRequest $request, Vehicle $vehicle)
    {
        abort_unless($this->canManage($request, $vehicle), 403);
        $data = $request->safe()->except(['images']);

        $vehicle->update($data + [
            'status' => $vehicle->status === 'active' ? 'active' : 'pending',
        ]);

        if (! empty($data['features'])) {
            $vehicle->features()->sync($data['features']);
        }

        foreach ($request->file('images', []) as $index => $file) {
            $stored = $this->images->storeListingImage($file, 'vehicles/' . now()->format('Y/m'));
            $vehicle->images()->create([
                'path' => $stored['path'],
                'thumb_path' => $stored['thumb_path'],
                'width' => $stored['width'],
                'height' => $stored['height'],
                'sort' => $vehicle->images()->max('sort') + 1,
                'is_cover' => $vehicle->images()->count() === 0,
            ]);
        }

        return redirect()->route('dashboard')->with('success', __('Listing updated.'));
    }

    public function destroy(Request $request, Vehicle $vehicle)
    {
        abort_unless($this->canManage($request, $vehicle), 403);
        $vehicle->delete();

        return redirect()->route('dashboard')->with('success', __('Listing removed.'));
    }

    // ---- Helpers -----------------------------------------------------------

    private function filters(Request $request): array
    {
        return array_filter([
            'category' => $request->input('category'),
            'make_id' => $request->input('make_id'),
            'car_model_id' => $request->input('car_model_id'),
            'price_min' => $request->input('price_min'),
            'price_max' => $request->input('price_max'),
            'year_min' => $request->input('year_min'),
            'year_max' => $request->input('year_max'),
            'mileage_max' => $request->input('mileage_max'),
            'power_min' => $request->input('power_min'),
            'power_max' => $request->input('power_max'),
            'fuel' => $request->input('fuel'),
            'transmission' => $request->input('transmission'),
            'drivetrain' => $request->input('drivetrain'),
            'body_type' => $request->input('body_type'),
            'condition' => $request->input('condition'),
            'seller_type' => $request->input('seller_type'),
            'city' => $request->input('city'),
            'features' => $request->input('features'),
            'q' => $request->input('q'),
        ], fn ($v) => $v !== null && $v !== '' && $v !== []);
    }

    private function favoriteIds(Request $request): array
    {
        return $request->user()
            ? $request->user()->favorites()->pluck('vehicle_id')->all()
            : [];
    }

    private function canManage(Request $request, Vehicle $vehicle): bool
    {
        $user = $request->user();
        return $user && ($user->id === $vehicle->user_id || $user->isStaff());
    }
}
