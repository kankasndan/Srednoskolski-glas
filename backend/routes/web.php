<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AuthContoller;
use App\Http\Controllers\Admin\RoleController;
use Illuminate\Support\Facades\Route;

Route::get("admin/login", [AuthContoller::class, "index"]);
Route::post("admin/login/login", [AuthContoller::class, "login"])->name("admin.login");

Route::prefix("admin")->middleware("auth")->group(function () {
    Route::get("dashboard", [AdminController::class, "index"])->name("admin.dashboard");
    

    // ROLES AND PERMISSION
    Route::get("roles", [RoleController::class, "index"])->name('role.index');
        // UPDATE ROLE
        Route::patch("roles/{user}/update", [RoleController::class, "update"])->name("role.update");
        Route::delete("roles/{user}/destroy", [RoleController::class, "destroy"])->name("role.destroy");

        // SEARCH STAFF
        Route::get('roles/live-search', [RoleController::class, 'liveSearch'])->name('role.liveSearch');
        Route::get('roles/{user}/show', [RoleController::class, 'show'])->name('role.show');

        // GRANT ROLE
        Route::get('roles/grant-search', [RoleController::class, 'grantSearch'])->name('role.grantSearch');
        Route::post('roles/grant', [RoleController::class, 'grant'])->name('role.grant');



});

