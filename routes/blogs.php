<?php

use App\Http\Controllers\BlogController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\IsUserAuth;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\CheckJWT;


Route::get('/', [BlogController::class, 'index']);
Route::get('/{id}', [BlogController::class, 'show']);
Route::get('/slug/{slug}', [BlogController::class, 'getBySlug']);


Route::middleware([IsUserAuth::class])->group(function () {

 Route::middleware([IsAdmin::class,CheckJWT::class])->group(function () {
    
    Route::post('/create-blogs', [BlogController::class, 'store']);
        Route::patch('/{id}', [BlogController::class, 'update']);
        Route::delete('/{id}', [BlogController::class, 'destroy']);

 });

});
