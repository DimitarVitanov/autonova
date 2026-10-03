<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $users = User::query()
            ->withCount('vehicles')
            ->when($request->input('q'), fn ($q, $term) => $q
                ->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"))
            ->when($request->input('role'), fn ($q, $role) => $q->where('role', $role))
            ->latest()
            ->paginate(20)
            ->withQueryString()
            ->through(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->role,
                'account_type' => $u->account_type,
                'city' => $u->city,
                'is_active' => $u->is_active,
                'vehicles_count' => $u->vehicles_count,
                'joined' => $u->created_at->translatedFormat('d M Y'),
            ]);

        return Inertia::render('Admin/Users', [
            'users' => $users,
            'filters' => ['q' => $request->input('q', ''), 'role' => $request->input('role', '')],
        ]);
    }

    public function updateRole(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(['admin', 'moderator', 'client'])],
        ]);

        $user->update(['role' => $data['role']]);

        return back()->with('success', __(":name's role updated to :role.", ['name' => $user->name, 'role' => __($data['role'])]));
    }

    public function toggleActive(User $user)
    {
        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', $user->is_active ? __('User activated.') : __('User suspended.'));
    }
}
