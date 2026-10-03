<?php

namespace App\Http\Controllers;

use App\Models\SavedSearch;
use Illuminate\Http\Request;

class SavedSearchController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'filters' => ['required', 'array'],
        ]);

        $request->user()->savedSearches()->create([
            'title' => $data['title'],
            'filters' => $data['filters'],
            'alerts' => true,
        ]);

        return back()->with('success', __('Search saved. We will notify you about new matches.'));
    }

    public function destroy(Request $request, SavedSearch $savedSearch)
    {
        abort_unless($savedSearch->user_id === $request->user()->id, 403);
        $savedSearch->delete();

        return back()->with('success', __('Saved search removed.'));
    }
}
