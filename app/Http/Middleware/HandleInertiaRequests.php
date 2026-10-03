<?php

namespace App\Http\Middleware;

use App\Models\Category;
use App\Models\Conversation;
use App\Models\Translation;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    /** Locale from the session (Macedonian default, English optional). */
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->session()->get('locale', 'mk');
        App::setLocale(in_array($locale, ['mk', 'en'], true) ? $locale : 'mk');

        return parent::handle($request, $next);
    }

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),

            'appName' => config('app.name'),

            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'account_type' => $user->account_type,
                    'city' => $user->city,
                    'phone' => $user->phone,
                    'avatar_url' => $user->avatar_url,
                    'is_dealer' => $user->isDealer(),
                    'is_staff' => $user->isStaff(),
                    'dealer' => $user->relationLoaded('dealer') ? $user->dealer : $user->dealer,
                ] : null,
            ],

            'counts' => fn () => $user ? [
                'favorites' => $user->favorites()->count(),
                'saved_searches' => $user->savedSearches()->count(),
                'unread_messages' => $this->unreadMessages($user->id),
            ] : ['favorites' => 0, 'saved_searches' => 0, 'unread_messages' => 0],

            'nav' => [
                // Cache a plain array — caching the Eloquent Collection itself
                // serialises to a __PHP_Incomplete_Class and renders empty.
                'categories' => Cache::remember('nav.categories', 300, fn () => Category::orderBy('sort')->get([
                    'id', 'slug', 'name', 'name_plural', 'hint', 'icon',
                ])->toArray()),
            ],

            // Editable UI translations for the active locale (mk falls back to en).
            'i18n' => [
                'locale' => App::getLocale(),
                'strings' => Translation::strings(),
            ],

            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }

    private function unreadMessages(int $userId): int
    {
        return Conversation::where(fn ($q) => $q->where('buyer_id', $userId)->orWhere('seller_id', $userId))
            ->withCount(['messages as unread' => fn ($m) => $m->whereNull('read_at')->where('sender_id', '!=', $userId)])
            ->get()
            ->sum('unread');
    }
}
