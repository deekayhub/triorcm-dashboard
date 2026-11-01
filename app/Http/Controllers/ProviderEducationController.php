<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProviderEducationRequest;
use App\Models\Provider;
use App\Models\ProviderEducation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ProviderEducationController extends Controller
{
    /**
     * Store a newly created education record.
     */
    public function store(ProviderEducationRequest $request, Provider $provider): RedirectResponse
    {
        $validated = $request->validated();
        $validated['provider_id'] = $provider->id;

        $education = ProviderEducation::create($validated);

        activity()
            ->causedBy($request->user())
            ->performedOn($education)
            ->event('created')
            ->log('Provider education created');

        return redirect()->route('providers.profile', $provider)->with('success', 'Education record added successfully.');
    }

    /**
     * Update the specified education record.
     */
    public function update(ProviderEducationRequest $request, Provider $provider, ProviderEducation $providerEducation): RedirectResponse
    {
        $validated = $request->validated();

        $providerEducation->update($validated);

        activity()
            ->causedBy($request->user())
            ->performedOn($providerEducation)
            ->event('updated')
            ->log('Provider education updated');

        return redirect()->route('providers.profile', $provider)->with('success', 'Education record updated successfully.');
    }

    /**
     * Remove the specified education record.
     */
    public function destroy(Provider $provider, ProviderEducation $providerEducation): RedirectResponse
    {
        $this->authorize('archive', $provider);
        $user = request()->user();

        DB::transaction(function () use ($providerEducation, $user) {
            activity()
                ->causedBy($user)
                ->performedOn($providerEducation)
                ->event('deleted')
                ->log('Provider education deleted');

            $providerEducation->delete();
        });

        return redirect()->route('providers.profile', $provider)->with('success', 'Education record deleted successfully.');
    }
}
