<?php

use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\MeController;
use App\Http\Controllers\Auth\OnboardingController;
use App\Http\Controllers\Auth\SocialLoginController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\FeedHideController;
use App\Http\Controllers\FollowForumController;
use App\Http\Controllers\FollowThreadController;
use App\Http\Controllers\NewestController;
use App\Http\Controllers\ExploreController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\PollController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ThreadController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\VoteController;
use Illuminate\Support\Facades\Route;

// Social login needs the web middleware so OAuth can start a session cookie.
Route::middleware(['web', 'throttle:social-auth'])->group(function () {
    // Redirect the browser to Google/Facebook OAuth.
    Route::get('/auth/{provider}/redirect', [SocialLoginController::class, 'redirect'])
        ->whereIn('provider', ['google', 'facebook'])
        ->name('social.redirect');
    // Handle the OAuth callback and log the user in.
    Route::get('/auth/{provider}/callback', [SocialLoginController::class, 'callback'])
        ->whereIn('provider', ['google', 'facebook'])
        ->name('social.callback');
});

// Save onboarding profile (and auto-follow the student's school forum).
Route::middleware(['auth:sanctum', 'not_banned', 'throttle:api-writes'])->put('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');
// Return the current authenticated user.
Route::middleware('auth:sanctum')->get('/me', MeController::class)->name('me.show');
Route::middleware(['auth:sanctum', 'not_banned', 'onboarding', 'throttle:api-writes'])->put('/me', [ProfileController::class, 'update'])->name('me.update');
// Profile activity lists for the authenticated user.
Route::middleware('auth:sanctum')->get('/me/counts', [ProfileController::class, 'counts'])->name('me.counts');
Route::middleware('auth:sanctum')->get('/me/threads', [ProfileController::class, 'threads'])->name('me.threads');
Route::middleware('auth:sanctum')->get('/me/comments', [ProfileController::class, 'comments'])->name('me.comments');
Route::middleware('auth:sanctum')->get('/me/followed-forums', [ProfileController::class, 'followedForums'])->name('me.followed-forums');
Route::middleware('auth:sanctum')->get('/me/followed-threads', [ProfileController::class, 'followedThreads'])->name('me.followed-threads');
Route::middleware('auth:sanctum')->get('/me/following-users', [ProfileController::class, 'followingUsers'])->name('me.following-users');
// Log the user out and end the session.
Route::middleware(['auth:sanctum', 'throttle:api-writes'])->post('/logout', LogoutController::class)->name('auth.logout');

// Public user profiles (by username).
Route::get('/u/{username}', [UserProfileController::class, 'show'])
    ->where('username', '[A-Za-z0-9_.-]+')
    ->name('users.profile.show');
Route::get('/u/{username}/threads', [UserProfileController::class, 'threads'])
    ->where('username', '[A-Za-z0-9_.-]+')
    ->name('users.profile.threads');
Route::get('/u/{username}/comments', [UserProfileController::class, 'comments'])
    ->where('username', '[A-Za-z0-9_.-]+')
    ->name('users.profile.comments');
Route::get('/u/{username}/followed-forums', [UserProfileController::class, 'followedForums'])
    ->where('username', '[A-Za-z0-9_.-]+')
    ->name('users.profile.followed-forums');

// List thematic + school forums for the sidebar.
Route::get('/forums', [ForumController::class, 'index'])->name('forums.index');
// List cities and schools (onboarding / filters).
Route::get('/cities', [CityController::class, 'index'])->name('cities.index');
// Paginated cross-forum home feed.
Route::get('/feed', [FeedController::class, 'index'])->name('feed.index');
// Newest discussions (/newest): general + followed schools, focus mix on follows.
Route::get('/newest', [NewestController::class, 'index'])->name('newest.index');
// Explore page: top visited general forums + most interacted threads this week.
Route::get('/explore', [ExploreController::class, 'index'])->name('explore.index');
// Live search + explore page (threads + matching forums).
Route::get('/search', [SearchController::class, 'index'])->name('search.index');

// Forum banner/metadata only (no threads).
Route::get('/p/{forum:slug}', [ForumController::class, 'show'])->name('forums.show');
// Paginated threads for a forum.
Route::get('/p/{forum:slug}/threads', [ThreadController::class, 'index'])->name('forums.threads.index');
// Thread detail with nested comments (increments views; sort=best|newest|oldest).
Route::get('/p/{forum:slug}/comments/{thread:id}', [ThreadController::class, 'show'])->name('forums.threads.show');

Route::middleware(['auth:sanctum', 'not_banned', 'onboarding'])->group(function () {
    // Follow / unfollow another user.
    Route::post('/u/{username}/follow', [UserProfileController::class, 'follow'])
        ->middleware('throttle:api-writes')
        ->where('username', '[A-Za-z0-9_.-]+')
        ->name('users.follow');
    Route::delete('/u/{username}/follow', [UserProfileController::class, 'unfollow'])
        ->middleware('throttle:api-writes')
        ->where('username', '[A-Za-z0-9_.-]+')
        ->name('users.unfollow');

    // Create a new thread (optional files, link, or poll).
    Route::post('/threads', [ThreadController::class, 'store'])
        ->middleware('throttle:thread-create')
        ->name('threads.store');
    // Update a thread (author only). POST accepts multipart for attachment changes.
    Route::match(['put', 'post'], '/threads/{thread}', [ThreadController::class, 'update'])
        ->middleware('throttle:api-writes')
        ->name('threads.update');
    // Soft-delete a thread (author only).
    Route::delete('/threads/{thread}', [ThreadController::class, 'destroy'])
        ->middleware('throttle:api-writes')
        ->name('threads.destroy');
    // Create a comment or reply on a thread.
    Route::post('/threads/{thread}/comments', [CommentController::class, 'store'])
        ->middleware('throttle:comment-create')
        ->name('comments.store');
    // Update a comment (author only).
    Route::put('/comments/{comment}', [CommentController::class, 'update'])
        ->middleware('throttle:api-writes')
        ->name('comments.update');
    // Soft-delete a comment (author only).
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])
        ->middleware('throttle:api-writes')
        ->name('comments.destroy');
    // Vote on a poll option (or change an existing vote).
    Route::post('/polls/{poll}/vote', [PollController::class, 'vote'])
        ->middleware('throttle:api-writes')
        ->name('polls.vote');

    // Follow a general forum.
    Route::post('/p/{forum:slug}/follow', [FollowForumController::class, 'store'])
        ->middleware('throttle:api-writes')
        ->name('forums.follow');
    // Unfollow a general forum.
    Route::delete('/p/{forum:slug}/follow', [FollowForumController::class, 'destroy'])
        ->middleware('throttle:api-writes')
        ->name('forums.unfollow');

    // Follow / unfollow a thread (visual only in MVP).
    Route::post('/threads/{thread}/follow', [FollowThreadController::class, 'store'])
        ->middleware('throttle:api-writes')
        ->name('threads.follow');
    Route::delete('/threads/{thread}/follow', [FollowThreadController::class, 'destroy'])
        ->middleware('throttle:api-writes')
        ->name('threads.unfollow');

    // Upload a media file.
    Route::post('/media', [MediaController::class, 'store'])
        ->middleware('throttle:media-upload')
        ->name('media.store');
    // Delete an uploaded media file (owner only).
    Route::delete('/media', [MediaController::class, 'destroy'])
        ->middleware('throttle:api-writes')
        ->name('media.destroy');

    // Toggle upvote on a thread.
    Route::post('/threads/{thread}/upvote', [VoteController::class, 'toggleThread'])
        ->middleware('throttle:api-writes')
        ->name('threads.upvote');
    // Toggle upvote on a comment.
    Route::post('/comments/{comment}/upvote', [VoteController::class, 'toggleComment'])
        ->middleware('throttle:api-writes')
        ->name('comments.upvote');

    // Hide / unhide a thread from the personalized feed.
    Route::post('/threads/{thread}/hide', [FeedHideController::class, 'store'])
        ->middleware('throttle:api-writes')
        ->name('threads.hide');
    Route::delete('/threads/{thread}/hide', [FeedHideController::class, 'destroy'])
        ->middleware('throttle:api-writes')
        ->name('threads.unhide');

    // Report thread / comment (also hides reported threads from the reporter's feed).
    Route::post('/threads/{thread}/report', [ReportController::class, 'storeThread'])
        ->middleware('throttle:api-writes')
        ->name('threads.report');
    Route::post('/comments/{comment}/report', [ReportController::class, 'storeComment'])
        ->middleware('throttle:api-writes')
        ->name('comments.report');
});
