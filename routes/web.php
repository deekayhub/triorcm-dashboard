<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::middleware('guest')->get('/', fn (Request $request) => Inertia::render('auth/Login', [
    'canResetPassword' => Features::enabled(Features::resetPasswords()),
    'status' => $request->session()->get('status'),
]))->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
