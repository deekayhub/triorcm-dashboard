<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProviderLocationRequest;
use App\Models\Provider;
use App\Models\ProviderLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ProviderLocationController extends Controller
{
    /**
     * Store a newly created provider location.
     */
    public function store(ProviderLocationRequest $request, Provider $provider): RedirectResponse
    {
        $validated = $request->validated();
        $validated['provider_id'] = $provider->id;

        DB::transaction(function () use ($provider, $validated, $request) {
            // If this is set as primary, unset any existing primary
            if ($validated['is_primary'] ?? false) {
                $provider->locations()->update(['is_primary' => false]);
            }

            $location = ProviderLocation::create($validated);

            activity()
                ->causedBy($request->user())
                ->performedOn($location)
                ->event('created')
                ->log('Provider location created');
        });

        return redirect()->route('providers.profile', $provider)->with('success', 'Location added successfully.');
    }

    /**
     * Update the specified provider location.
     */
    public function update(ProviderLocationRequest $request, Provider $provider, ProviderLocation $providerLocation): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($provider, $providerLocation, $validated, $request) {
            // If this is set as primary, unset any existing primary
            if ($validated['is_primary'] ?? false) {
                $provider->locations()->where('id', '!=', $providerLocation->id)->update(['is_primary' => false]);
            }

            $providerLocation->update($validated);

            activity()
                ->causedBy($request->user())
                ->performedOn($providerLocation)
                ->event('updated')
                ->log('Provider location updated');
        });

        return redirect()->route('providers.profile', $provider)->with('success', 'Location updated successfully.');
    }

    /**
     * Remove the specified provider location.
     */
    public function destroy(Provider $provider, ProviderLocation $providerLocation): RedirectResponse
    {
        $this->authorize('archive', $provider);
        $user = request()->user();

        DB::transaction(function () use ($providerLocation, $user) {
            activity()
                ->causedBy($user)
                ->performedOn($providerLocation)
                ->event('deleted')
                ->log('Provider location deleted');

            $providerLocation->delete();
        });

        return redirect()->route('providers.profile', $provider)->with('success', 'Location removed successfully.');
    }
}
