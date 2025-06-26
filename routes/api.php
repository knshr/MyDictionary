<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DictionaryController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\SettingsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Authentication Routes (Public)
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/verify-registration-otp', [AuthController::class, 'verifyRegistrationOtp']);
    Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
    Route::post('/otp-status', [AuthController::class, 'getOtpStatus']);
});

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    // Authentication Routes (Protected)
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    // Dictionary Routes (Protected)
    Route::prefix('dictionary')->group(function () {
        Route::get('/search', [\App\Http\Controllers\Api\DictionaryController::class, 'search']);
        Route::get('/search/{word}', [DictionaryController::class, 'search']);
        Route::get('/{word}/definitions', [DictionaryController::class, 'definitions']);
        Route::get('/{word}/synonyms', [DictionaryController::class, 'synonyms']);
        Route::get('/{word}/antonyms', [DictionaryController::class, 'antonyms']);
        Route::get('/{word}/examples', [DictionaryController::class, 'examples']);
        Route::get('/{word}/pronunciation', [DictionaryController::class, 'pronunciation']);
        Route::delete('/{word}/cache', [DictionaryController::class, 'clearCache']);
    });

    // Favorites Routes
    Route::prefix('favorites')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\FavoriteController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Api\FavoriteController::class, 'store']);
        Route::put('/{favorite}', [\App\Http\Controllers\Api\FavoriteController::class, 'update']);
        Route::delete('/{favorite}', [\App\Http\Controllers\Api\FavoriteController::class, 'destroy']);
    });

    // Settings Routes
    Route::prefix('settings')->group(function () {
        Route::get('/cleanup', [SettingsController::class, 'getCleanupSettings']);
        Route::put('/cleanup', [SettingsController::class, 'updateCleanupSettings']);
    });
});

// Public Dictionary Routes (for basic word search)
Route::prefix('dictionary')->group(function () {
    Route::get('/public/search/{word}', [DictionaryController::class, 'search']);
    Route::get('/public/{word}/definitions', [DictionaryController::class, 'definitions']);
    Route::get('/public/{word}/synonyms', [DictionaryController::class, 'synonyms']);
    Route::get('/public/{word}/antonyms', [DictionaryController::class, 'antonyms']);
    Route::get('/public/{word}/examples', [DictionaryController::class, 'examples']);
    Route::get('/public/{word}/pronunciation', [DictionaryController::class, 'pronunciation']);
});
