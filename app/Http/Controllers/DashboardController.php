<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response|\Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        if ($user->isStaff()) {
            return redirect()->route('admin.dashboard');
        }

        $listings = Vehicle::where('user_id', $user->id)
            ->with(['coverImage', 'category'])
            ->withCount('images')
            ->latest()
            ->get()
            ->map(fn ($v) => [
                'id' => $v->id,
                'slug' => $v->slug,
                'title' => $v->title,
                'version' => $v->version,
                'price' => $v->price,
                'year' => $v->year,
                'mileage_km' => $v->mileage_km,
                'views' => $v->views,
                'status' => $v->status,
                'promotion' => $v->promotion,
                'cover_url' => $v->cover_url,
                'created_at' => $v->created_at->translatedFormat('d M Y'),
            ]);

        $vehicleIds = $listings->pluck('id');

        $stats = [
            'active' => $listings->where('status', 'active')->count(),
            'pending' => $listings->where('status', 'pending')->count(),
            'sold' => $listings->where('status', 'sold')->count(),
            'total_views' => (int) Vehicle::where('user_id', $user->id)->sum('views'),
            'leads' => Conversation::whereIn('vehicle_id', $vehicleIds)->count(),
            'promo_credits' => $user->dealer?->promo_credits ?? 0,
        ];

        return Inertia::render('Dashboard', [
            'listings' => $listings,
            'stats' => $stats,
            'dealer' => $user->dealer,
        ]);
    }
}
