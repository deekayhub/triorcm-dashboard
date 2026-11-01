<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProviderDocumentReplaceRequest;
use App\Http\Requests\ProviderDocumentRequest;
use App\Http\Requests\ProviderDocumentReviewRequest;
use App\Http\Resources\ProviderDocumentResource;
use App\Models\Provider;
use App\Models\ProviderApplication;
use App\Models\ProviderDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProviderDocumentController extends Controller
{
    /**
     * Display a listing of provider documents.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Provider::class);

        $query = ProviderDocument::query()
            ->with(['provider', 'application', 'reviewedBy', 'uploadedBy'])
            ->active();

        if ($request->filled('provider_id')) {
            $query->where('provider_id', $request->integer('provider_id'));
        }

        if ($request->filled('application_id')) {
            $query->where('provider_application_id', $request->integer('application_id'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->query('type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        $currentOnly = $request->has('current_only') ? $request->boolean('current_only') : true;

        if ($currentOnly) {
            $query->current();
        }

        $documents = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('provider-documents/Index', [
            'documents' => ProviderDocumentResource::collection($documents),
            'providers' => Provider::query()->orderBy('last_name')->get(['id', 'first_name', 'last_name', 'email']),
            'applications' => ProviderApplication::query()->orderBy('created_at')->get(['id', 'application_number', 'type', 'status']),
            'filters' => [
                'provider_id' => $request->query('provider_id'),
                'application_id' => $request->query('application_id'),
                'type' => $request->query('type'),
                'status' => $request->query('status'),
                'current_only' => $currentOnly,
            ],
        ]);
    }

    /**
     * Show the upload form.
     */
    public function create(Request $request): Response
    {
        abort_unless($request->user()?->can('providers.update'), 403);

        $provider = $request->filled('provider_id')
            ? Provider::query()->findOrFail($request->integer('provider_id'))
            : null;

        $application = $request->filled('application_id')
            ? ProviderApplication::query()->findOrFail($request->integer('application_id'))
            : null;

        return Inertia::render('provider-documents/Create', [
            'provider' => $provider ? [
                'id' => $provider->id,
                'full_name' => $provider->full_name,
                'first_name' => $provider->first_name,
                'last_name' => $provider->last_name,
                'email' => $provider->email,
            ] : null,
            'application' => $application?->only(['id', 'application_number', 'type', 'status']),
            'providers' => Provider::query()->orderBy('last_name')->get(['id', 'first_name', 'last_name', 'email']),
            'applications' => ProviderApplication::query()->orderBy('created_at')->get(['id', 'provider_id', 'application_number', 'type', 'status']),
        ]);
    }

    /**
     * Store a newly uploaded document.
     */
    public function store(ProviderDocumentRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $provider = Provider::query()->findOrFail($validated['provider_id']);

        $this->authorize('update', $provider);

        $document = $this->storeUpload($request->file('file'), $provider, $validated, $request->user()?->id);

        activity()
            ->causedBy($request->user())
            ->performedOn($document)
            ->event('uploaded')
            ->withProperties([
                'provider_id' => $provider->id,
                'version' => $document->version,
                'type' => $document->type,
            ])
            ->log('Provider document uploaded');

        return redirect()
            ->route('provider-documents.show', $document)
            ->with('success', 'Document uploaded successfully.');
    }

    /**
     * Store a document for a provider from the profile section.
     */
    public function storeForProvider(ProviderDocumentRequest $request, Provider $provider): RedirectResponse
    {
        $this->authorize('update', $provider);

        $validated = $request->validated();
        $validated['provider_id'] = $provider->id;

        $document = $this->storeUpload($request->file('file'), $provider, $validated, $request->user()?->id);

        activity()
            ->causedBy($request->user())
            ->performedOn($document)
            ->event('uploaded')
            ->withProperties([
                'provider_id' => $provider->id,
                'version' => $document->version,
                'type' => $document->type,
            ])
            ->log('Provider document uploaded');

        return redirect()
            ->route('providers.profile', $provider)
            ->with('success', 'Document uploaded successfully.');
    }

    /**
     * Display the specified document.
     */
    public function show(Request $request, ProviderDocument $providerDocument): Response
    {
        $providerDocument->load([
            'provider',
            'application',
            'reviewedBy',
            'uploadedBy',
            'archivedBy',
            'versions',
        ]);

        $this->authorize('view', $providerDocument->provider);

        activity()
            ->causedBy($request->user())
            ->performedOn($providerDocument)
            ->event('viewed')
            ->log('Provider document viewed');

        return Inertia::render('provider-documents/Show', [
            'document' => ProviderDocumentResource::make($providerDocument),
        ]);
    }

    /**
     * Show the form for editing document metadata.
     */
    public function edit(ProviderDocument $providerDocument): Response
    {
        $this->authorize('update', $providerDocument->provider);

        $providerDocument->load(['provider', 'application', 'reviewedBy', 'uploadedBy', 'versions']);

        return Inertia::render('provider-documents/Edit', [
            'document' => ProviderDocumentResource::make($providerDocument),
            'applications' => ProviderApplication::query()->orderBy('created_at')->get(['id', 'provider_id', 'application_number', 'type', 'status']),
        ]);
    }

    /**
     * Update document metadata.
     */
    public function update(ProviderDocumentRequest $request, ProviderDocument $providerDocument): RedirectResponse
    {
        $this->authorize('update', $providerDocument->provider);

        $validated = $request->validated();
        unset($validated['provider_id'], $validated['file']);

        $providerDocument->update($validated);

        activity()
            ->causedBy($request->user())
            ->performedOn($providerDocument)
            ->event('updated')
            ->log('Provider document updated');

        return redirect()
            ->route('provider-documents.show', $providerDocument)
            ->with('success', 'Document updated successfully.');
    }

    /**
     * Update document metadata from the provider profile section.
     */
    public function updateForProvider(
        ProviderDocumentRequest $request,
        Provider $provider,
        ProviderDocument $providerDocument,
    ): RedirectResponse {
        abort_unless($providerDocument->provider_id === $provider->id, 404);

        $this->authorize('update', $provider);

        $validated = $request->validated();
        unset($validated['provider_id'], $validated['file']);

        $providerDocument->update($validated);

        activity()
            ->causedBy($request->user())
            ->performedOn($providerDocument)
            ->event('updated')
            ->log('Provider document updated');

        return redirect()
            ->route('providers.profile', $provider)
            ->with('success', 'Document updated successfully.');
    }

    /**
     * Upload a new version of an existing document.
     */
    public function replace(
        ProviderDocumentReplaceRequest $request,
        Provider $provider,
        ProviderDocument $providerDocument,
    ): RedirectResponse {
        abort_unless($providerDocument->provider_id === $provider->id, 404);

        $this->authorize('update', $provider);

        $this->performReplacement($request, $provider, $providerDocument);

        return redirect()
            ->route('providers.profile', $provider)
            ->with('success', 'New document version uploaded. It is pending review.');
    }

    /**
     * Upload a new version from the standalone documents area.
     */
    public function replaceStandalone(
        ProviderDocumentReplaceRequest $request,
        ProviderDocument $providerDocument,
    ): RedirectResponse {
        $provider = $providerDocument->provider;

        $this->authorize('update', $provider);

        $this->performReplacement($request, $provider, $providerDocument);

        return redirect()
            ->route('provider-documents.show', $providerDocument)
            ->with('success', 'New document version uploaded. It is pending review.');
    }

    /**
     * Record a review decision.
     */
    public function review(
        ProviderDocumentReviewRequest $request,
        Provider $provider,
        ProviderDocument $providerDocument,
    ): RedirectResponse {
        abort_unless($providerDocument->provider_id === $provider->id, 404);

        $this->performReview($request, $providerDocument);

        return redirect()
            ->route('providers.profile', $provider)
            ->with('success', $this->reviewMessage($request->validated('status')));
    }

    /**
     * Record a review decision from the standalone documents area.
     */
    public function reviewStandalone(
        ProviderDocumentReviewRequest $request,
        ProviderDocument $providerDocument,
    ): RedirectResponse {
        $this->performReview($request, $providerDocument);

        return redirect()
            ->route('provider-documents.show', $providerDocument)
            ->with('success', $this->reviewMessage($request->validated('status')));
    }

    /**
     * Archive a document without deleting its audit trail.
     */
    public function archive(
        Request $request,
        Provider $provider,
        ProviderDocument $providerDocument,
    ): RedirectResponse {
        abort_unless($providerDocument->provider_id === $provider->id, 404);

        $this->authorize('archive', $provider);

        $this->performArchive($request, $providerDocument);

        return redirect()
            ->route('providers.profile', $provider)
            ->with('success', 'Document archived successfully.');
    }

    /**
     * Archive a document from the standalone documents area.
     */
    public function destroy(Request $request, ProviderDocument $providerDocument): RedirectResponse
    {
        $this->authorize('archive', $providerDocument->provider);

        $this->performArchive($request, $providerDocument);

        return redirect()
            ->route('provider-documents.index')
            ->with('success', 'Document archived successfully.');
    }

    /**
     * Archive a document from its standalone detail page.
     */
    public function archiveStandalone(Request $request, ProviderDocument $providerDocument): RedirectResponse
    {
        $this->authorize('archive', $providerDocument->provider);

        $this->performArchive($request, $providerDocument);

        return redirect()
            ->route('provider-documents.index')
            ->with('success', 'Document archived successfully.');
    }

    /**
     * Stream a private document through a temporary signed URL.
     */
    public function download(Request $request, Provider $provider, ProviderDocument $providerDocument): StreamedResponse
    {
        abort_unless($providerDocument->provider_id === $provider->id, 404);
        $this->authorize('view', $provider);

        $disk = Storage::disk($providerDocument->file_disk ?: 'local');
        $expectedPrefix = 'provider-documents/'.$provider->id.'/';

        abort_unless(
            filled($providerDocument->file_path)
            && Str::startsWith($providerDocument->file_path, $expectedPrefix)
            && $disk->exists($providerDocument->file_path),
            404,
            'The document file could not be found.',
        );

        activity()
            ->causedBy($request->user())
            ->performedOn($providerDocument)
            ->event('downloaded')
            ->withProperties([
                'provider_id' => $provider->id,
                'version' => $providerDocument->version,
            ])
            ->log('Provider document downloaded');

        return $disk->download($providerDocument->file_path, $providerDocument->file_name);
    }

    /**
     * Create a replacement version and retire the previous one.
     */
    private function performReplacement(
        ProviderDocumentReplaceRequest $request,
        Provider $provider,
        ProviderDocument $providerDocument,
    ): ProviderDocument {
        $validated = $request->validated();

        return DB::transaction(function () use ($request, $provider, $providerDocument, $validated) {
            $path = $this->storeFile($request->file('file'), $provider->id);
            $versionGroup = $providerDocument->version_group ?: (string) Str::uuid();

            $providerDocument->update([
                'is_current' => false,
                'version_group' => $versionGroup,
            ]);

            $replacement = ProviderDocument::create([
                'provider_id' => $provider->id,
                'provider_application_id' => $providerDocument->provider_application_id,
                'type' => $providerDocument->type,
                'name' => $validated['name'] ?? $providerDocument->name,
                'file_path' => $path['path'],
                'file_disk' => 'local',
                'file_name' => $path['name'],
                'mime_type' => $path['mime_type'],
                'file_size' => $path['size'],
                'checksum' => $path['checksum'],
                'issue_date' => $validated['issue_date'] ?? $providerDocument->issue_date?->format('Y-m-d'),
                'expiration_date' => $validated['expiration_date'] ?? $providerDocument->expiration_date?->format('Y-m-d'),
                'version' => ((int) $providerDocument->version) + 1,
                'version_group' => $versionGroup,
                'is_current' => true,
                'supersedes_id' => $providerDocument->id,
                // A replacement must be reviewed again.
                'status' => 'pending',
                'reviewed_by' => null,
                'reviewed_at' => null,
                'review_notes' => null,
                'uploaded_by' => $request->user()?->id,
            ]);

            activity()
                ->causedBy($request->user())
                ->performedOn($replacement)
                ->event('replaced')
                ->withProperties([
                    'provider_id' => $provider->id,
                    'supersedes_id' => $providerDocument->id,
                    'version' => $replacement->version,
                ])
                ->log('Provider document replaced');

            return $replacement;
        });
    }

    /**
     * Store a review decision.
     */
    private function performReview(
        ProviderDocumentReviewRequest $request,
        ProviderDocument $providerDocument,
    ): void {
        $validated = $request->validated();

        $providerDocument->update([
            'status' => $validated['status'],
            'review_notes' => $validated['review_notes'] ?? null,
            'reviewed_by' => $request->user()?->id,
            'reviewed_at' => now(),
        ]);

        activity()
            ->causedBy($request->user())
            ->performedOn($providerDocument)
            ->event('reviewed')
            ->withProperties([
                'status' => $validated['status'],
                'version' => $providerDocument->version,
            ])
            ->log('Provider document reviewed');
    }

    private function reviewMessage(string $status): string
    {
        return $status === 'approved' ? 'Document approved.' : 'Document rejected.';
    }

    /**
     * Archive a document and record the audit event.
     */
    private function performArchive(Request $request, ProviderDocument $providerDocument): void
    {
        $this->archiveDocument($providerDocument, $request->user()?->id);

        activity()
            ->causedBy($request->user())
            ->performedOn($providerDocument)
            ->event('archived')
            ->withProperties(['provider_id' => $providerDocument->provider_id])
            ->log('Provider document archived');
    }

    /**
     * Persist an uploaded document with version 1.
     *
     * @param  array<string, mixed>  $data
     */
    private function storeUpload(UploadedFile $file, Provider $provider, array $data, ?int $userId): ProviderDocument
    {
        $stored = $this->storeFile($file, $provider->id);

        return ProviderDocument::create([
            'provider_id' => $provider->id,
            'provider_application_id' => $data['provider_application_id'] ?? null,
            'type' => $data['type'],
            'name' => $data['name'],
            'file_path' => $stored['path'],
            'file_disk' => 'local',
            'file_name' => $stored['name'],
            'mime_type' => $stored['mime_type'],
            'file_size' => $stored['size'],
            'checksum' => $stored['checksum'],
            'issue_date' => $data['issue_date'] ?? null,
            'expiration_date' => $data['expiration_date'] ?? null,
            'version' => 1,
            'version_group' => (string) Str::uuid(),
            'is_current' => true,
            'status' => 'pending',
            'uploaded_by' => $userId,
        ]);
    }

    /**
     * Store a file on the private local disk.
     *
     * @return array{path: string, name: string, mime_type: string|null, size: int, checksum: string}
     */
    private function storeFile(UploadedFile $file, int $providerId): array
    {
        $extension = strtolower($file->extension() ?: $file->getClientOriginalExtension());
        $fileName = Str::uuid().($extension !== '' ? '.'.$extension : '');

        $checksum = hash_file('sha256', $file->getRealPath()) ?: '';

        $path = $file->storeAs('provider-documents/'.$providerId, $fileName, 'local');

        abort_if($path === false, 500, 'The document could not be stored.');

        return [
            'path' => $path,
            'name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType() ?: $file->getClientMimeType(),
            'size' => (int) $file->getSize(),
            'checksum' => $checksum,
        ];
    }

    /**
     * Mark a document as archived.
     */
    private function archiveDocument(ProviderDocument $document, ?int $userId): void
    {
        $document->update([
            'is_current' => false,
            'archived_at' => now(),
            'archived_by' => $userId,
        ]);
    }
}
