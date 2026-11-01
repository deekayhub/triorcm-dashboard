<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use App\Models\ProviderLicense;
use App\Models\ProviderInsurance;
use App\Models\ProviderApplication;
use Inertia\Inertia;
use Inertia\Response;

class ProviderDashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('provider-dashboard/Index', [
            'kpis' => [
                'providers' => Provider::count(),
                'applications' => ProviderApplication::count(),
                'licenses' => ProviderLicense::count(),
                'expiring_licenses' => ProviderLicense::where('expiration_date', '<=', now()->addDays(30))->count(),
                'insurance' => ProviderInsurance::count(),
            ],
        ]);
    }
}
