<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register', [
            'cities' => config('marketplace.cities'),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'account_type' => ['required', 'in:private,dealer'],
            'phone' => ['nullable', 'string', 'max:40'],
            'city' => ['nullable', 'string', 'max:60'],
            'dealer_name' => ['nullable', 'required_if:account_type,dealer', 'string', 'max:120'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'client',
            'account_type' => $validated['account_type'],
            'phone' => $validated['phone'] ?? null,
            'city' => $validated['city'] ?? null,
        ]);

        if ($validated['account_type'] === 'dealer') {
            $user->dealer()->create([
                'name' => $validated['dealer_name'],
                'slug' => \Illuminate\Support\Str::slug($validated['dealer_name']).'-'.$user->id,
                'city' => $validated['city'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'package' => 'start',
            ]);
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('verification.notice', absolute: false));
    }
}
