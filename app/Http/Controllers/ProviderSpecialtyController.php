<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProviderSpecialtyRequest;
use App\Models\Provider;
use App\Models\ProviderSpecialty;
use App\Models\Specialty;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ProviderSpecialtyController extends Controller
{
    /**
     * Store a newly created provider specialty.
     */
    public function store(ProviderSpecialtyRequest $request, Provider $provider): RedirectResponse
    {
        $validated = $request->validated();
        $validated['provider_id'] = $provider->id;

        DB::transaction(function () use ($provider, $validated, $request) {
            // If this is set as primary, unset any existing primary
            if ($validated['is_primary'] ?? false) {
                $provider->providerSpecialties()->update(['is_primary' => false]);
            }

            $providerSpecialty = ProviderSpecialty::create($validated);

            activity()
                ->causedBy($request->user())
                ->performedOn($providerSpecialty)
                ->event('created')
                ->log('Provider specialty created');
        });

        return redirect()->route('providers.profile', $provider)->with('success', 'Specialty added successfully.');
    }

    /**
     * Update the specified provider specialty.
     */
    public function update(ProviderSpecialtyRequest $request, Provider $provider, ProviderSpecialty $providerSpecialty): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($provider, $providerSpecialty, $validated, $request) {
            // If this is set as primary, unset any existing primary
            if ($validated['is_primary'] ?? false) {
                $provider->providerSpecialties()->where('id', '!=', $providerSpecialty->id)->update(['is_primary' => false]);
            }

            $providerSpecialty->update($validated);

            activity()
                ->causedBy($request->user())
                ->performedOn($providerSpecialty)
                ->event('updated')
                ->log('Provider specialty updated');
        });

        return redirect()->route('providers.profile', $provider)->with('success', 'Specialty updated successfully.');
    }

    /**
     * Remove the specified provider specialty.
     */
    public function destroy(Provider $provider, ProviderSpecialty $providerSpecialty): RedirectResponse
    {
        $this->authorize('archive', $provider);
        $user = request()->user();

        DB::transaction(function () use ($providerSpecialty, $user) {
            activity()
                ->causedBy($user)
                ->performedOn($providerSpecialty)
                ->event('deleted')
                ->log('Provider specialty deleted');

            $providerSpecialty->delete();
        });

        return redirect()->route('providers.profile', $provider)->with('success', 'Specialty removed successfully.');
    }
}
