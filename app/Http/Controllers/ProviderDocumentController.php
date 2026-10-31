<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use App\Models\ProviderDocument;
use App\Models\ProviderApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProviderDocumentController extends Controller
{
    public function index(Request $request): Response
    {
        $query = ProviderDocument::with(['provider', 'application']);

        if ($request->has('provider_id')) {
            $query->where('provider_id', $request->query('provider_id'));
        }

        if ($request->has('application_id')) {
            $query->where('provider_application_id', $request->query('application_id'));
        }

        $documents = $query->latest()->paginate(15);

        return Inertia::render('provider-documents/Index', [
            'documents' => $documents,
        ]);
    }

    public function create(Request $request): Response
    {
        $provider = $request->query('provider_id') ? Provider::findOrFail($request->query('provider_id')) : null;
        $application = $request->query('application_id') ? ProviderApplication::findOrFail($request->query('application_id')) : null;

        return Inertia::render('provider-documents/Create', [
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
            'type' => ['required', Rule::in(['cv', 'license', 'dea', 'malpractice', 'education', 'certification', 'other'])],
            'name' => ['required', 'string', 'max:255'],
            'file_path' => ['required', 'string'],
            'file_name' => ['required', 'string'],
            'mime_type' => ['nullable', 'string'],
            'file_size' => ['nullable', 'integer'],
            'status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
            'reviewed_by' => ['nullable', 'exists:users,id'],
            'reviewed_at' => ['nullable', 'date'],
            'review_notes' => ['nullable', 'string'],
            'uploaded_by' => ['nullable', 'exists:users,id'],
        ]);

        $document = ProviderDocument::create($validated);

        activity()
            ->causedBy($request->user())
            ->performedOn($document)
            ->event('created')
            ->log('Provider document created');

        return redirect()->route('provider-documents.show', $document)->with('success', 'Document created successfully.');
    }

    public function show(ProviderDocument $providerDocument): Response
    {
        $providerDocument->load(['provider', 'application', 'reviewedBy', 'uploadedBy']);

        return Inertia::render('provider-documents/Show', [
            'document' => $providerDocument,
        ]);
    }

    public function edit(ProviderDocument $providerDocument): Response
    {
        return Inertia::render('provider-documents/Edit', [
            'document' => $providerDocument,
            'providers' => Provider::orderBy('last_name')->get(['id', 'first_name', 'last_name', 'email']),
            'applications' => ProviderApplication::orderBy('created_at')->get(['id', 'application_number', 'type', 'status']),
        ]);
    }

    public function update(Request $request, ProviderDocument $providerDocument): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(['cv', 'license', 'dea', 'malpractice', 'education', 'certification', 'other'])],
            'name' => ['required', 'string', 'max:255'],
            'file_path' => ['required', 'string'],
            'file_name' => ['required', 'string'],
            'mime_type' => ['nullable', 'string'],
            'file_size' => ['nullable', 'integer'],
            'status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
            'reviewed_by' => ['nullable', 'exists:users,id'],
            'reviewed_at' => ['nullable', 'date'],
            'review_notes' => ['nullable', 'string'],
            'uploaded_by' => ['nullable', 'exists:users,id'],
        ]);

        $providerDocument->update($validated);

        activity()
            ->causedBy($request->user())
            ->performedOn($providerDocument)
            ->event('updated')
            ->log('Provider document updated');

        return redirect()->route('provider-documents.show', $providerDocument)->with('success', 'Document updated successfully.');
    }

    public function destroy(ProviderDocument $providerDocument): RedirectResponse
    {
        activity()
            ->causedBy(request()->user())
            ->performedOn($providerDocument)
            ->event('deleted')
            ->log('Provider document deleted');

        $providerDocument->delete();

        return redirect()->route('provider-documents.index')->with('success', 'Document deleted successfully.');
    }
}