<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\CredentialingStepController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProviderApplicationController;
use App\Http\Controllers\ProviderContactController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\ProviderDisclosureController;
use App\Http\Controllers\ProviderDocumentController;
use App\Http\Controllers\ProviderEducationController;
use App\Http\Controllers\ProviderEmploymentController;
use App\Http\Controllers\ProviderHospitalAffiliationController;
use App\Http\Controllers\ProviderIdentifierController;
use App\Http\Controllers\ProviderInsuranceController;
use App\Http\Controllers\ProviderLicenseController;
use App\Http\Controllers\ProviderLocationController;
use App\Http\Controllers\ProviderPersonalDetailController;
use App\Http\Controllers\ProviderReferenceController;
use App\Http\Controllers\ProviderSpecialtyController;
use App\Http\Controllers\ProviderVerificationController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::middleware('guest')->get('/', fn (Request $request) => Inertia::render('auth/Login', [
    'canResetPassword' => Features::enabled(Features::resetPasswords()),
    'status' => $request->session()->get('status'),
]))->name('home');

Route::middleware(['auth', 'verified', 'permission:dashboard.view'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::get('users', [UserController::class, 'index'])
        ->middleware('permission:users.view')
        ->name('users.index');

    Route::get('users/create', [UserController::class, 'create'])
        ->middleware('role:super-admin')
        ->name('users.create');

    Route::post('users', [UserController::class, 'store'])
        ->middleware('role:super-admin')
        ->name('users.store');

    Route::get('users/{user}/edit', [UserController::class, 'edit'])
        ->middleware('role:super-admin')
        ->name('users.edit');

    Route::put('users/{user}', [UserController::class, 'update'])
        ->middleware('role:super-admin')
        ->name('users.update');

    Route::patch('users/{user}', [UserController::class, 'update'])
        ->middleware('role:super-admin')
        ->name('users.update.patch');

    Route::delete('users/{user}', [UserController::class, 'destroy'])
        ->middleware('role:super-admin')
        ->name('users.destroy');

    Route::get('notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');
    Route::get('notifications/unread', [NotificationController::class, 'unread'])
        ->name('notifications.unread');
    Route::post('notifications/{id}/read', [NotificationController::class, 'markAsRead'])
        ->name('notifications.read');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead'])
        ->name('notifications.read-all');

    Route::resource('providers', ProviderController::class);

    Route::get('providers/{provider}/profile', [ProviderController::class, 'profile'])
        ->middleware('permission:providers.view')
        ->name('providers.profile');

    Route::patch('providers/{provider}/personal-details', [ProviderPersonalDetailController::class, 'update'])
        ->middleware('permission:providers.update')
        ->name('providers.personal-details.update');

    Route::post('providers/{provider}/identifiers', [ProviderIdentifierController::class, 'store'])
        ->middleware('permission:providers.update')
        ->name('providers.identifiers.store');

    Route::patch('providers/{provider}/identifiers/{providerIdentifier}', [ProviderIdentifierController::class, 'update'])
        ->middleware('permission:providers.update')
        ->name('providers.identifiers.update');

    Route::delete('providers/{provider}/identifiers/{providerIdentifier}', [ProviderIdentifierController::class, 'destroy'])
        ->middleware('permission:providers.archive')
        ->name('providers.identifiers.destroy');

    Route::post('providers/{provider}/educations', [ProviderEducationController::class, 'store'])
        ->middleware('permission:providers.update')
        ->name('providers.educations.store');

    Route::patch('providers/{provider}/educations/{providerEducation}', [ProviderEducationController::class, 'update'])
        ->middleware('permission:providers.update')
        ->name('providers.educations.update');

    Route::delete('providers/{provider}/educations/{providerEducation}', [ProviderEducationController::class, 'destroy'])
        ->middleware('permission:providers.archive')
        ->name('providers.educations.destroy');

    Route::post('providers/{provider}/specialties', [ProviderSpecialtyController::class, 'store'])
        ->middleware('permission:providers.update')
        ->name('providers.specialties.store');

    Route::patch('providers/{provider}/specialties/{providerSpecialty}', [ProviderSpecialtyController::class, 'update'])
        ->middleware('permission:providers.update')
        ->name('providers.specialties.update');

    Route::delete('providers/{provider}/specialties/{providerSpecialty}', [ProviderSpecialtyController::class, 'destroy'])
        ->middleware('permission:providers.archive')
        ->name('providers.specialties.destroy');

    Route::post('providers/{provider}/locations', [ProviderLocationController::class, 'store'])
        ->middleware('permission:providers.update')
        ->name('providers.locations.store');

    Route::patch('providers/{provider}/locations/{providerLocation}', [ProviderLocationController::class, 'update'])
        ->middleware('permission:providers.update')
        ->name('providers.locations.update');

    Route::delete('providers/{provider}/locations/{providerLocation}', [ProviderLocationController::class, 'destroy'])
        ->middleware('permission:providers.archive')
        ->name('providers.locations.destroy');

    Route::post('providers/{provider}/hospital-affiliations', [ProviderHospitalAffiliationController::class, 'store'])
        ->middleware('permission:providers.update')
        ->name('providers.hospital-affiliations.store');

    Route::patch('providers/{provider}/hospital-affiliations/{providerHospitalAffiliation}', [ProviderHospitalAffiliationController::class, 'update'])
        ->middleware('permission:providers.update')
        ->name('providers.hospital-affiliations.update');

    Route::delete('providers/{provider}/hospital-affiliations/{providerHospitalAffiliation}', [ProviderHospitalAffiliationController::class, 'destroy'])
        ->middleware('permission:providers.archive')
        ->name('providers.hospital-affiliations.destroy');

    Route::post('providers/{provider}/contacts', [ProviderContactController::class, 'store'])
        ->middleware('permission:providers.update')
        ->name('providers.contacts.store');

    Route::patch('providers/{provider}/contacts/{providerContact}', [ProviderContactController::class, 'update'])
        ->middleware('permission:providers.update')
        ->name('providers.contacts.update');

    Route::delete('providers/{provider}/contacts/{providerContact}', [ProviderContactController::class, 'destroy'])
        ->middleware('permission:providers.archive')
        ->name('providers.contacts.destroy');

    Route::post('providers/{provider}/insurances', [ProviderInsuranceController::class, 'store'])
        ->middleware('permission:providers.update')
        ->name('providers.insurances.store');

    Route::patch('providers/{provider}/insurances/{providerInsurance}', [ProviderInsuranceController::class, 'update'])
        ->middleware('permission:providers.update')
        ->name('providers.insurances.update');

    Route::delete('providers/{provider}/insurances/{providerInsurance}', [ProviderInsuranceController::class, 'destroy'])
        ->middleware('permission:providers.archive')
        ->name('providers.insurances.destroy');

    Route::post('providers/{provider}/employments', [ProviderEmploymentController::class, 'store'])
        ->middleware('permission:providers.update')
        ->name('providers.employments.store');

    Route::patch('providers/{provider}/employments/{providerEmployment}', [ProviderEmploymentController::class, 'update'])
        ->middleware('permission:providers.update')
        ->name('providers.employments.update');

    Route::delete('providers/{provider}/employments/{providerEmployment}', [ProviderEmploymentController::class, 'destroy'])
        ->middleware('permission:providers.archive')
        ->name('providers.employments.destroy');

    Route::post('providers/{provider}/references', [ProviderReferenceController::class, 'store'])
        ->middleware('permission:providers.update')
        ->name('providers.references.store');

    Route::patch('providers/{provider}/references/{providerReference}', [ProviderReferenceController::class, 'update'])
        ->middleware('permission:providers.update')
        ->name('providers.references.update');

    Route::delete('providers/{provider}/references/{providerReference}', [ProviderReferenceController::class, 'destroy'])
        ->middleware('permission:providers.archive')
        ->name('providers.references.destroy');

    Route::patch('providers/{provider}/disclosures', [ProviderDisclosureController::class, 'save'])
        ->middleware('permission:providers.update')
        ->name('providers.disclosures.save');

    Route::patch('providers/{provider}/disclosures/{providerDisclosure}/review', [ProviderDisclosureController::class, 'review'])
        ->middleware('permission:providers.viewSensitive')
        ->name('providers.disclosures.review');

    Route::post('providers/{provider}/documents', [ProviderDocumentController::class, 'storeForProvider'])
        ->middleware('permission:providers.update')
        ->name('providers.documents.store');

    Route::patch('providers/{provider}/documents/{providerDocument}', [ProviderDocumentController::class, 'updateForProvider'])
        ->middleware('permission:providers.update')
        ->name('providers.documents.update');

    Route::post('providers/{provider}/documents/{providerDocument}/replace', [ProviderDocumentController::class, 'replace'])
        ->middleware('permission:providers.update')
        ->name('providers.documents.replace');

    Route::patch('providers/{provider}/documents/{providerDocument}/review', [ProviderDocumentController::class, 'review'])
        ->middleware('permission:providers.viewSensitive')
        ->name('providers.documents.review');

    Route::post('providers/{provider}/documents/{providerDocument}/archive', [ProviderDocumentController::class, 'archive'])
        ->middleware('permission:providers.archive')
        ->name('providers.documents.archive');

    Route::get('providers/{provider}/documents/{providerDocument}/download', [ProviderDocumentController::class, 'download'])
        ->middleware(['permission:providers.view', 'signed'])
        ->name('providers.documents.download');

    Route::resource('provider-applications', ProviderApplicationController::class);
    Route::resource('provider-licenses', ProviderLicenseController::class);

    Route::post('provider-documents/{providerDocument}/replace', [ProviderDocumentController::class, 'replaceStandalone'])
        ->middleware('permission:providers.update')
        ->name('provider-documents.replace');

    Route::patch('provider-documents/{providerDocument}/review', [ProviderDocumentController::class, 'reviewStandalone'])
        ->middleware('permission:providers.viewSensitive')
        ->name('provider-documents.review');

    Route::post('provider-documents/{providerDocument}/archive', [ProviderDocumentController::class, 'archiveStandalone'])
        ->middleware('permission:providers.archive')
        ->name('provider-documents.archive');

    Route::resource('provider-documents', ProviderDocumentController::class);
    Route::resource('provider-verifications', ProviderVerificationController::class);
    Route::resource('credentialing-steps', CredentialingStepController::class);

    Route::get('roles', [RoleController::class, 'index'])
        ->middleware('permission:roles.view')
        ->name('roles.index');
    Route::get('roles/create', [RoleController::class, 'create'])
        ->middleware('permission:roles.create')
        ->name('roles.create');
    Route::post('roles', [RoleController::class, 'store'])
        ->middleware('permission:roles.create')
        ->name('roles.store');
    Route::get('roles/{role}/edit', [RoleController::class, 'edit'])
        ->middleware('permission:roles.edit')
        ->name('roles.edit');
    Route::put('roles/{role}', [RoleController::class, 'update'])
        ->middleware('permission:roles.edit')
        ->name('roles.update');
    Route::patch('roles/{role}', [RoleController::class, 'update'])
        ->middleware('permission:roles.edit');
    Route::delete('roles/{role}', [RoleController::class, 'destroy'])
        ->middleware('permission:roles.delete')
        ->name('roles.destroy');

    Route::get('activity-logs', [ActivityLogController::class, 'index'])
        ->middleware('permission:activity-logs.view')
        ->name('activity-logs.index');
});

require __DIR__.'/settings.php';
