<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    /**
     * Show the user directory.
     */
    public function index(Request $request): Response|JsonResponse
    {
        if ($request->ajax() && $request->has('draw')) {
            $query = User::query()
                ->with('roles')
                ->whereDoesntHave('roles', function ($q) {
                    $q->where('name', 'super-admin');
                });

            return DataTables::of($query)
                ->addColumn('id', function (User $user) {
                    return $user->id;
                })
                ->addColumn('user', function (User $user) {
                    return [
                        'name' => $user->name,
                        'email' => $user->email,
                    ];
                })
                ->addColumn('roles', function (User $user) {
                    return $user->roles->pluck('name')->sort()->values()->all();
                })
                ->addColumn('status', function (User $user) {
                    return $user->email_verified_at !== null ? 'verified' : 'pending';
                })
                ->addColumn('joined', function (User $user) {
                    return $user->created_at?->format('d M Y');
                })
                ->addColumn('actions', function (User $user) {
                    return $user->id;
                })
                ->filterColumn('user', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%");
                    });
                })
                ->filter(function ($query) use ($request) {
                    $role = $request->input('role');
                    if (! empty($role) && $role !== 'all') {
                        $query->whereHas('roles', function ($q) use ($role) {
                            $q->where('name', $role);
                        });
                    }
                })
                ->orderColumn('user', function ($query, $order) {
                    $query->orderBy('name', $order);
                })
                ->orderColumn('joined', function ($query, $order) {
                    $query->orderBy('created_at', $order);
                })
                ->rawColumns(['user', 'roles', 'status', 'actions'])
                ->make(true);
        }

        $perPage = $request->integer('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;

        $search = trim((string) $request->input('search', ''));
        $roleFilter = (string) $request->input('role', 'all');

        $usersQuery = User::query()
            ->with('roles')
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', 'super-admin');
            })
            ->orderBy('name');

        if ($search !== '') {
            $usersQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($roleFilter !== '' && $roleFilter !== 'all') {
            $usersQuery->whereHas('roles', function ($query) use ($roleFilter) {
                $query->where('name', $roleFilter);
            });
        }

        $users = $usersQuery
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at?->toIso8601String(),
                'created_at' => $user->created_at?->format('d M Y'),
                'roles' => $user->roles->pluck('name')->sort()->values()->all(),
            ]);

        $allTeamUsers = User::query()
            ->with('roles')
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', 'super-admin');
            })
            ->get();

        return Inertia::render('users/Index', [
            'users' => $users,
            'roles' => Role::query()
                ->where('name', '!=', 'super-admin')
                ->orderBy('name')
                ->pluck('name'),
            'filters' => [
                'search' => $search,
                'role' => $roleFilter,
                'per_page' => $perPage,
            ],
            'stats' => [
                'total' => $allTeamUsers->count(),
                'verified' => $allTeamUsers->filter(fn ($u) => $u->email_verified_at !== null)->count(),
                'inactive' => $allTeamUsers->filter(fn ($u) => $u->email_verified_at === null)->count(),
            ],
        ]);
    }

    /**
     * Show the create user form.
     */
    public function create(): Response
    {
        return Inertia::render('users/Create', [
            'roles' => Role::query()
                ->where('name', '!=', 'super-admin')
                ->orderBy('name')
                ->pluck('name'),
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
     * Show the edit user form.
     */
    public function edit(User $user): Response
    {
        $user->load('roles');

        return Inertia::render('users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name')->all(),
            ],
            'roles' => Role::query()
                ->where('name', '!=', 'super-admin')
                ->orderBy('name')
                ->pluck('name'),
        ]);
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
