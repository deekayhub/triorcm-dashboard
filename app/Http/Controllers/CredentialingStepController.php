<?php

namespace App\Http\Controllers;

use App\Models\CredentialingStep;
use App\Models\ProviderApplication;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CredentialingStepController extends Controller
{
    public function index(Request $request): Response
    {
        $query = CredentialingStep::with(['application.provider', 'assignedTo', 'completedBy']);

        if ($request->has('provider_application_id')) {
            $query->where('provider_application_id', $request->query('provider_application_id'));
        }

        $steps = $query->latest()->paginate(15);

        return Inertia::render('credentialing-steps/Index', [
            'steps' => $steps,
        ]);
    }

    public function create(Request $request): Response
    {
        $application = $request->query('provider_application_id')
            ? ProviderApplication::findOrFail($request->query('provider_application_id'))
            : null;

        return Inertia::render('credentialing-steps/Create', [
            'application' => $application,
            'applications' => ProviderApplication::with('provider')->orderBy('created_at')->get(['id', 'application_number', 'provider_id', 'status']),
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'provider_application_id' => ['required', 'exists:provider_applications,id'],
            'name' => ['required', 'string', 'max:255'],
            'order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['pending', 'in_progress', 'completed', 'skipped'])],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'started_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date'],
            'completed_by' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($validated['status'] === 'completed' && empty($validated['completed_at'])) {
            $validated['completed_at'] = now();
        }

        $step = CredentialingStep::create($validated);

        activity()
            ->causedBy($request->user())
            ->performedOn($step)
            ->event('created')
            ->log('Credentialing step created');

        return redirect()->route('credentialing-steps.show', $step)->with('success', 'Credentialing step created successfully.');
    }

    public function show(CredentialingStep $credentialingStep): Response
    {
        $credentialingStep->load(['application.provider', 'assignedTo', 'completedBy']);

        return Inertia::render('credentialing-steps/Show', [
            'step' => $credentialingStep,
        ]);
    }

    public function edit(CredentialingStep $credentialingStep): Response
    {
        return Inertia::render('credentialing-steps/Edit', [
            'step' => $credentialingStep,
            'applications' => ProviderApplication::with('provider')->orderBy('created_at')->get(['id', 'application_number', 'provider_id', 'status']),
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    public function update(Request $request, CredentialingStep $credentialingStep): RedirectResponse
    {
        $validated = $request->validate([
            'provider_application_id' => ['required', 'exists:provider_applications,id'],
            'name' => ['required', 'string', 'max:255'],
            'order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['pending', 'in_progress', 'completed', 'skipped'])],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'started_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date'],
            'completed_by' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($validated['status'] === 'completed' && empty($validated['completed_at'])) {
            $validated['completed_at'] = now();
        }

        $credentialingStep->update($validated);

        activity()
            ->causedBy($request->user())
            ->performedOn($credentialingStep)
            ->event('updated')
            ->log('Credentialing step updated');

        return redirect()->route('credentialing-steps.show', $credentialingStep)->with('success', 'Credentialing step updated successfully.');
    }

    public function destroy(CredentialingStep $credentialingStep): RedirectResponse
    {
        activity()
            ->causedBy(request()->user())
            ->performedOn($credentialingStep)
            ->event('deleted')
            ->log('Credentialing step deleted');

        $credentialingStep->delete();

        return redirect()->route('credentialing-steps.index')->with('success', 'Credentialing step deleted successfully.');
    }
}
