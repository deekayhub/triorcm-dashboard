<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProviderIdentifierRequest;
use App\Models\Provider;
use App\Models\ProviderIdentifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ProviderIdentifierController extends Controller
{
    /**
     * Store a newly created provider identifier.
     */
    public function store(ProviderIdentifierRequest $request, Provider $provider): RedirectResponse
    {
        $validated = $request->validated();
        $validated['provider_id'] = $provider->id;

        DB::transaction(function () use ($provider, $validated, $request) {
            $identifier = ProviderIdentifier::create($validated);

            activity()
                ->causedBy($request->user())
                ->performedOn($identifier)
                ->event('created')
                ->log('Provider identifier created');
        });

        return redirect()->route('providers.profile', $provider)->with('success', 'Identifier added successfully.');
    }

    /**
     * Update the specified provider identifier.
     */
    public function update(ProviderIdentifierRequest $request, Provider $provider, ProviderIdentifier $providerIdentifier): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($providerIdentifier, $validated, $request) {
            $providerIdentifier->update($validated);

            activity()
                ->causedBy($request->user())
                ->performedOn($providerIdentifier)
                ->event('updated')
                ->log('Provider identifier updated');
        });

        return redirect()->route('providers.profile', $provider)->with('success', 'Identifier updated successfully.');
    }

    /**
     * Remove the specified provider identifier.
     */
    public function destroy(Provider $provider, ProviderIdentifier $providerIdentifier): RedirectResponse
    {
        $this->authorize('archive', $provider);
        $user = request()->user();

        DB::transaction(function () use ($providerIdentifier, $user) {
            activity()
                ->causedBy($user)
                ->performedOn($providerIdentifier)
                ->event('deleted')
                ->log('Provider identifier deleted');

            $providerIdentifier->delete();
        });

        return redirect()->route('providers.profile', $provider)->with('success', 'Identifier deleted successfully.');
    }
}
