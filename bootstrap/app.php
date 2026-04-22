<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'checkrole' => \App\Http\Middleware\CheckRole::class,
            'approved' => \App\Http\Middleware\CheckApproved::class,
            'assigned' => \App\Http\Middleware\CheckAssigned::class,
            'notassigned' => \App\Http\Middleware\EnsureNotAssigned::class,
            'office' => \App\Http\Middleware\CheckOfficeNetwork::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
