<?php

use App\Http\Controllers\Auth\AcknowledgeSanctionController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\MeController;
use App\Http\Controllers\Auth\OnboardingAvatarController;
use App\Http\Controllers\Auth\OnboardingController;
use App\Http\Controllers\Auth\StoreAppealController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ExploreController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\FeedHideController;
use App\Http\Controllers\FollowForumController;
use App\Http\Controllers\FollowThreadController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\GifController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\PollController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\StoreFeedbackController;
use App\Http\Controllers\ThreadController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\UserSearchController;
use App\Http\Controllers\VoteController;
use App\Support\Username;
use Illuminate\Support\Facades\Route;

// The social login routes live in routes/web.php (under the same /api prefix) so
// the session middleware runs exactly once. See the note there.

// Save onboarding profile (and auto-follow the student's school forum).
Route::middleware(['auth:sanctum', 'not_banned', 'throttle:api-writes'])->put('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');
Route::middleware(['auth:sanctum', 'not_banned', 'onboarding', 'throttle:media-upload'])->post('/onboarding/avatar', [OnboardingAvatarController::class, 'store'])->name('onboarding.avatar');
// Return the current authenticated user.
Route::middleware('auth:sanctum')->get('/me', MeController::class)->name('me.show');
// Dismiss the current-user sanction popup (banned users must still be able to call this).
Route::middleware(['auth:sanctum', 'throttle:api-writes'])->post('/me/sanctions/{sanction}/acknowledge', AcknowledgeSanctionController::class)
    ->withTrashed()
    ->name('me.sanctions.acknowledge');
Route::middleware(['auth:sanctum', 'throttle:api-writes'])->post('/me/sanctions/{sanction}/appeals', StoreAppealController::class)
    ->name('me.sanctions.appeals.store');
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
Route::middleware('throttle:api-reads')->group(function () {
    Route::get('/u/{username}', [UserProfileController::class, 'show'])
        ->where('username', Username::ROUTE_PATTERN)
        ->name('users.profile.show');
    Route::get('/u/{username}/threads', [UserProfileController::class, 'threads'])
        ->where('username', Username::ROUTE_PATTERN)
        ->name('users.profile.threads');
    Route::get('/u/{username}/comments', [UserProfileController::class, 'comments'])
        ->where('username', Username::ROUTE_PATTERN)
        ->name('users.profile.comments');
    Route::get('/u/{username}/followed-forums', [UserProfileController::class, 'followedForums'])
        ->where('username', Username::ROUTE_PATTERN)
        ->name('users.profile.followed-forums');

    Route::get('/forums', [ForumController::class, 'index'])->name('forums.index');
    Route::get('/cities', [CityController::class, 'index'])->name('cities.index');
    Route::get('/feed', [FeedController::class, 'index'])->name('feed.index');
    Route::get('/explore', [ExploreController::class, 'index'])->name('explore.index');
    Route::get('/p/{forum:slug}', [ForumController::class, 'show'])->name('forums.show');
    Route::get('/p/{forum:slug}/threads', [ThreadController::class, 'index'])->name('forums.threads.index');
    Route::get('/p/{forum:slug}/comments/{thread:id}', [ThreadController::class, 'show'])->name('forums.threads.show');
    Route::get('/comments/{comment}/replies', [CommentController::class, 'replies'])
        ->withTrashed()
        ->name('comments.replies');
});

Route::get('/search', [SearchController::class, 'index'])
    ->middleware('throttle:api-search')
    ->name('search.index');

// About-page feedback. Guests and banned users can submit; CSRF still applies.
Route::post('/feedback', StoreFeedbackController::class)
    ->middleware('throttle:feedback')
    ->name('feedback.store');

Route::middleware(['auth:sanctum', 'not_banned', 'onboarding'])->group(function () {
    // Username autocomplete for @mentions in comments.
    Route::get('/users/search', [UserSearchController::class, 'index'])
        ->middleware('throttle:api-search')
        ->name('users.search');

    // Giphy search/trending proxy so the API key never ships to the browser.
    Route::get('/gifs', [GifController::class, 'index'])
        ->middleware('throttle:api-search')
        ->name('gifs.search');

    // Follow / unfollow another user.
    Route::post('/u/{username}/follow', [UserProfileController::class, 'follow'])
        ->middleware('throttle:api-writes')
        ->where('username', Username::ROUTE_PATTERN)
        ->name('users.follow');
    Route::delete('/u/{username}/follow', [UserProfileController::class, 'unfollow'])
        ->middleware('throttle:api-writes')
        ->where('username', Username::ROUTE_PATTERN)
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
