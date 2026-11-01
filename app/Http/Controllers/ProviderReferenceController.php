<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProviderReferenceRequest;
use App\Models\Provider;
use App\Models\ProviderReference;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ProviderReferenceController extends Controller
{
    /**
     * Store a newly created professional reference.
     */
    public function store(ProviderReferenceRequest $request, Provider $provider): RedirectResponse
    {
        $validated = $request->validated();
        $validated['provider_id'] = $provider->id;

        $reference = ProviderReference::create($validated);

        activity()
            ->causedBy($request->user())
            ->performedOn($reference)
            ->event('created')
            ->log('Provider reference created');

        return redirect()->route('providers.profile', $provider)->with('success', 'Professional reference added successfully.');
    }

    /**
     * Update the specified professional reference.
     */
    public function update(ProviderReferenceRequest $request, Provider $provider, ProviderReference $providerReference): RedirectResponse
    {
        abort_unless($providerReference->provider_id === $provider->id, 404);

        $validated = $request->validated();

        // Reference records cannot be moved between providers.
        unset($validated['provider_id']);

        $providerReference->update($validated);

        activity()
            ->causedBy($request->user())
            ->performedOn($providerReference)
            ->event('updated')
            ->log('Provider reference updated');

        return redirect()->route('providers.profile', $provider)->with('success', 'Professional reference updated successfully.');
    }

    /**
     * Remove the specified professional reference.
     */
    public function destroy(Provider $provider, ProviderReference $providerReference): RedirectResponse
    {
        abort_unless($providerReference->provider_id === $provider->id, 404);

        $this->authorize('archive', $provider);
        $user = request()->user();

        DB::transaction(function () use ($providerReference, $user) {
            activity()
                ->causedBy($user)
                ->performedOn($providerReference)
                ->event('deleted')
                ->log('Provider reference deleted');

            $providerReference->delete();
        });

        return redirect()->route('providers.profile', $provider)->with('success', 'Professional reference removed successfully.');
    }
}
