<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Dealer;
use App\Models\User;
use App\Models\Translation;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $pending = Vehicle::where('status', 'pending')
            ->with(['user', 'make', 'carModel', 'category', 'coverImage'])
            ->latest()
            ->limit(6)
            ->get()
            ->map(fn ($v) => $this->row($v));

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'pending' => Vehicle::where('status', 'pending')->count(),
                'active' => Vehicle::where('status', 'active')->count(),
                'total' => Vehicle::count(),
                'sold' => Vehicle::where('status', 'sold')->count(),
                'users' => User::count(),
                'dealers' => Dealer::count(),
                'views' => (int) Vehicle::sum('views'),
                'categories' => Category::count(),
            ],
            'pending' => $pending,
            'role' => $request->user()->role,
        ]);
    }

    private function row(Vehicle $v): array
    {
        return [
            'id' => $v->id,
            'slug' => $v->slug,
            'title' => $v->title,
            'version' => $v->version,
            'price' => $v->price,
            'year' => $v->year,
            'city' => $v->city,
            'seller' => $v->user->name,
            'seller_type' => $v->seller_type,
            'category' => Translation::label('cat.' . $v->category->slug, $v->category->name),
            'cover_url' => $v->cover_url,
            'created_at' => $v->created_at->diffForHumans(),
        ];
    }
}
