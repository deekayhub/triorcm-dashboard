<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProviderStoreRequest;
use App\Http\Requests\ProviderUpdateRequest;
use App\Http\Resources\ProviderResource;
use App\Models\DisclosureQuestion;
use App\Models\Provider;
use App\Models\Specialty;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Yajra\DataTables\Facades\DataTables;

class ProviderController extends Controller
{
    public function index(Request $request): Response|JsonResponse
    {
        $this->authorize('viewAny', Provider::class);

        if ($request->ajax() && $request->has('draw')) {
            $query = Provider::query()
                ->with(['user', 'team', 'creator', 'updater'])
                ->withCount(['applications', 'licenses', 'documents']);

            return DataTables::of($query)
                ->addColumn('provider', function (Provider $provider) {
                    return [
                        'id' => $provider->id,
                        'name' => $provider->full_name,
                        'email' => $provider->email,
                        'npi' => $provider->npi,
                    ];
                })
                ->addColumn('specialty', function (Provider $provider) {
                    return $provider->specialty;
                })
                ->addColumn('status', function (Provider $provider) {
                    return $provider->status;
                })
                ->addColumn('team', function (Provider $provider) {
                    return $provider->team?->name ?? '-';
                })
                ->addColumn('applications_count', function (Provider $provider) {
                    return $provider->applications_count;
                })
                ->addColumn('actions', function (Provider $provider) {
                    return $provider->id;
                })
                ->filterColumn('provider', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('first_name', 'like', "%{$keyword}%")
                            ->orWhere('last_name', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%")
                            ->orWhere('npi', 'like', "%{$keyword}%");
                    });
                })
                ->orderColumn('provider', function ($query, $order) {
                    $query->orderBy('last_name', $order)->orderBy('first_name', $order);
                })
                ->rawColumns(['provider', 'status', 'actions'])
                ->make(true);
        }

        return Inertia::render('providers/Index', [
            'stats' => [
                'total' => Provider::count(),
                'active' => Provider::where('status', 'active')->count(),
                'pending' => Provider::where('status', 'pending')->count(),
                'inactive' => Provider::where('status', 'inactive')->count(),
            ],
            'providers' => Provider::query()
                ->with(['user', 'team'])
                ->latest()
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Provider::class);

        return Inertia::render('providers/Create', [
            'teams' => Team::query()->orderBy('name')->get(['id', 'name']),
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(ProviderStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['created_by'] = $request->user()?->id;
        $validated['updated_by'] = $request->user()?->id;

        $provider = Provider::create($validated);

        activity()
            ->causedBy($request->user())
            ->performedOn($provider)
            ->event('created')
            ->log('Provider created');

        return redirect()->route('providers.show', $provider)->with('success', 'Provider created successfully.');
    }

    public function show(Provider $provider): Response
    {
        $this->authorize('view', $provider);

        $provider->load(['applications.steps', 'applications.submittedBy', 'applications.reviewedBy', 'licenses', 'documents.uploadedBy', 'documents.reviewedBy', 'verifications.verifiedBy', 'user', 'team', 'creator', 'updater']);

        return Inertia::render('providers/Show', [
            'provider' => new ProviderResource($provider),
        ]);
    }

    public function edit(Provider $provider): Response
    {
        $this->authorize('update', $provider);

        return Inertia::render('providers/Edit', [
            'provider' => new ProviderResource($provider),
            'teams' => Team::query()->orderBy('name')->get(['id', 'name']),
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(ProviderUpdateRequest $request, Provider $provider): RedirectResponse
    {
        $validated = $request->validated();
        $validated['updated_by'] = $request->user()?->id;

        $provider->update($validated);

        activity()
            ->causedBy($request->user())
            ->performedOn($provider)
            ->event('updated')
            ->log('Provider updated');

        return redirect()->route('providers.show', $provider)->with('success', 'Provider updated successfully.');
    }

    public function destroy(Provider $provider): RedirectResponse
    {
        $this->authorize('archive', $provider);

        activity()
            ->causedBy(request()->user())
            ->performedOn($provider)
            ->event('deleted')
            ->log('Provider deleted');

        $provider->delete();

        return redirect()->route('providers.index')->with('success', 'Provider deleted successfully.');
    }

    public function profile(Provider $provider): Response
    {
        $this->authorize('view', $provider);

        $provider->load(['user', 'team', 'creator', 'updater', 'personalDetail', 'identifiers', 'educations', 'providerSpecialties.specialty', 'locations', 'hospitalAffiliations', 'contacts', 'insurances', 'employments', 'references', 'disclosures.reviewer', 'applications', 'licenses', 'documents' => fn ($query) => $query->current()->with(['reviewedBy', 'uploadedBy', 'versions'])]);

        return Inertia::render('providers/Profile', [
            'provider' => new ProviderResource($provider),
            'availableSpecialties' => Specialty::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']),
            'disclosureQuestions' => DisclosureQuestion::query()->active()->ordered()->get(['id', 'category', 'question', 'response_type', 'is_required', 'is_active', 'order']),
        ]);
    }
}
