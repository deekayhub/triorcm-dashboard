<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProviderPersonalDetailRequest;
use App\Models\Provider;
use App\Models\ProviderPersonalDetail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ProviderPersonalDetailController extends Controller
{
    /**
     * Update the personal details section for a provider.
     */
    public function update(ProviderPersonalDetailRequest $request, Provider $provider): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();
        $userId = $user?->id;

        DB::transaction(function () use ($provider, $validated, $userId, $user) {
            // Update core provider fields if present
            $providerFields = array_intersect_key($validated, array_flip([
                'first_name', 'middle_name', 'last_name', 'email', 'phone',
                'dob', 'gender',
            ]));

            if (! empty($providerFields)) {
                $providerFields['updated_by'] = $userId;
                $provider->update($providerFields);
            }

            // Update or create personal details
            $detailFields = array_intersect_key($validated, array_flip([
                'alternate_phone', 'address', 'city', 'state', 'zip_code',
                'country', 'languages', 'previous_names',
            ]));

            if (! empty($detailFields)) {
                $provider->personalDetail()->updateOrCreate(
                    ['provider_id' => $provider->id],
                    $detailFields
                );
            }
        });

        activity()
            ->causedBy($user)
            ->performedOn($provider)
            ->event('updated')
            ->log('Provider personal information updated');

        return back()->with('success', 'Personal information saved successfully.');
    }
}
