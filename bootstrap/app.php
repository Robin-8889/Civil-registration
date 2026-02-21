<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;
use App\Http\Middleware\CheckUserApproved;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'check_user_approved' => CheckUserApproved::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        // Daily database backup at 2:00 AM
        $schedule->command('db:backup --type=full')
            ->dailyAt('02:00')
            ->appendOutputTo(storage_path('logs/backup-schedule.log'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
