<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Show the user directory.
     */
    public function index(): Response
    {
        $users = User::query()
            ->with('roles')
            ->orderBy('name')
            ->get()
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at?->toIso8601String(),
                'created_at' => $user->created_at?->toIso8601String(),
                'roles' => $user->roles->pluck('name')->sort()->values()->all(),
            ]);

        return Inertia::render('users/Index', [
            'users' => $users,
            'roles' => Role::query()->orderBy('name')->pluck('name'),
        ]);
    }

    /**
     * Create a user from the dashboard.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $attributes = $request->validated();

        $user = User::query()->create([
            'name' => $attributes['name'],
            'email' => Str::lower($attributes['email']),
            'password' => $attributes['password'],
        ]);

        $user->forceFill(['email_verified_at' => now()])->save();
        $user->assignRole($attributes['role']);

        activity()
            ->causedBy($request->user())
            ->performedOn($user)
            ->event('created')
            ->withProperties([
                'name' => $user->name,
                'email' => $user->email,
                'role' => $attributes['role'],
            ])
            ->log('User created');

        Inertia::flash('toast', ['type' => 'success', 'message' => __('User created.')]);

        return to_route('users.index');
    }

    /**
     * Update a user from the dashboard.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $attributes = $request->validated();

        $originalRole = $user->roles->pluck('name')->first();

        $user->fill([
            'name' => $attributes['name'],
            'email' => Str::lower($attributes['email']),
        ]);

        if (! empty($attributes['password'])) {
            $user->password = $attributes['password'];
        }

        $user->save();

        if (isset($attributes['role'])) {
            $user->syncRoles([$attributes['role']]);
        }

        activity()
            ->causedBy($request->user())
            ->performedOn($user)
            ->event('updated')
            ->withProperties([
                'name' => $user->name,
                'email' => $user->email,
                'role_before' => $originalRole,
                'role_after' => $attributes['role'] ?? $originalRole,
            ])
            ->log('User updated');

        Inertia::flash('toast', ['type' => 'success', 'message' => __('User updated.')]);

        return to_route('users.index');
    }

    /**
     * Delete a user from the dashboard.
     */
    public function destroy(User $user): RedirectResponse
    {
        $actor = request()->user();

        if ($actor && $actor->id === $user->id) {
            Inertia::flash('toast', ['type' => 'error', 'message' => __('You cannot delete your own account.')]);

            return to_route('users.index');
        }

        activity()
            ->causedBy($actor)
            ->performedOn($user)
            ->event('deleted')
            ->withProperties([
                'name' => $user->name,
                'email' => $user->email,
            ])
            ->log('User deleted');

        $user->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('User deleted.')]);

        return to_route('users.index');
    }
}
