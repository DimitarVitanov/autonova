<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(): Response
    {
        $categories = Category::orderBy('sort')
            ->withCount(['makes', 'vehicles', 'vehicles as active_count' => fn ($q) => $q->where('status', 'active')])
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'slug' => $c->slug,
                'name' => $c->name,
                'name_plural' => $c->name_plural,
                'hint' => $c->hint,
                'icon' => $c->icon,
                'makes_count' => $c->makes_count,
                'vehicles_count' => $c->vehicles_count,
                'active_count' => $c->active_count,
            ]);

        return Inertia::render('Admin/Categories', [
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'name_plural' => ['nullable', 'string', 'max:60'],
            'hint' => ['nullable', 'string', 'max:120'],
        ]);

        $category->update($data);

        return back()->with('success', __('Category updated.'));
    }
}
