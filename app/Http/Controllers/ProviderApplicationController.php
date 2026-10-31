<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use App\Models\ProviderApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProviderApplicationController extends Controller
{
    public function index(Request $request): Response
    {
        $applications = ProviderApplication::with('provider')
            ->latest()
            ->paginate(15);

        return Inertia::render('provider-applications/Index', [
            'applications' => $applications,
        ]);
    }

    public function create(Request $request): Response
    {
        $provider = $request->query('provider_id') ? Provider::findOrFail($request->query('provider_id')) : null;

        return Inertia::render('provider-applications/Create', [
            'provider' => $provider,
            'providers' => Provider::orderBy('last_name')->get(['id', 'first_name', 'last_name', 'email']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'provider_id' => ['required', 'exists:providers,id'],
            'type' => ['required', Rule::in(['initial', 'recredentialing', 'modification'])],
            'status' => ['required', Rule::in(['draft', 'submitted', 'in_review', 'approved', 'denied', 'withdrawn'])],
            'effective_date' => ['nullable', 'date'],
            'expiration_date' => ['nullable', 'date'],
            'review_notes' => ['nullable', 'string'],
        ]);

        $validated['application_number'] = 'APP-' . strtoupper(Str::random(10));
        $validated['submitted_by'] = $request->user()?->id;

        if ($validated['status'] === 'submitted' && empty($validated['submitted_at'])) {
            $validated['submitted_at'] = now();
        }

        $application = ProviderApplication::create($validated);

        activity()
            ->causedBy($request->user())
            ->performedOn($application)
            ->event('created')
            ->log('Provider application created');

        return redirect()->route('provider-applications.show', $application)->with('success', 'Application created successfully.');
    }

    public function show(ProviderApplication $providerApplication): Response
    {
        $providerApplication->load(['provider', 'submittedBy', 'reviewedBy', 'documents.uploadedBy', 'documents.reviewedBy', 'verifications.verifiedBy', 'steps.assignedTo', 'steps.completedBy']);

        return Inertia::render('provider-applications/Show', [
            'application' => $providerApplication,
        ]);
    }

    public function edit(ProviderApplication $providerApplication): Response
    {
        return Inertia::render('provider-applications/Edit', [
            'application' => $providerApplication,
            'providers' => Provider::orderBy('last_name')->get(['id', 'first_name', 'last_name', 'email']),
        ]);
    }

    public function update(Request $request, ProviderApplication $providerApplication): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(['initial', 'recredentialing', 'modification'])],
            'status' => ['required', Rule::in(['draft', 'submitted', 'in_review', 'approved', 'denied', 'withdrawn'])],
            'effective_date' => ['nullable', 'date'],
            'expiration_date' => ['nullable', 'date'],
            'review_notes' => ['nullable', 'string'],
        ]);

        if ($validated['status'] === 'submitted' && $providerApplication->status !== 'submitted') {
            $validated['submitted_at'] = now();
            $validated['submitted_by'] = $validated['submitted_by'] ?? $request->user()?->id;
        }

        if (in_array($validated['status'], ['approved', 'denied']) && $providerApplication->status !== $validated['status']) {
            $validated['reviewed_at'] = now();
            $validated['reviewed_by'] = $request->user()?->id;
        }

        $providerApplication->update($validated);

        activity()
            ->causedBy($request->user())
            ->performedOn($providerApplication)
            ->event('updated')
            ->log('Provider application updated');

        return redirect()->route('provider-applications.show', $providerApplication)->with('success', 'Application updated successfully.');
    }

    public function destroy(ProviderApplication $providerApplication): RedirectResponse
    {
        activity()
            ->causedBy(request()->user())
            ->performedOn($providerApplication)
            ->event('deleted')
            ->log('Provider application deleted');

        $providerApplication->delete();

        return redirect()->route('provider-applications.index')->with('success', 'Application deleted successfully.');
    }
}
