<?php

use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\MeController;
use App\Http\Controllers\Auth\OnboardingController;
use App\Http\Controllers\Auth\SocialLoginController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\ThreadController;
use Illuminate\Support\Facades\Route;

// Social login uses full-page browser redirects (Google/Facebook -> callback),
// so these routes get the "web" group to start a session. The callback logs the
// user in and sets the httpOnly session cookie the SPA relies on.
Route::middleware('web')->group(function () {
    Route::get('/auth/{provider}/redirect', [SocialLoginController::class, 'redirect'])->name('social.redirect');
    Route::get('/auth/{provider}/callback', [SocialLoginController::class, 'callback'])->name('social.callback');
});

//Onboarding routes
Route::middleware('auth:sanctum')->put('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');

//Sidebar and user profile routes
Route::get('/forums', [ForumController::class, 'index'])->name('forums.index');
Route::get('/cities', [CityController::class, 'index'])->name('cities.index');

Route::middleware('auth:sanctum')->get('/me', MeController::class)->name('me.show');
Route::middleware('auth:sanctum')->post('/logout', LogoutController::class)->name('auth.logout');

//Forum page routes
Route::get('/p/{forum:slug}', [ForumController::class, 'show'])->name('forums.show');
Route::get('/p/{forum:slug}/comments/{thread:id}', [ThreadController::class, 'show'])->name('forums.threads.show');

//Storage routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/media', [MediaController::class, 'store'])->name('media.store');
    Route::delete('/media', [MediaController::class, 'destroy'])->name('media.destroy');
});
