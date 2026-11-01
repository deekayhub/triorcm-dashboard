<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProviderInsuranceRequest;
use App\Models\Provider;
use App\Models\ProviderInsurance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ProviderInsuranceController extends Controller
{
    /**
     * Store a newly created insurance policy.
     */
    public function store(ProviderInsuranceRequest $request, Provider $provider): RedirectResponse
    {
        $validated = $request->validated();
        $validated['provider_id'] = $provider->id;

        $insurance = ProviderInsurance::create($validated);

        activity()
            ->causedBy($request->user())
            ->performedOn($insurance)
            ->event('created')
            ->log('Provider insurance created');

        return redirect()->route('providers.profile', $provider)->with('success', 'Insurance policy added successfully.');
    }

    /**
     * Update the specified insurance policy.
     */
    public function update(ProviderInsuranceRequest $request, Provider $provider, ProviderInsurance $providerInsurance): RedirectResponse
    {
        $validated = $request->validated();

        $providerInsurance->update($validated);

        activity()
            ->causedBy($request->user())
            ->performedOn($providerInsurance)
            ->event('updated')
            ->log('Provider insurance updated');

        return redirect()->route('providers.profile', $provider)->with('success', 'Insurance policy updated successfully.');
    }

    /**
     * Remove the specified insurance policy.
     */
    public function destroy(Provider $provider, ProviderInsurance $providerInsurance): RedirectResponse
    {
        $this->authorize('archive', $provider);
        $user = request()->user();

        DB::transaction(function () use ($providerInsurance, $user) {
            activity()
                ->causedBy($user)
                ->performedOn($providerInsurance)
                ->event('deleted')
                ->log('Provider insurance deleted');

            $providerInsurance->delete();
        });

        return redirect()->route('providers.profile', $provider)->with('success', 'Insurance policy removed successfully.');
    }
}
