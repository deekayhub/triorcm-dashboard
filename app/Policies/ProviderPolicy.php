<?php

namespace App\Policies;

use App\Models\Provider;
use App\Models\User;

class ProviderPolicy
{
    /**
     * Determine whether the user can view any providers.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('providers.viewAny');
    }

    /**
     * Determine whether the user can view the provider.
     */
    public function view(User $user, Provider $provider): bool
    {
        return $user->can('providers.view');
    }

    /**
     * Determine whether the user can create providers.
     */
    public function create(User $user): bool
    {
        return $user->can('providers.create');
    }

    /**
     * Determine whether the user can update the provider.
     */
    public function update(User $user, Provider $provider): bool
    {
        return $user->can('providers.update');
    }

    /**
     * Determine whether the user can archive the provider.
     */
    public function archive(User $user, Provider $provider): bool
    {
        return $user->can('providers.archive');
    }

    /**
     * Determine whether the user can assign the provider.
     */
    public function assign(User $user): bool
    {
        return $user->can('providers.assign');
    }

    /**
     * Determine whether the user can view sensitive provider data.
     */
    public function viewSensitive(User $user): bool
    {
        return $user->can('providers.viewSensitive');
    }
}
