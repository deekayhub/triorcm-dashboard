<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProviderContactRequest;
use App\Models\Provider;
use App\Models\ProviderContact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ProviderContactController extends Controller
{
    /**
     * Store a newly created credentialing contact.
     */
    public function store(ProviderContactRequest $request, Provider $provider): RedirectResponse
    {
        $validated = $request->validated();
        $validated['provider_id'] = $provider->id;

        $contact = ProviderContact::create($validated);

        activity()
            ->causedBy($request->user())
            ->performedOn($contact)
            ->event('created')
            ->log('Provider credentialing contact created');

        return redirect()->route('providers.profile', $provider)->with('success', 'Credentialing contact added successfully.');
    }

    /**
     * Update the specified credentialing contact.
     */
    public function update(ProviderContactRequest $request, Provider $provider, ProviderContact $providerContact): RedirectResponse
    {
        $validated = $request->validated();

        $providerContact->update($validated);

        activity()
            ->causedBy($request->user())
            ->performedOn($providerContact)
            ->event('updated')
            ->log('Provider credentialing contact updated');

        return redirect()->route('providers.profile', $provider)->with('success', 'Credentialing contact updated successfully.');
    }

    /**
     * Remove the specified credentialing contact.
     */
    public function destroy(Provider $provider, ProviderContact $providerContact): RedirectResponse
    {
        $this->authorize('archive', $provider);
        $user = request()->user();

        DB::transaction(function () use ($providerContact, $user) {
            activity()
                ->causedBy($user)
                ->performedOn($providerContact)
                ->event('deleted')
                ->log('Provider credentialing contact deleted');

            $providerContact->delete();
        });

        return redirect()->route('providers.profile', $provider)->with('success', 'Credentialing contact removed successfully.');
    }
}
