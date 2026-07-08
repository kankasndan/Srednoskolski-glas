<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SocialLoginController;


Route::get('login', function () {
    return view('login');
});
Route::get('api/auth/{provider}/redirect', [SocialLoginController::class, 'redirect'])->name('social.redirect'); // Mora da pocnuva na api sekoja ruta
Route::get('api/auth/{provider}/callback', [SocialLoginController::class, 'callback'])->name('social.callback');
