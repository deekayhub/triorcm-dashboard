<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use App\Models\ProviderVerification;
use App\Models\ProviderApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProviderVerificationController extends Controller
{
    public function index(Request $request): Response
    {
        $query = ProviderVerification::with(['provider', 'application']);

        if ($request->has('provider_id')) {
            $query->where('provider_id', $request->query('provider_id'));
        }

        if ($request->has('application_id')) {
            $query->where('provider_application_id', $request->query('application_id'));
        }

        $verifications = $query->latest()->paginate(15);

        return Inertia::render('provider-verifications/Index', [
            'verifications' => $verifications,
        ]);
    }

    public function create(Request $request): Response
    {
        $provider = $request->query('provider_id') ? Provider::findOrFail($request->query('provider_id')) : null;
        $application = $request->query('application_id') ? ProviderApplication::findOrFail($request->query('application_id')) : null;

        return Inertia::render('provider-verifications/Create', [
            'provider' => $provider,
            'application' => $application,
            'providers' => Provider::orderBy('last_name')->get(['id', 'first_name', 'last_name', 'email']),
            'applications' => ProviderApplication::orderBy('created_at')->get(['id', 'application_number', 'type', 'status']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'provider_id' => ['required', 'exists:providers,id'],
            'provider_application_id' => ['nullable', 'exists:provider_applications,id'],
            'type' => ['required', Rule::in(['primary_source', 'education', 'training', 'work_history', 'malpractice', 'sanctions', 'other'])],
            'verification_source' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['pending', 'in_progress', 'verified', 'unable_to_verify', 'discrepancy'])],
            'requested_at' => ['nullable', 'date'],
            'received_at' => ['nullable', 'date'],
            'verified_at' => ['nullable', 'date'],
            'verified_by' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
            'findings' => ['nullable', 'string'],
        ]);

        $verification = ProviderVerification::create($validated);

        activity()
            ->causedBy($request->user())
            ->performedOn($verification)
            ->event('created')
            ->log('Provider verification created');

        return redirect()->route('provider-verifications.show', $verification)->with('success', 'Verification created successfully.');
    }

    public function show(ProviderVerification $providerVerification): Response
    {
        $providerVerification->load(['provider', 'application', 'verifiedBy']);

        return Inertia::render('provider-verifications/Show', [
            'verification' => $providerVerification,
        ]);
    }

    public function edit(ProviderVerification $providerVerification): Response
    {
        return Inertia::render('provider-verifications/Edit', [
            'verification' => $providerVerification,
            'providers' => Provider::orderBy('last_name')->get(['id', 'first_name', 'last_name', 'email']),
            'applications' => ProviderApplication::orderBy('created_at')->get(['id', 'application_number', 'type', 'status']),
        ]);
    }

    public function update(Request $request, ProviderVerification $providerVerification): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(['primary_source', 'education', 'training', 'work_history', 'malpractice', 'sanctions', 'other'])],
            'verification_source' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['pending', 'in_progress', 'verified', 'unable_to_verify', 'discrepancy'])],
            'requested_at' => ['nullable', 'date'],
            'received_at' => ['nullable', 'date'],
            'verified_at' => ['nullable', 'date'],
            'verified_by' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
            'findings' => ['nullable', 'string'],
        ]);

        $providerVerification->update($validated);

        activity()
            ->causedBy($request->user())
            ->performedOn($providerVerification)
            ->event('updated')
            ->log('Provider verification updated');

        return redirect()->route('provider-verifications.show', $providerVerification)->with('success', 'Verification updated successfully.');
    }

    public function destroy(ProviderVerification $providerVerification): RedirectResponse
    {
        activity()
            ->causedBy(request()->user())
            ->performedOn($providerVerification)
            ->event('deleted')
            ->log('Provider verification deleted');

        $providerVerification->delete();

        return redirect()->route('provider-verifications.index')->with('success', 'Verification deleted successfully.');
    }
}