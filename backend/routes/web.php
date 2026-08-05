<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AppealController;
use App\Http\Controllers\Admin\AuthContoller;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ForumController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SanctionController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('admin', [AuthContoller::class, 'redirecting']);

Route::get('admin/login', [AuthContoller::class, 'index'])->name('login');
Route::post('admin/login/login', [AuthContoller::class, 'login'])->name('admin.login');

Route::prefix('admin')->middleware('auth')->group(function () {

    // DAHSBOARD
    Route::get('dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('dashboard/export', [DashboardController::class, 'exportPdf'])->name('admin.dashboard.export');

    // UPDATE PROFILE
    Route::get('profile/{user}', [AdminController::class, 'profile'])->name('admin.profile');
    Route::patch('profile/{user}/update', [AdminController::class, 'update'])->name('profile.update');
    Route::patch('profile/{user}/images', [AdminController::class, 'updateImages'])->name('profile.updateImages');
    Route::patch('profile/{user}/password', [AdminController::class, 'updatePassword'])->name('profile.updatePassword');

    // MODERATION

    // REPORTS
    Route::get('reports', [ReportController::class, 'index'])->name('report.index');

    // APPROVE REPORT
    Route::patch('reports/{report}/approve', [ReportController::class, 'approve'])->name('report.approve');

    // REJECT REPORT
    Route::patch('reports/{report}/reject', [ReportController::class, 'reject'])->name('report.reject');

    // SANCTIONS
    Route::get('sanctions', [SanctionController::class, 'index'])->name('sanction.index');

    // REMOVE SANCTION
    Route::delete('sanctions/{sanction}/remove', [SanctionController::class, 'remove'])->name('sanction.remove');

    // CREATE SANCTION
    Route::post('sanctions/create', [SanctionController::class, 'store'])->name('sanction.create');

    // APPEALS
    Route::get('appeals', [AppealController::class, 'index'])->name('appeal.index');

    // SEARCH APPEALS
    Route::get('appeals/liveSearch', [AppealController::class, 'liveSearch'])->name('appeal.liveSearch');

    // SHOW
    Route::get('appeals/{appeal}/show', [AppealController::class, 'show'])->name('appeal.show');
    // ACCEPT
    Route::patch('appeals/{appeal}/accept', [AppealController::class, 'accept'])->name('appeal.accept');
    // REJECT
    Route::patch('appeals/{appeal}/reject', [AppealController::class, 'reject'])->name('appeal.reject');

    // COMUNITY

    // USERS
    Route::get('users', [UserController::class, 'index'])->name('user.index');

    // SEARCH USERS
    Route::get('users/liveSearch', [UserController::class, 'liveSearch'])->name('user.liveSearch');
    Route::get('users/{user}/show', [UserController::class, 'show'])->name('user.show');

    // EXPORT USER AS PDF
    Route::get('users/{user}/export', [UserController::class, 'export'])->name('user.export');

    // FORUMS
    Route::get('forums', [ForumController::class, 'index'])->name('forum.index');

    // CREATE FORUM
    Route::post('forum/store', [ForumController::class, 'store'])->name('forum.store');

    // UPDATE FORUM
    Route::patch('forum/{forum}/update', [ForumController::class, 'edit'])->name('forum.update');

    // DELETE FORUM
    Route::delete('forum/{forum}/destroy', [ForumController::class, 'destroy'])->name('forum.destroy');

    // SEARCH FORUMS
    Route::get('forums/liveSearch', [ForumController::class, 'liveSearch'])->name('forum.liveSearch');
    Route::get('forums/{forum}/show', [ForumController::class, 'show'])->name('forum.show');

    // ROLES AND PERMISSION
    Route::get('roles', [RoleController::class, 'index'])->name('role.index');
    // UPDATE ROLE
    Route::patch('roles/{user}/update', [RoleController::class, 'update'])->name('role.update');
    Route::delete('roles/{user}/destroy', [RoleController::class, 'destroy'])->name('role.destroy');
    Route::patch('roles/update/forum', [RoleController::class, 'updateForum'])->name('role.update.forum');
    // SEARCH STAFF
    Route::get('roles/live-search', [RoleController::class, 'liveSearch'])->name('role.liveSearch');
    Route::get('roles/{user}/show', [RoleController::class, 'show'])->name('role.show');
    // GRANT ROLE
    Route::get('roles/grant-search', [RoleController::class, 'grantSearch'])->name('role.grantSearch');
    Route::post('roles/grant', [RoleController::class, 'grant'])->name('role.grant');

    // LOGOUT
    Route::get('logout', [AuthContoller::class, 'logout'])->name('admin.logout');

});
