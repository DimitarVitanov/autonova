<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function toggle(Request $request, Vehicle $vehicle)
    {
        $user = $request->user();
        $existing = $user->favorites()->where('vehicle_id', $vehicle->id)->first();

        if ($existing) {
            $existing->delete();
            $message = __('Removed from saved.');
        } else {
            $user->favorites()->create(['vehicle_id' => $vehicle->id]);
            $message = __('Saved to favorites.');
        }

        return back(fallback: route('vehicles.show', $vehicle))->with('success', $message);
    }
}
