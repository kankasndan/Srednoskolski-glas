<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AuthContoller;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::get("admin/login", [AuthContoller::class, "index"])->name("login");
Route::post("admin/login/login", [AuthContoller::class, "login"])->name("admin.login");

Route::prefix("admin")->middleware("auth")->group(function () {
    Route::get("dashboard", [AdminController::class, "index"])->name("admin.dashboard");
    
    // UPDATE PROFILE
        Route::get('profile/{user}', [AdminController::class, 'profile'])->name('admin.profile');
        Route::patch('profile/{user}/update', [AdminController::class, 'update'])->name('profile.update');
        Route::patch('profile/{user}/images', [AdminController::class, 'updateImages'])->name('profile.updateImages');
        Route::patch('profile/{user}/password', [AdminController::class, 'updatePassword'])->name('profile.updatePassword');

    // COMUNITY

        // USERS
            Route::get("users", [UserController::class, "index"])->name("user.index");

            // SEARCH USERS
            Route::get("users/liveSearch", [UserController::class, "liveSearch"])->name("user.liveSearch");
            Route::get("users/{user}/show", [UserController::class, "show"])->name("user.show");

            // EXPORT USER AS PDF
            Route::get('/admin/users/{user}/export', [UserController::class, 'export'])->name('user.export');
   
   
    // ROLES AND PERMISSION
    Route::get("roles", [RoleController::class, "index"])->name('role.index');
        // UPDATE ROLE
        Route::patch("roles/{user}/update", [RoleController::class, "update"])->name("role.update");
        Route::delete("roles/{user}/destroy", [RoleController::class, "destroy"])->name("role.destroy");
        Route::patch("roles/update/forum", [RoleController::class, "updateForum"])->name("role.update.forum");
        // SEARCH STAFF
        Route::get('roles/live-search', [RoleController::class, 'liveSearch'])->name('role.liveSearch');
        Route::get('roles/{user}/show', [RoleController::class, 'show'])->name('role.show');
        // GRANT ROLE
        Route::get('roles/grant-search', [RoleController::class, 'grantSearch'])->name('role.grantSearch');
        Route::post('roles/grant', [RoleController::class, 'grant'])->name('role.grant');


    // LOGOUT
    Route::get("logout", [AuthContoller::class, "logout"])->name("admin.logout");

});

