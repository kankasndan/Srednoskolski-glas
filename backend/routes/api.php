<?php

use App\Http\Controllers\Auth\MeController;
use App\Http\Controllers\Auth\OnboardingController;
use App\Http\Controllers\Auth\SocialLoginController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\ThreadController;
use Illuminate\Support\Facades\Route;

//Social login routes
Route::get('/auth/{provider}/redirect', [SocialLoginController::class, 'redirect'])->name('social.redirect');
Route::get('/auth/{provider}/callback', [SocialLoginController::class, 'callback'])->name('social.callback');

//Onboarding routes
Route::middleware('auth:sanctum')->put('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');

//Sidebar and user profile routes
Route::get('/forums', [ForumController::class, 'index'])->name('forums.index');
Route::get('/cities', [CityController::class, 'index'])->name('cities.index');
Route::middleware('auth:sanctum')->get('/me', MeController::class)->name('me.show');

//Forum page routes
Route::get('/p/{forum:slug}', [ForumController::class, 'show'])->name('forums.show');
Route::get('/p/{forum:slug}/comments/{thread:id}', [ThreadController::class, 'show'])->name('forums.threads.show');

//Storage routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/media', [MediaController::class, 'store'])->name('media.store');
    Route::delete('/media', [MediaController::class, 'destroy'])->name('media.destroy');
});
