<?php

namespace App\Providers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Native\Desktop\Events\AutoUpdater\Error as UpdateError;
use Native\Desktop\Events\AutoUpdater\UpdateAvailable;
use Native\Desktop\Events\AutoUpdater\UpdateDownloaded;
use Native\Desktop\Events\AutoUpdater\UpdateNotAvailable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(UpdateAvailable::class, function ($event) {
            Cache::forever('update.status', 'available');
            Cache::forever('update.version', $event->version ?? null);
        });

        Event::listen(UpdateNotAvailable::class, function () {
            Cache::forever('update.status', 'none');
        });

        Event::listen(UpdateDownloaded::class, function () {
            Cache::forever('update.status', 'downloaded');
        });

        Event::listen(UpdateError::class, function () {
            Cache::forever('update.status', 'error');
        });
    }
}
