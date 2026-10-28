<?php

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

    Route::post('users', [UserController::class, 'store'])
        ->middleware('role:super-admin')
        ->name('users.store');

    Route::put('users/{user}', [UserController::class, 'update'])
        ->middleware('role:super-admin')
        ->name('users.update');

    Route::patch('users/{user}', [UserController::class, 'update'])
        ->middleware('role:super-admin')
        ->name('users.update.patch');

    Route::delete('users/{user}', [UserController::class, 'destroy'])
        ->middleware('role:super-admin')
        ->name('users.destroy');

    Route::get('roles', [RoleController::class, 'index'])
        ->middleware('permission:roles.view')
        ->name('roles.index');

    Route::get('activity-logs', [\App\Http\Controllers\ActivityLogController::class, 'index'])
        ->middleware('permission:activity-logs.view')
        ->name('activity-logs.index');
});

require __DIR__.'/settings.php';
