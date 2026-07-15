<?php

use App\Http\Controllers\Auth\MeController;
use App\Http\Controllers\Auth\OnboardingController;
use App\Http\Controllers\Auth\SocialLoginController;
use App\Http\Controllers\MediaController;
use Illuminate\Support\Facades\Route;

Route::get('/auth/{provider}/redirect', [SocialLoginController::class, 'redirect'])->name('social.redirect');
Route::get('/auth/{provider}/callback', [SocialLoginController::class, 'callback'])->name('social.callback');
Route::middleware('auth:sanctum')->get('/me', MeController::class)->name('me.show');
Route::middleware('auth:sanctum')->put('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/media', [MediaController::class, 'store'])->name('media.store');
    Route::delete('/media', [MediaController::class, 'destroy'])->name('media.destroy');
});
