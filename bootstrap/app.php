<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureProfileIsComplete;
use App\Http\Middleware\EnsureUserIsNotTimedOut;
use App\Http\Middleware\EnsureMarketingAdmin;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            EnsureUserIsNotTimedOut::class,
        ]);

        $middleware->alias([
            'profile.complete' => EnsureProfileIsComplete::class,
            'marketing.admin' => EnsureMarketingAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
