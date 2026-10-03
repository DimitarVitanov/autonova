<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class TranslationController extends Controller
{
    public function index(): Response
    {
        $translations = Translation::orderBy('group')->orderBy('key')->get(['id', 'group', 'key', 'en', 'mk']);

        return Inertia::render('Admin/Translations', [
            'translations' => $translations,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'translations' => ['required', 'array'],
            'translations.*' => ['nullable', 'string', 'max:2000'],
        ]);

        foreach ($data['translations'] as $id => $mk) {
            Translation::where('id', (int) $id)->update(['mk' => ($mk === null || $mk === '') ? null : $mk]);
        }

        Cache::forget('i18n.strings.mk');
        Cache::forget('i18n.strings.en');

        return back()->with('success', __('Translations saved.'));
    }
}
