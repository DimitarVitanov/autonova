<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Translation;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ListingController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->input('status', 'pending');

        $vehicles = Vehicle::query()
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($request->input('q'), fn ($q, $term) => $q->where('title', 'like', "%{$term}%"))
            ->with(['user', 'make', 'carModel', 'category', 'coverImage'])
            ->latest()
            ->paginate(20)
            ->withQueryString()
            ->through(fn ($v) => [
                'id' => $v->id,
                'slug' => $v->slug,
                'title' => $v->title,
                'version' => $v->version,
                'price' => $v->price,
                'year' => $v->year,
                'city' => $v->city,
                'status' => $v->status,
                'seller' => $v->user->name,
                'seller_type' => $v->seller_type,
                'category' => Translation::label('cat.' . $v->category->slug, $v->category->name),
                'cover_url' => $v->cover_url,
                'created_at' => $v->created_at->diffForHumans(),
            ]);

        return Inertia::render('Admin/Listings', [
            'vehicles' => $vehicles,
            'filters' => ['status' => $status, 'q' => $request->input('q', '')],
            'statusCounts' => [
                'pending' => Vehicle::where('status', 'pending')->count(),
                'active' => Vehicle::where('status', 'active')->count(),
                'sold' => Vehicle::where('status', 'sold')->count(),
                'rejected' => Vehicle::where('status', 'rejected')->count(),
                'draft' => Vehicle::where('status', 'draft')->count(),
            ],
        ]);
    }

    public function approve(Vehicle $vehicle)
    {
        $vehicle->update([
            'status' => 'active',
            'reject_reason' => null,
            'published_at' => $vehicle->published_at ?? now(),
            'bumped_at' => now(),
        ]);

        return back()->with('success', __('“:title” approved and published.', ['title' => $vehicle->title]));
    }

    public function reject(Request $request, Vehicle $vehicle)
    {
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:200'],
        ]);

        $vehicle->update([
            'status' => 'rejected',
            'reject_reason' => $data['reason'] ?? __('Does not meet listing guidelines.'),
        ]);

        return back()->with('success', __('“:title” was rejected.', ['title' => $vehicle->title]));
    }

    public function toggleFeature(Vehicle $vehicle)
    {
        $isFeatured = ! $vehicle->is_featured;
        $vehicle->update([
            'is_featured' => $isFeatured,
            'promotion' => $isFeatured ? 'featured' : 'none',
            'promoted_until' => $isFeatured ? now()->addDays(7) : null,
        ]);

        return back()->with('success', $isFeatured ? __('Listing featured.') : __('Listing un-featured.'));
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();

        return back()->with('success', __('Listing deleted.'));
    }
}
