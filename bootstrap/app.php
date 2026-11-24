<?php

use Illuminate\Console\Scheduling\Schedule;
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
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [\App\Http\Middleware\SetLocale::class]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'auth.api' => \App\Http\Middleware\EnsureApiKey::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('orders:sync-supplier-status')->everyFiveMinutes()->withoutOverlapping();
        $schedule->command('suppliers:sync-justanotherpanel')->dailyAt('03:15')->withoutOverlapping();
        $schedule->command('suppliers:sync-textverified-products')->dailyAt('03:30')->withoutOverlapping();
        $schedule->command('catalogue:sync-smm-caps')->dailyAt('03:45')->withoutOverlapping();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
