<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use App\Models\ProviderLicense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProviderLicenseController extends Controller
{
    public function index(Request $request): Response
    {
        $query = ProviderLicense::with(['provider']);

        if ($request->has('provider_id')) {
            $query->where('provider_id', $request->query('provider_id'));
        }

        $licenses = $query->latest()->paginate(15);

        return Inertia::render('provider-licenses/Index', [
            'licenses' => $licenses,
        ]);
    }

    public function create(Request $request): Response
    {
        $provider = $request->query('provider_id') ? Provider::findOrFail($request->query('provider_id')) : null;

        return Inertia::render('provider-licenses/Create', [
            'provider' => $provider,
            'providers' => Provider::orderBy('last_name')->get(['id', 'first_name', 'last_name', 'email']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'provider_id' => ['required', 'exists:providers,id'],
            'license_number' => ['required', 'string', 'max:100', Rule::unique('provider_licenses', 'license_number')],
            'license_type' => ['required', Rule::in(['md', 'do', 'np', 'pa', 'rn', 'other'])],
            'state' => ['required', 'string', 'max:2'] . Rule::unique('provider_licenses', 'state')->ignore(fn($q) => $q->where('provider_id', $request->provider_id)),
            'issue_date' => ['required', 'date'],
            'expiration_date' => ['required', 'date'],
            'status' => ['required', Rule::in(['active', 'expired', 'suspended', 'revoked'])],
            'issuing_board' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['provider_id'] = $request->provider_id;

        $license = ProviderLicense::create($validated);

        activity()
            ->causedBy($request->user())
            ->performedOn($license)
            ->event('created')
            ->log('Provider license created');

        return redirect()->route('provider-licenses.show', $license)->with('success', 'License created successfully.');
    }

    public function show(ProviderLicense $providerLicense): Response
    {
        $providerLicense->load(['provider']);

        return Inertia::render('provider-licenses/Show', [
            'license' => $providerLicense,
        ]);
    }

    public function edit(ProviderLicense $providerLicense): Response
    {
        return Inertia::render('provider-licenses/Edit', [
            'license' => $providerLicense,
            'providers' => Provider::orderBy('last_name')->get(['id', 'first_name', 'last_name', 'email']),
        ]);
    }

    public function update(Request $request, ProviderLicense $providerLicense): RedirectResponse
    {
        $validated = $request->validate([
            'license_type' => ['required', Rule::in(['md', 'do', 'np', 'pa', 'rn', 'other'])],
            'state' => ['required', 'string', 'max:2'] . Rule::unique('provider_licenses', 'state')->ignore($providerLicense->id),
            'issue_date' => ['required', 'date'],
            'expiration_date' => ['required', 'date'],
            'status' => ['required', Rule::in(['active', 'expired', 'suspended', 'revoked'])],
            'issuing_board' => ['nullable', 'string', 'max:255'],
        ]);

        $providerLicense->update($validated);

        activity()
            ->causedBy($request->user())
            ->performedOn($providerLicense)
            ->event('updated')
            ->log('Provider license updated');

        return redirect()->route('provider-licenses.show', $providerLicense)->with('success', 'License updated successfully.');
    }

    public function destroy(ProviderLicense $providerLicense): RedirectResponse
    {
        activity()
            ->causedBy(request()->user())
            ->performedOn($providerLicense)
            ->event('deleted')
            ->log('Provider license deleted');

        $providerLicense->delete();

        return redirect()->route('provider-licenses.index')->with('success', 'License deleted successfully.');
    }
}