<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function(){
            Route::prefix('auth')->group(base_path('routes/auth.php'));
            Route::prefix('blogs')->group(base_path('routes/blogs.php'));
            Route::prefix('tags')->group(base_path('routes/tags.php'));
            Route::prefix('comment')->group(base_path('routes/comment.php'));
            Route::prefix('newsletter')->group(base_path('routes/newsletter.php'));


        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
