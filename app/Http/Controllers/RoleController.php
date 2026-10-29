<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Show the roles and permissions overview.
     */
    public function index(): Response
    {
        $roles = Role::query()
            ->with('permissions')
            ->withCount('users')
            ->orderBy('name')
            ->get()
            ->map(fn (Role $role): array => [
                'id' => $role->id,
                'name' => $role->name,
                'guard_name' => $role->guard_name,
                'users_count' => $role->users_count,
                'permissions' => $role->permissions->pluck('name')->sort()->values()->all(),
            ]);

        return Inertia::render('roles/Index', [
            'roles' => $roles,
            'permissions' => Permission::query()->orderBy('name')->pluck('name'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('roles/Create', [
            'permissions' => Permission::query()->orderBy('name')->pluck('name'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $permissions = $request->input('permissions');

        if (is_string($permissions)) {
            $decoded = json_decode($permissions, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $request->merge(['permissions' => array_values($decoded)]);
            }
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        if (! empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()->route('roles.index')->with('success', 'Role created successfully.');
    }

    public function edit(Role $role): Response
    {
        $role->load('permissions');

        return Inertia::render('roles/Edit', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name')->values()->all(),
            ],
            'permissions' => Permission::query()->orderBy('name')->pluck('name'),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        if ($role->name === 'super-admin') {
            abort(403, 'The super-admin role cannot be modified.');
        }

        $permissions = $request->input('permissions');

        if (is_string($permissions)) {
            $decoded = json_decode($permissions, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $request->merge(['permissions' => array_values($decoded)]);
            }
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($role->id)],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ]);

        $role->update([
            'name' => $validated['name'],
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->name === 'super-admin') {
            abort(403, 'The super-admin role cannot be deleted.');
        }

        if ($role->users()->count() > 0) {
            return redirect()->route('roles.index')->with('error', 'Cannot delete a role that is assigned to users.');
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }
}
