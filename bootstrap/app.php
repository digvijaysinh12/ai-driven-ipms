<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'checkrole' => \App\Http\Middleware\CheckRole::class,
            'approved' => \App\Http\Middleware\CheckApproved::class,
            'assigned' => \App\Http\Middleware\CheckAssigned::class,
            'ensureNotAssigned' => \App\Http\Middleware\EnsureNotAssigned::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
