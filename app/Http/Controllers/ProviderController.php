<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Yajra\DataTables\Facades\DataTables;

class ProviderController extends Controller
{
    public function index(Request $request): Response|\Illuminate\Http\JsonResponse
    {
        if ($request->ajax() && $request->has('draw')) {
            $query = Provider::query()->withCount(['applications', 'licenses', 'documents']);

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
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('providers/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:providers,email'],
            'npi' => ['nullable', 'string', 'max:10', 'unique:providers,npi'],
            'phone' => ['nullable', 'string', 'max:20'],
            'fax' => ['nullable', 'string', 'max:20'],
            'dob' => ['nullable', 'date'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'provider_type' => ['required', Rule::in(['physician', 'nurse_practitioner', 'physician_assistant', 'other'])],
            'specialty' => ['nullable', 'string', 'max:255'],
            'taxonomy_code' => ['nullable', 'string', 'max:10'],
            'status' => ['required', Rule::in(['active', 'inactive', 'pending', 'suspended'])],
            'notes' => ['nullable', 'string'],
        ]);

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
        $provider->load(['applications.steps', 'applications.submittedBy', 'applications.reviewedBy', 'licenses', 'documents.uploadedBy', 'documents.reviewedBy', 'verifications.verifiedBy']);

        return Inertia::render('providers/Show', [
            'provider' => $provider,
        ]);
    }

    public function edit(Provider $provider): Response
    {
        return Inertia::render('providers/Edit', [
            'provider' => $provider,
        ]);
    }

    public function update(Request $request, Provider $provider): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('providers', 'email')->ignore($provider->id)],
            'npi' => ['nullable', 'string', 'max:10', Rule::unique('providers', 'npi')->ignore($provider->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'fax' => ['nullable', 'string', 'max:20'],
            'dob' => ['nullable', 'date'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'provider_type' => ['required', Rule::in(['physician', 'nurse_practitioner', 'physician_assistant', 'other'])],
            'specialty' => ['nullable', 'string', 'max:255'],
            'taxonomy_code' => ['nullable', 'string', 'max:10'],
            'status' => ['required', Rule::in(['active', 'inactive', 'pending', 'suspended'])],
            'notes' => ['nullable', 'string'],
        ]);

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
        activity()
            ->causedBy(request()->user())
            ->performedOn($provider)
            ->event('deleted')
            ->log('Provider deleted');

        $provider->delete();

        return redirect()->route('providers.index')->with('success', 'Provider deleted successfully.');
    }
}

