<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProviderHospitalAffiliationRequest;
use App\Models\Provider;
use App\Models\ProviderHospitalAffiliation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ProviderHospitalAffiliationController extends Controller
{
    /**
     * Store a newly created hospital affiliation.
     */
    public function store(ProviderHospitalAffiliationRequest $request, Provider $provider): RedirectResponse
    {
        $validated = $request->validated();
        $validated['provider_id'] = $provider->id;

        $affiliation = ProviderHospitalAffiliation::create($validated);

        activity()
            ->causedBy($request->user())
            ->performedOn($affiliation)
            ->event('created')
            ->log('Provider hospital affiliation created');

        return redirect()->route('providers.profile', $provider)->with('success', 'Hospital affiliation added successfully.');
    }

    /**
     * Update the specified hospital affiliation.
     */
    public function update(ProviderHospitalAffiliationRequest $request, Provider $provider, ProviderHospitalAffiliation $providerHospitalAffiliation): RedirectResponse
    {
        $validated = $request->validated();

        $providerHospitalAffiliation->update($validated);

        activity()
            ->causedBy($request->user())
            ->performedOn($providerHospitalAffiliation)
            ->event('updated')
            ->log('Provider hospital affiliation updated');

        return redirect()->route('providers.profile', $provider)->with('success', 'Hospital affiliation updated successfully.');
    }

    /**
     * Remove the specified hospital affiliation.
     */
    public function destroy(Provider $provider, ProviderHospitalAffiliation $providerHospitalAffiliation): RedirectResponse
    {
        $this->authorize('archive', $provider);
        $user = request()->user();

        DB::transaction(function () use ($providerHospitalAffiliation, $user) {
            activity()
                ->causedBy($user)
                ->performedOn($providerHospitalAffiliation)
                ->event('deleted')
                ->log('Provider hospital affiliation deleted');

            $providerHospitalAffiliation->delete();
        });

        return redirect()->route('providers.profile', $provider)->with('success', 'Hospital affiliation removed successfully.');
    }
}
