<?php
use App\Http\Controllers\CommentController;
use App\Http\Middleware\CheckJWT;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsUserAuth;
use Illuminate\Support\Facades\Route;

// Get all comments from a (public) blog
Route::get('blogs/{blogId}/comments', [CommentController::class, 'getComments']);

// Routes that require an authenticated user
Route::middleware(IsUserAuth::class)->group(function () {
    // Create comment (any)
    Route::post('blogs/{blogId}/comments', [CommentController::class, 'createComment']);

    // Update and delete comment (only author or admin with valid JWT)
    Route::middleware([CheckJWT::class])->group(function () {
        Route::put('comments/{commentId}', [CommentController::class, 'updateComment']);
        Route::delete('comments/{commentId}', [CommentController::class, 'deleteComment']);
    });
});
