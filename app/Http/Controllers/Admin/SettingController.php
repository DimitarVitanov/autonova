<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\LaunchOffer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Settings', [
            'launchOfferSettings' => LaunchOffer::settings(),
            // Pre-filled end date when switching the offer on: two months from today.
            'suggestedUntil' => today()->addMonths(2)->toDateString(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'launch_offer_enabled' => ['required', 'boolean'],
            'launch_offer_until' => ['nullable', 'date_format:Y-m-d', 'required_if:launch_offer_enabled,true'],
        ]);

        LaunchOffer::update($data['launch_offer_enabled'], $data['launch_offer_until'] ?? null);

        return back()->with('success', __('Settings saved.'));
    }
}
