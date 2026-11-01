<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProviderEmploymentRequest;
use App\Models\Provider;
use App\Models\ProviderEmployment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ProviderEmploymentController extends Controller
{
    /**
     * Store a newly created employment record.
     */
    public function store(ProviderEmploymentRequest $request, Provider $provider): RedirectResponse
    {
        $validated = $request->validated();
        $validated['provider_id'] = $provider->id;

        // If this is set as current, unset any existing current employment
        if ($validated['is_current'] ?? false) {
            $provider->employments()->update(['is_current' => false]);
        }

        $employment = ProviderEmployment::create($validated);

        activity()
            ->causedBy($request->user())
            ->performedOn($employment)
            ->event('created')
            ->log('Provider employment created');

        return redirect()->route('providers.profile', $provider)->with('success', 'Employment record added successfully.');
    }

    /**
     * Update the specified employment record.
     */
    public function update(ProviderEmploymentRequest $request, Provider $provider, ProviderEmployment $providerEmployment): RedirectResponse
    {
        abort_unless($providerEmployment->provider_id === $provider->id, 404);

        $validated = $request->validated();

        // Employment records cannot be moved between providers.
        unset($validated['provider_id']);

        // If this is set as current, unset any existing current employment
        if ($validated['is_current'] ?? false) {
            $provider->employments()->where('id', '!=', $providerEmployment->id)->update(['is_current' => false]);
        }

        $providerEmployment->update($validated);

        activity()
            ->causedBy($request->user())
            ->performedOn($providerEmployment)
            ->event('updated')
            ->log('Provider employment updated');

        return redirect()->route('providers.profile', $provider)->with('success', 'Employment record updated successfully.');
    }

    /**
     * Remove the specified employment record.
     */
    public function destroy(Provider $provider, ProviderEmployment $providerEmployment): RedirectResponse
    {
        abort_unless($providerEmployment->provider_id === $provider->id, 404);

        $this->authorize('archive', $provider);
        $user = request()->user();

        DB::transaction(function () use ($providerEmployment, $user) {
            activity()
                ->causedBy($user)
                ->performedOn($providerEmployment)
                ->event('deleted')
                ->log('Provider employment deleted');

            $providerEmployment->delete();
        });

        return redirect()->route('providers.profile', $provider)->with('success', 'Employment record removed successfully.');
    }
}
