<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AppealController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ForumController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SanctionController;
use App\Http\Controllers\Admin\SchoolController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\SocialLoginController;
use Illuminate\Support\Facades\Route;

/**
 * OAuth lives here rather than in routes/api.php, even though the URLs keep the
 * /api prefix that Google and Facebook already have registered as the callback.
 *
 * Socialite's CSRF defence stores a `state` value in the session and re-reads it
 * on the callback. Reaching the session from routes/api.php meant adding the
 * `web` group on top of Sanctum's stateful pipeline, which made EncryptCookies
 * and StartSession run twice per request and silently lost the write. Declaring
 * the routes here gives them the `web` group once and no Sanctum wrapper.
 */
Route::prefix('api')->middleware('throttle:social-auth')->group(function () {
    Route::get('/auth/{provider}/redirect', [SocialLoginController::class, 'redirect'])
        ->whereIn('provider', ['google', 'facebook'])
        ->name('social.redirect');
    Route::get('/auth/{provider}/callback', [SocialLoginController::class, 'callback'])
        ->whereIn('provider', ['google', 'facebook'])
        ->name('social.callback');
});

Route::get('admin', [AuthController::class, 'redirecting']);

Route::get('admin/login', [AuthController::class, 'index'])->name('login');
Route::post('admin/login/login', [AuthController::class, 'login'])
    ->middleware('throttle:admin-login')
    ->name('admin.login');

Route::prefix('admin')
    ->middleware(['auth', 'role:super_admin|admin|moderator', 'permission:access admin panel'])
    ->group(function () {

        // NOTIFICATIONS

        // MARK AS READ
        Route::post('notifications/read-all', [AdminController::class, 'readAllNotifications'])
            ->name('admin.notifications.readAll');
        // POST: marking a notification read changes state, so it must not be
        // reachable through a plain link (or a cross-site <img> tag).
        Route::post('notifications/{id}/read', [AdminController::class, 'readNotification'])
            ->name('admin.notifications.read');

        // DAHSBOARD
        Route::get('dashboard', [DashboardController::class, 'index'])
            ->name('admin.dashboard')
            ->middleware('permission:view dashboard');

        Route::get('dashboard/export', [DashboardController::class, 'exportPdf'])
            ->name('admin.dashboard.export')
            ->middleware('permission:export dashboard');

        // UPDATE PROFILE
        Route::get('profile/{user}', [AdminController::class, 'profile'])
            ->name('admin.profile')
            ->middleware('permission:view own profile');

        Route::patch('profile/{user}/update', [AdminController::class, 'update'])
            ->name('profile.update')
            ->middleware('permission:update own profile');

        Route::patch('profile/{user}/images', [AdminController::class, 'updateImages'])
            ->name('profile.updateImages')
            ->middleware('permission:update own profile images');

        Route::patch('profile/{user}/password', [AdminController::class, 'updatePassword'])
            ->name('profile.updatePassword')
            ->middleware('permission:update own password');

        // MODERATION

        // REPORTS
        Route::get('reports', [ReportController::class, 'index'])
            ->name('report.index')
            ->middleware('permission:view reports');

        // APPROVE REPORT
        Route::patch('reports/{report}/approve', [ReportController::class, 'approve'])
            ->name('report.approve')
            ->middleware('permission:approve reports');

        // REJECT REPORT
        Route::patch('reports/{report}/reject', [ReportController::class, 'reject'])
            ->name('report.reject')
            ->middleware('permission:reject reports');

        // SANCTIONS
        Route::get('sanctions', [SanctionController::class, 'index'])
            ->name('sanction.index')
            ->middleware('permission:view sanctions');

        // REMOVE SANCTION
        Route::delete('sanctions/{sanction}/remove', [SanctionController::class, 'remove'])
            ->name('sanction.remove')
            ->middleware('permission:remove sanctions');

        // CREATE SANCTION
        Route::post('sanctions/create', [SanctionController::class, 'store'])
            ->name('sanction.create')
            ->middleware('permission:create sanctions');

        // APPEALS
        Route::get('appeals', [AppealController::class, 'index'])
            ->name('appeal.index')
            ->middleware('permission:view appeals');

        // SHOW
        Route::get('appeals/{appeal}/show', [AppealController::class, 'show'])
            ->name('appeal.show')
            ->middleware('permission:view appeal details');

        // ACCEPT
        Route::patch('appeals/{appeal}/accept', [AppealController::class, 'accept'])
            ->name('appeal.accept')
            ->middleware('permission:accept appeals');

        // REJECT
        Route::patch('appeals/{appeal}/reject', [AppealController::class, 'reject'])
            ->name('appeal.reject')
            ->middleware('permission:reject appeals');

        // COMUNITY

        // USERS
        Route::get('users', [UserController::class, 'index'])
            ->name('user.index')
            ->middleware('permission:view users');

        // SEARCH USERS
        Route::get('users/liveSearch', [UserController::class, 'liveSearch'])
            ->name('user.liveSearch')
            ->middleware('permission:search users');

        Route::get('users/{user}/show', [UserController::class, 'show'])
            ->name('user.show')
            ->middleware('permission:view user details');

        // EXPORT USER AS PDF
        Route::get('users/{user}/export', [UserController::class, 'export'])
            ->name('user.export')
            ->middleware('permission:export user as pdf');

        // FORUMS
        Route::get('forums', [ForumController::class, 'index'])
            ->name('forum.index')
            ->middleware('permission:view forums');

        // CREATE FORUM
        Route::post('forum/store', [ForumController::class, 'store'])
            ->name('forum.store')
            ->middleware('permission:create forums');

        // UPDATE FORUM
        Route::patch('forum/{forum}/update', [ForumController::class, 'edit'])
            ->name('forum.update')
            ->middleware('permission:update forums');

        // DELETE FORUM
        Route::delete('forum/{forum}/destroy', [ForumController::class, 'destroy'])
            ->name('forum.destroy')
            ->middleware('permission:delete forums');

        // SEARCH FORUMS
        Route::get('forums/liveSearch', [ForumController::class, 'liveSearch'])
            ->name('forum.liveSearch')
            ->middleware('permission:search forums');

        Route::get('forums/{forum}/show', [ForumController::class, 'show'])
            ->name('forum.show')
            ->middleware('permission:view forum details');

        // SCHOOLS
        Route::get('shools', [SchoolController::class, 'index'])
            ->name('school.index')
            ->middleware('permission:view schools');

        // CREATE
        Route::post('school/store', [SchoolController::class, 'store'])
            ->name('school.store')
            ->middleware('permission:create schools');

        // SEARCH
        Route::get('schools/liveSearch', [SchoolController::class, 'liveSearch'])
            ->name('school.liveSearch')
            ->middleware('permission:search schools');

        // DELETE
        Route::delete('schools/{school}/delete', [SchoolController::class, 'destroy'])
            ->name('school.delete')
            ->middleware('permission:delete schools');

        // ROLES AND PERMISSIONS
        Route::middleware('permission:view roles page')->group(function () {
            Route::get('roles', [RoleController::class, 'index'])->name('role.index');
            Route::get('roles/live-search', [RoleController::class, 'liveSearch'])->name('role.liveSearch');
            Route::get('roles/{user}/show', [RoleController::class, 'show'])->name('role.show');
        });

        Route::middleware('permission:grant roles')->group(function () {
            Route::get('roles/grant-search', [RoleController::class, 'grantSearch'])->name('role.grantSearch');
            Route::post('roles/grant', [RoleController::class, 'grant'])->name('role.grant');
        });

        Route::middleware('permission:update user role')->group(function () {
            Route::patch('roles/{user}/update', [RoleController::class, 'update'])->name('role.update');
        });

        Route::middleware('permission:delete user role')->group(function () {
            Route::delete('roles/{user}/destroy', [RoleController::class, 'destroy'])->name('role.destroy');
        });

        Route::middleware('permission:update forum role settings')->group(function () {
            Route::patch('roles/update/forum', [RoleController::class, 'updateForum'])->name('role.update.forum');
        });

        // LOGOUT (POST + CSRF — avoid CSRF logout via GET)
        Route::post('logout', [AuthController::class, 'logout'])
            ->name('admin.logout')
            ->middleware('permission:logout admin');
    });
