<?php

namespace App\Http\Controllers;

use App\Http\Resources\VehicleCardResource;
use App\Models\Vehicle;
use App\Models\Translation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SavedController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $favoriteIds = $user->favorites()->pluck('vehicle_id');

        $vehicles = Vehicle::whereIn('id', $favoriteIds)
            ->with(['make', 'carModel', 'category', 'dealer', 'coverImage'])
            ->withCount('images')
            ->latest()
            ->get();

        return Inertia::render('Saved', [
            'vehicles' => VehicleCardResource::collection($vehicles),
            'savedSearches' => $user->savedSearches()->latest()->get()->map(fn ($s) => [
                'id' => $s->id,
                'title' => $s->title,
                'filters' => $s->filters,
                'new_count' => $s->new_count,
                'alerts' => $s->alerts,
                'summary' => $this->summarise($s->filters),
            ]),
            'favoriteIds' => $favoriteIds->all(),
        ]);
    }

    private function summarise(array $filters): string
    {
        $parts = [];
        if (! empty($filters['q'])) {
            $parts[] = ucfirst($filters['q']);
        }
        if (! empty($filters['category'])) {
            $parts[] = Translation::label('cat.' . $filters['category'], ucfirst($filters['category']));
        }
        if (! empty($filters['city'])) {
            $parts[] = Translation::label('city.' . $filters['city'], $filters['city']);
        }
        if (! empty($filters['fuel'])) {
            $parts[] = implode('/', array_map(function ($fuel) {
                $label = config("marketplace.fuels.{$fuel}", ucfirst($fuel));

                return Translation::label('enum.' . $label, $label);
            }, (array) $filters['fuel']));
        }
        if (! empty($filters['price_max'])) {
            $parts[] = __('up to €:price', ['price' => number_format((int) $filters['price_max'])]);
        }
        if (! empty($filters['year_min'])) {
            $parts[] = $filters['year_min'] . '+';
        }

        return implode(' · ', $parts) ?: __('All vehicles');
    }
}
