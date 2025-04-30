<?php
use App\Http\Controllers\TagController;
use App\Http\Middleware\CheckJWT;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsUserAuth;
use Illuminate\Support\Facades\Route;

// Public route to get the tags (without middleware)
Route::get('/', [TagController::class, 'getTags']);

Route::middleware([IsUserAuth::class])->group(function () {
    //If the user is authenticated, it allows access to the Tags functionality

    Route::middleware([IsAdmin::class, CheckJWT::class])->group(function () {
        // If the user is admin and has a valid JWT

        Route::get('/{id}', [TagController::class, 'getTagById']); // Ruta para obtener tag por ID
        // Create a new tag
        Route::post('/create-tag', [TagController::class, 'createTag']);

        // Update an existing tag
        Route::put('/{id}', [TagController::class, 'updateTag']);

        // Delete a tag
Route::delete('/{id}', [TagController::class, 'deleteTag']);


        //Assign tags to a blog
        Route::post('blogs/{productId}/tags', [TagController::class, 'assignTagsToBlogs']);
    });
});
