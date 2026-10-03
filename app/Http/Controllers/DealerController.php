<?php

namespace App\Http\Controllers;

use App\Http\Resources\VehicleCardResource;
use App\Models\Dealer;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DealerController extends Controller
{
    public function index(Request $request): Response
    {
        $dealers = Dealer::withCount(['vehicles' => fn ($q) => $q->where('status', 'active')])
            ->when($request->input('q'), fn ($q, $term) => $q->where('name', 'like', "%{$term}%"))
            ->orderByDesc('verified')
            ->orderByDesc('vehicles_count')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Dealers/Index', [
            'dealers' => $dealers,
            'filters' => ['q' => $request->input('q', '')],
        ]);
    }

    public function show(Request $request, Dealer $dealer): Response
    {
        $sort = $request->input('sort', 'newest');

        $vehicles = Vehicle::active()
            ->where('dealer_id', $dealer->id)
            ->sortBy($sort)
            ->with(['make', 'carModel', 'category', 'coverImage'])
            ->withCount('images')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Dealers/Show', [
            'dealer' => [
                'id' => $dealer->id,
                'name' => $dealer->name,
                'slug' => $dealer->slug,
                'city' => $dealer->city,
                'address' => $dealer->address,
                'founded_year' => $dealer->founded_year,
                'verified' => $dealer->verified,
                'phone' => $dealer->phone,
                'website' => $dealer->website,
                'hours' => $dealer->hours,
                'about' => $dealer->about,
                'rating' => $dealer->rating,
                'logo_url' => $dealer->logo_url,
                'cover_url' => $dealer->cover_url,
                'package' => $dealer->package,
            ],
            'vehicles' => VehicleCardResource::collection($vehicles),
            'stats' => [
                'active' => Vehicle::active()->where('dealer_id', $dealer->id)->count(),
                'sold' => Vehicle::where('dealer_id', $dealer->id)->where('status', 'sold')->count(),
                'rating' => $dealer->rating,
                'years' => $dealer->founded_year ? (int) date('Y') - $dealer->founded_year : null,
            ],
            'filters' => ['sort' => $sort],
            'options' => ['sorts' => collect(config('marketplace.sorts'))->map(fn ($l, $v) => ['value' => $v, 'label' => $l])->values()],
            'favoriteIds' => $request->user()?->favorites()->pluck('vehicle_id')->all() ?? [],
        ]);
    }
}
