<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    
    // OTP verification routes (for guest users)
    Route::post('/verify-registration-otp', [AuthController::class, 'verifyRegistrationOtp'])->name('verify-registration-otp');
    Route::post('/resend-otp', [AuthController::class, 'resendOtp'])->name('resend-otp');
});

// OTP verification route (no middleware - can be accessed by both guest and authenticated users)
Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->name('verify-otp');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', function () {
        return inertia('Dashboard');
    })->name('dashboard');
    Route::get('/dictionary', function (\Illuminate\Http\Request $request) {
        return inertia('Dictionary', [
            'query' => $request->query('q'),
        ]);
    })->name('dictionary');
    Route::get('/favorites', function () {
        return inertia('Favorites');
    })->name('favorites');
    Route::get('/settings', function () {
        return inertia('Settings');
    })->name('settings');
});
